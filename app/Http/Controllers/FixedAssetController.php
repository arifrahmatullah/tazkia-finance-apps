<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FixedAsset;
use App\Models\FixedAssetDepreciation;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        $status = $request->input('status');
        $search = $request->input('search');

        $assets = FixedAsset::with(['account', 'accumulatedDepreciationAccount', 'depreciationExpenseAccount', 'depreciations'])
            ->when($orgId, fn($q) => $q->where('organization_id', $orgId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where(fn($sq) => $sq
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->orderBy('code')
            ->get();

        $totals = (object) [
            'cost'         => $assets->sum('acquisition_cost'),
            'accumulated'  => $assets->sum('accumulated_depreciation'),
            'book_value'   => $assets->sum('book_value'),
        ];

        $currentPeriod = now()->startOfMonth()->toDateString();

        return view('fixed-assets.index', compact(
            'assets', 'organizations', 'orgId', 'status', 'search', 'totals', 'currentPeriod'
        ));
    }

    public function create()
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        return view('fixed-assets.create', [
            'organizations' => $organizations,
            'accounts'      => $this->accountsFor($organizations),
            'asset'         => new FixedAsset(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        abort_unless(auth()->user()->canAccessOrganization($validated['organization_id']), 403);

        $validated['created_by'] = auth()->id();
        FixedAsset::create($validated);

        return redirect()->route('fixed-assets.index', ['organization_id' => $validated['organization_id']])
            ->with('success', 'Aset tetap berhasil ditambahkan.');
    }

    public function edit(FixedAsset $fixedAsset)
    {
        abort_unless(auth()->user()->canAccessOrganization($fixedAsset->organization_id), 403);

        $organizations = $this->allowedOrgs()->orderBy('name')->get();

        return view('fixed-assets.edit', [
            'organizations' => $organizations,
            'accounts'      => $this->accountsFor($organizations),
            'asset'         => $fixedAsset,
        ]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        abort_unless(auth()->user()->canAccessOrganization($fixedAsset->organization_id), 403);

        $validated = $this->validated($request, $fixedAsset->id);

        abort_unless(auth()->user()->canAccessOrganization($validated['organization_id']), 403);

        $fixedAsset->update($validated);

        return redirect()->route('fixed-assets.index', ['organization_id' => $validated['organization_id']])
            ->with('success', 'Aset tetap berhasil diperbarui.');
    }

    public function destroy(FixedAsset $fixedAsset)
    {
        abort_unless(auth()->user()->canAccessOrganization($fixedAsset->organization_id), 403);

        if ($fixedAsset->depreciations()->exists()) {
            return back()->withErrors([
                'delete' => 'Aset tidak bisa dihapus karena sudah punya riwayat penyusutan. Ubah status ke "Dihapusbukukan" jika aset sudah tidak digunakan.',
            ]);
        }

        $fixedAsset->delete();

        return back()->with('success', 'Aset tetap berhasil dihapus.');
    }

    // Proses penyusutan bulanan: buat satu jurnal (debit beban depresiasi, kredit akumulasi depresiasi)
    // per aset aktif yang belum diposting pada periode ini dan belum lunas disusutkan.
    public function depreciate(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'period'          => 'required|date_format:Y-m',
        ]);

        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);

        $orgId = $request->organization_id;
        $period = $request->period . '-01';

        $result = DB::transaction(function () use ($orgId, $period) {
            $assets = FixedAsset::with('depreciations')
                ->where('organization_id', $orgId)
                ->where('status', 'aktif')
                ->where('acquisition_date', '<=', $period)
                ->lockForUpdate()
                ->get()
                ->reject(fn($a) => $a->depreciations->contains(fn($d) => $d->period->toDateString() === $period))
                ->reject(fn($a) => $a->is_fully_depreciated);

            $lines = [];
            $logRows = [];
            $sortOrder = 0;
            $total = 0.0;

            foreach ($assets as $asset) {
                $amount = $asset->remainingDepreciationFor($asset->monthly_depreciation);
                if ($amount <= 0) {
                    continue;
                }

                $lines[] = [
                    'account_id'  => $asset->depreciation_expense_account_id,
                    'description' => "Beban penyusutan {$asset->name} ({$asset->code})",
                    'debit'       => $amount,
                    'credit'      => 0,
                    'sort_order'  => $sortOrder,
                ];
                $lines[] = [
                    'account_id'  => $asset->accumulated_depreciation_account_id,
                    'description' => "Akumulasi penyusutan {$asset->name} ({$asset->code})",
                    'debit'       => 0,
                    'credit'      => $amount,
                    'sort_order'  => $sortOrder + 1,
                ];
                $sortOrder += 2;

                $logRows[] = ['fixed_asset_id' => $asset->id, 'amount' => $amount];
                $total += $amount;
            }

            if (empty($lines)) {
                return ['count' => 0, 'total' => 0];
            }

            $entry = JournalEntry::create([
                'organization_id' => $orgId,
                'entry_date'      => $period,
                'reference'       => $this->generateReference($period),
                'description'     => 'Jurnal penyusutan aset tetap periode ' . \Carbon\Carbon::parse($period)->translatedFormat('F Y'),
                'status'          => 'posted',
                'source_type'     => 'asset_depreciation',
                'created_by'      => auth()->id(),
                'posted_at'       => now(),
                'posted_by'       => auth()->id(),
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create($line + ['journal_entry_id' => $entry->id]);
            }

            foreach ($logRows as $row) {
                FixedAssetDepreciation::create($row + ['period' => $period, 'journal_entry_id' => $entry->id]);
            }

            return ['count' => count($logRows), 'total' => $total];
        });

        if ($result['count'] === 0) {
            return back()->with('info', 'Tidak ada aset yang perlu disusutkan pada periode ini (sudah diproses atau sudah lunas disusutkan).');
        }

        return back()->with('success',
            "Penyusutan berhasil diposting untuk {$result['count']} aset, total Rp " . number_format($result['total'], 0, ',', '.') . '.'
        );
    }

    private function validated(Request $request, ?string $ignoreId = null): array
    {
        $orgId = $request->input('organization_id');

        return $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'code'             => 'required|string|max:30|unique:fixed_assets,code,' . ($ignoreId ?? 'NULL') . ',id,organization_id,' . $orgId,
            'name'             => 'required|string|max:150',
            'account_id'                          => 'required|exists:accounts,id',
            'accumulated_depreciation_account_id'  => 'required|exists:accounts,id|different:account_id',
            'depreciation_expense_account_id'      => 'required|exists:accounts,id',
            'acquisition_date'    => 'required|date',
            'acquisition_cost'    => 'required|numeric|min:0',
            'salvage_value'       => 'nullable|numeric|min:0|lt:acquisition_cost',
            'useful_life_months'  => 'required|integer|min:1',
            'status'              => 'required|in:aktif,dihapusbukukan',
            'notes'               => 'nullable|string|max:1000',
        ]);
    }

    private function generateReference(string $period): string
    {
        $prefix = 'PA-' . \Carbon\Carbon::parse($period)->format('Ym') . '-';

        $last = JournalEntry::withTrashed()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function allowedOrgs()
    {
        $orgIds = auth()->user()->organizationIds();
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds));
    }

    private function accountsFor($organizations)
    {
        return Account::whereIn('organization_id', $organizations->pluck('id'))
            ->whereIn('account_type', ['aset', 'beban'])
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'organization_id', 'code', 'name', 'account_type']);
    }
}
