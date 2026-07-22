<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\FundRefund;
use App\Models\FundRequest;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    // Laporan Pengajuan Dana — rekap seluruh pengajuan (non-draft) untuk keuangan/pimpinan
    public function fundRequests(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $orgIds = auth()->user()->organizationIds();

        $base = FundRequest::query()
            ->where('status', '!=', 'draft')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($dateFrom, fn($q) => $q->whereDate('submitted_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('submitted_at', '<=', $dateTo))
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('status'), function ($q) use ($request) {
                match ($request->status) {
                    'pending'   => $q->where('status', 'pending'),
                    'approved'  => $q->where('status', 'approved')->whereNull('disbursed_at'),
                    'disbursed' => $q->whereNotNull('disbursed_at'),
                    'rejected'  => $q->where('status', 'rejected'),
                    default     => null,
                };
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('reference', 'like', $s)
                    ->orWhere('title', 'like', $s)
                    ->orWhereHas('requester', fn($rq) => $rq->where('name', 'like', $s)));
            });

        $summary = (clone $base)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_amount,
            SUM(status = 'pending') as pending_count,
            SUM(status = 'approved' AND disbursed_at IS NULL) as approved_count,
            SUM(status = 'rejected') as rejected_count,
            SUM(disbursed_at IS NOT NULL) as disbursed_count,
            COALESCE(SUM(CASE WHEN disbursed_at IS NOT NULL THEN amount ELSE 0 END), 0) as disbursed_amount
        ")->first();

        $perOrg = $this->recapPerOrganization($base);

        $fundRequests = (clone $base)
            ->with(['organization', 'department', 'requester', 'budgetProgram'])
            ->orderByDesc('submitted_at')
            ->paginate(15)->withQueryString();

        $organizations = $this->organizationOptions($orgIds);

        return view('reports.fund-requests', compact(
            'fundRequests', 'summary', 'perOrg', 'organizations', 'dateFrom', 'dateTo'
        ));
    }

    // Laporan Pencairan Dana — rekap dana yang sudah dicairkan
    public function disbursements(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $orgIds = auth()->user()->organizationIds();

        $base = FundRequest::query()
            ->whereNotNull('disbursed_at')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($dateFrom, fn($q) => $q->whereDate('disbursed_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('disbursed_at', '<=', $dateTo))
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('account_id'), fn($q) => $q->where('disburse_account_id', $request->account_id))
            ->when($request->filled('proof'), function ($q) use ($request) {
                $request->proof === 'ada'
                    ? $q->whereHas('disbursementProofs')
                    : $q->whereDoesntHave('disbursementProofs');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('reference', 'like', $s)
                    ->orWhere('title', 'like', $s)
                    ->orWhereHas('requester', fn($rq) => $rq->where('name', 'like', $s)));
            });

        $summary = (clone $base)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_amount
        ")->first();

        $missingProofCount = (clone $base)->whereDoesntHave('disbursementProofs')->count();

        $perOrg = $this->recapPerOrganization($base);

        // Rekap per rekening sumber pencairan
        $perAccount = (clone $base)
            ->selectRaw('disburse_account_id, COUNT(*) as jumlah, COALESCE(SUM(amount), 0) as total')
            ->groupBy('disburse_account_id')
            ->orderByDesc('total')
            ->get();
        $accountNames = Account::whereIn('id', $perAccount->pluck('disburse_account_id')->filter())
            ->get(['id', 'code', 'name'])->keyBy('id');

        $fundRequests = (clone $base)
            ->with(['organization', 'requester', 'disburseAccount', 'disbursementProofs'])
            ->orderByDesc('disbursed_at')
            ->paginate(15)->withQueryString();

        $organizations = $this->organizationOptions($orgIds);

        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('reports.disbursements', compact(
            'fundRequests', 'summary', 'missingProofCount', 'perOrg', 'perAccount', 'accountNames',
            'organizations', 'bankAccounts', 'dateFrom', 'dateTo'
        ));
    }

    // Laporan Realisasi Anggaran — pagu & anggaran program kerja vs dana yang sudah dicairkan
    public function budgetRealization(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();

        $periods = BudgetPeriod::with('organization:id,name')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderByDesc('period_start')
            ->get();

        $period = $request->filled('budget_period_id')
            ? $periods->firstWhere('id', $request->budget_period_id)
            : null;
        $period ??= $periods->firstWhere('is_active', true) ?? $periods->first();

        if (!$period) {
            return view('reports.budget-realization', [
                'periods' => $periods, 'period' => null, 'groups' => collect(),
                'departments' => collect(), 'totals' => null,
            ]);
        }

        // Departemen yang punya alokasi pada periode ini (untuk opsi filter)
        $departments = BudgetAllocation::with('department:id,name')
            ->where('budget_period_id', $period->id)
            ->get()
            ->pluck('department')->filter()->unique('id')->sortBy('name')->values();

        $allocations = BudgetAllocation::with('department:id,name')
            ->where('budget_period_id', $period->id)
            ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id))
            ->get();

        $programs = BudgetProgram::whereIn('budget_allocation_id', $allocations->pluck('id'))
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->with('account:id,code,name')
            ->withSum('details as budget_total', 'total_amount')
            ->orderBy('name')
            ->get();

        $programIds = $programs->pluck('id');

        // Realisasi: pengajuan yang sudah dicairkan, per program
        $realized = FundRequest::whereIn('budget_program_id', $programIds)
            ->whereNotNull('disbursed_at')
            ->selectRaw('budget_program_id, COALESCE(SUM(amount), 0) as total, COUNT(*) as jumlah')
            ->groupBy('budget_program_id')
            ->get()->keyBy('budget_program_id');

        // Dana proses: pengajuan pending/approved yang belum dicairkan
        $inProcess = FundRequest::whereIn('budget_program_id', $programIds)
            ->whereNull('disbursed_at')
            ->whereIn('status', ['pending', 'approved'])
            ->selectRaw('budget_program_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('budget_program_id')
            ->get()->keyBy('budget_program_id');

        // Pengembalian dana terkonfirmasi mengurangi realisasi (realisasi netto)
        $refunded = FundRefund::where('fund_refunds.status', 'confirmed')
            ->join('fund_requests', 'fund_requests.id', '=', 'fund_refunds.fund_request_id')
            ->whereIn('fund_requests.budget_program_id', $programIds)
            ->selectRaw('fund_requests.budget_program_id as pid, COALESCE(SUM(fund_refunds.amount), 0) as total')
            ->groupBy('fund_requests.budget_program_id')
            ->get()->keyBy('pid');

        $groups = $allocations->map(function ($alloc) use ($programs, $realized, $inProcess, $refunded) {
            $rows = $programs->where('budget_allocation_id', $alloc->id)->values()->map(function ($p) use ($realized, $inProcess, $refunded) {
                $budget   = (float) ($p->budget_total ?? 0);
                $cair     = (float) ($realized[$p->id]->total ?? 0);
                $kembali  = (float) ($refunded[$p->id]->total ?? 0);
                $netto    = $cair - $kembali;

                return (object) [
                    'name'       => $p->name,
                    'type_label' => $p->type_label,
                    'account'    => $p->account,
                    'budget'     => $budget,
                    'disbursed'  => $cair,
                    'refunded'   => $kembali,
                    'realized'   => $netto,
                    'in_process' => (float) ($inProcess[$p->id]->total ?? 0),
                    'count'      => (int) ($realized[$p->id]->jumlah ?? 0),
                    'remaining'  => $budget - $netto,
                    'pct'        => $budget > 0 ? round($netto / $budget * 100, 1) : null,
                ];
            });

            return (object) [
                'department' => $alloc->department?->name ?? '-',
                'pagu'       => (float) $alloc->amount,
                'programs'   => $rows,
                'budget'     => $rows->sum('budget'),
                'realized'   => $rows->sum('realized'),
                'in_process' => $rows->sum('in_process'),
                'remaining'  => $rows->sum('budget') - $rows->sum('realized'),
            ];
        })
        // Saat mencari program, sembunyikan departemen tanpa hasil
        ->filter(fn($g) => !$request->filled('search') || $g->programs->isNotEmpty())
        ->sortBy('department')->values();

        $totals = (object) [
            'pagu'       => $groups->sum('pagu'),
            'budget'     => $groups->sum('budget'),
            'realized'   => $groups->sum('realized'),
            'in_process' => $groups->sum('in_process'),
            'remaining'  => $groups->sum('remaining'),
            'pct'        => $groups->sum('budget') > 0 ? round($groups->sum('realized') / $groups->sum('budget') * 100, 1) : null,
        ];

        return view('reports.budget-realization', compact('periods', 'period', 'groups', 'departments', 'totals'));
    }

    // Buku Besar — riwayat mutasi debit/kredit per akun dengan saldo berjalan (hanya jurnal posted)
    public function generalLedger(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        $accounts = Account::where('organization_id', $orgId)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'account_type', 'normal_balance', 'is_active']);

        [$dateFrom, $dateTo] = $this->dateRange($request);

        $account = $request->filled('account_id')
            ? $accounts->firstWhere('id', $request->account_id)
            : null;

        $lines = collect();
        $opening = 0.0;
        $totals = null;

        if ($account) {
            // Saldo dinyatakan menurut saldo normal akun: debit-normal = D−K, kredit-normal = K−D
            $sign = $account->normal_balance === 'kredit' ? -1 : 1;

            if ($dateFrom) {
                $pre = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                        ->whereDate('entry_date', '<', $dateFrom))
                    ->selectRaw('COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
                    ->first();
                $opening = $sign * ((float) $pre->d - (float) $pre->c);
            }

            $lines = JournalEntryLine::with('journalEntry:id,entry_date,reference,description')
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('entry_date', '>=', $dateFrom))
                    ->when($dateTo, fn($qq) => $qq->whereDate('entry_date', '<=', $dateTo)))
                ->get()
                ->sortBy(fn($l) => [
                    $l->journalEntry->entry_date->toDateString(),
                    $l->journalEntry->reference,
                    $l->sort_order,
                ])->values();

            $running = $opening;
            foreach ($lines as $line) {
                $running += $sign * ((float) $line->debit - (float) $line->credit);
                $line->running_balance = $running;
            }

            $totals = (object) [
                'debit'   => $lines->sum('debit'),
                'credit'  => $lines->sum('credit'),
                'closing' => $running,
            ];
        }

        return view('reports.general-ledger', compact(
            'organizations', 'orgId', 'accounts', 'account', 'lines',
            'opening', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    // Neraca Saldo — saldo awal, mutasi, dan saldo akhir semua akun pada satu periode (hanya jurnal posted)
    public function trialBalance(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        [$dateFrom, $dateTo] = $this->trialBalanceDateRange($request);
        $showZero = $request->boolean('show_zero');

        $rows = collect();
        $totals = null;

        if ($orgId) {
            $accountList = Account::where('organization_id', $orgId)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'account_type', 'normal_balance']);

            $opening  = $this->sumsPerAccount($accountList->pluck('id'), lt: $dateFrom);
            $mutation = $this->sumsPerAccount($accountList->pluck('id'), from: $dateFrom, to: $dateTo);

            $totals = (object) [
                'opening_debit' => 0.0, 'opening_credit' => 0.0,
                'mutation_debit' => 0.0, 'mutation_credit' => 0.0,
                'closing_debit' => 0.0, 'closing_credit' => 0.0,
            ];

            $rows = $accountList->map(function ($acc) use ($opening, $mutation, $totals) {
                $op = $opening[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];
                $mu = $mutation[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];

                $openingNet = $op['debit'] - $op['credit'];
                $closingNet = $openingNet + $mu['debit'] - $mu['credit'];

                $row = (object) [
                    'account'         => $acc,
                    'opening_debit'   => max($openingNet, 0.0),
                    'opening_credit'  => max(-$openingNet, 0.0),
                    'mutation_debit'  => $mu['debit'],
                    'mutation_credit' => $mu['credit'],
                    'closing_debit'   => max($closingNet, 0.0),
                    'closing_credit'  => max(-$closingNet, 0.0),
                ];

                $totals->opening_debit   += $row->opening_debit;
                $totals->opening_credit  += $row->opening_credit;
                $totals->mutation_debit  += $row->mutation_debit;
                $totals->mutation_credit += $row->mutation_credit;
                $totals->closing_debit   += $row->closing_debit;
                $totals->closing_credit  += $row->closing_credit;

                return $row;
            });

            if (!$showZero) {
                $rows = $rows->filter(fn($r) => $r->opening_debit || $r->opening_credit
                    || $r->mutation_debit || $r->mutation_credit || $r->closing_debit || $r->closing_credit);
            }

            $rows = $rows->values();
        }

        return view('reports.trial-balance', compact(
            'organizations', 'orgId', 'rows', 'totals', 'dateFrom', 'dateTo', 'showZero'
        ));
    }

    // Jumlah debit/kredit per akun dari jurnal posted, dibatasi salah satu: lt (sebelum tanggal), atau from/to (rentang)
    private function sumsPerAccount($accountIds, ?string $lt = null, ?string $from = null, ?string $to = null): array
    {
        if ($accountIds->isEmpty()) {
            return [];
        }

        $sums = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($lt, $from, $to) {
                $q->where('status', 'posted')
                    ->when($lt, fn($qq) => $qq->whereDate('entry_date', '<', $lt))
                    ->when($from, fn($qq) => $qq->whereDate('entry_date', '>=', $from))
                    ->when($to, fn($qq) => $qq->whereDate('entry_date', '<=', $to));
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
            ->groupBy('account_id')
            ->get();

        return $sums->mapWithKeys(fn($r) => [
            $r->account_id => ['debit' => (float) $r->d, 'credit' => (float) $r->c],
        ])->all();
    }

    // Default: awal tahun berjalan s.d. hari ini. Jika form sudah pernah disubmit, hormati input user (kosong = tanpa batas).
    private function trialBalanceDateRange(Request $request): array
    {
        if ($request->has('date_from') || $request->has('date_to')) {
            return [$request->input('date_from'), $request->input('date_to')];
        }

        return [now()->startOfYear()->toDateString(), now()->toDateString()];
    }

    // Default: bulan berjalan. Jika form sudah pernah disubmit, hormati input user (kosong = tanpa batas).
    private function dateRange(Request $request): array
    {
        if ($request->has('date_from') || $request->has('date_to')) {
            return [$request->input('date_from'), $request->input('date_to')];
        }

        return [now()->startOfMonth()->toDateString(), now()->toDateString()];
    }

    private function recapPerOrganization($base)
    {
        $rows = (clone $base)
            ->selectRaw('organization_id, COUNT(*) as jumlah, COALESCE(SUM(amount), 0) as total')
            ->groupBy('organization_id')
            ->orderByDesc('total')
            ->get();

        $names = Organization::whereIn('id', $rows->pluck('organization_id'))->pluck('name', 'id');

        return $rows->map(fn($r) => (object) [
            'name'   => $names[$r->organization_id] ?? '-',
            'jumlah' => $r->jumlah,
            'total'  => $r->total,
        ]);
    }

    private function organizationOptions($orgIds)
    {
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get(['id', 'name']);
    }
}
