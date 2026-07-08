<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\Department;
use Illuminate\Http\Request;

class BudgetProgramController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $query = BudgetProgram::with([
            'budgetAllocation.department',
            'budgetAllocation.budgetPeriod',
            'account',
            'details',
        ])->whereHas('budgetAllocation.department', function ($q) use ($orgIds) {
            if ($orgIds !== null) {
                $q->whereIn('organization_id', $orgIds);
            }
            $q->where('is_active', true);
        });

        if ($request->filled('budget_period_id')) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('budget_period_id', $request->budget_period_id));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('department_id', $request->department_id));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();

        $budgetPeriods = BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->orderBy('name')->get();

        $departments = Department::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->where('has_budget', true)->orderBy('name')->get();

        // Ringkasan per alokasi dari program yang tampil (untuk kartu di luar tabel)
        $allocationIds = $programs->pluck('budget_allocation_id')->unique();
        $allocationSummaries = \App\Models\BudgetAllocation::with(['department', 'budgetPeriod'])
            ->whereIn('id', $allocationIds)
            ->get()
            ->map(function ($alloc) {
                $programs = \App\Models\BudgetProgram::with('details')
                    ->where('budget_allocation_id', $alloc->id)->get();
                $terpakai = $programs->sum(fn($p) => (float) $p->total_amount);
                return [
                    'dept'     => $alloc->department->name,
                    'periode'  => $alloc->budgetPeriod->name,
                    'pagu'     => (float) $alloc->amount,
                    'terpakai' => $terpakai,
                    'sisa'     => (float) $alloc->amount - $terpakai,
                ];
            });

        return view('budget-programs.index', compact('programs', 'budgetPeriods', 'departments', 'allocationSummaries'));
    }

    public function create()
    {
        $user     = auth()->user();
        $employee = $user->employee()->with('activePosition.position.department')->first();

        $department = $employee?->activePosition?->position?->department;

        abort_if(!$department, 403, 'Jabatan aktifmu belum diatur. Hubungi admin.');

        $allocation = BudgetAllocation::with(['department', 'budgetPeriod'])
            ->whereHas('budgetPeriod', fn($q) => $q->where('is_active', true))
            ->where('department_id', $department->id)
            ->where('is_active', true)
            ->first();

        abort_if(!$allocation, 403, 'Pagu anggaran untuk departemen ' . $department->name . ' belum tersedia. Hubungi bagian Keuangan.');

        // Hitung sisa pagu setelah dikurangi program yang sudah ada
        $usedByPrograms = BudgetProgram::with('details')
            ->where('budget_allocation_id', $allocation->id)
            ->get()
            ->sum(fn($p) => (float) $p->total_amount);
        $sisaAlokasi = (float) $allocation->amount - $usedByPrograms;

        $accounts = Account::where('organization_id', $department->organization_id)
            ->where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.create', compact('allocation', 'accounts', 'department', 'sisaAlokasi'));
    }

    public function store(Request $request)
    {
        $allocation = BudgetAllocation::with('department')->findOrFail($request->budget_allocation_id);

        abort_unless(
            auth()->user()->canAccessOrganization($allocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'budget_allocation_id' => 'required|exists:budget_allocations,id',
            'name'                 => 'required|string|max:255',
            'notes'                => 'nullable|string|max:1000',
            'lines'                => 'nullable|array',
            'lines.*.description'  => 'nullable|string|max:255',
            'lines.*.account_id'   => 'nullable|exists:accounts,id',
            'lines.*.nominal'      => 'nullable|numeric|min:0',
        ]);

        $lines = collect($validated['lines'] ?? [])
            ->filter(fn($l) => !empty($l['description']));

        $grandTotal   = $lines->sum(fn($l) => (float) ($l['nominal'] ?? 0));
        $pagu         = (float) $allocation->amount;
        $usedByOthers = BudgetProgram::with('details')
            ->where('budget_allocation_id', $allocation->id)
            ->get()
            ->sum(fn($p) => (float) $p->total_amount);
        $sisaAlokasi = $pagu - $usedByOthers;

        if ($pagu > 0 && $grandTotal > $sisaAlokasi) {
            return back()->withInput()->withErrors([
                'lines' => "Total rincian (Rp " . number_format($grandTotal, 0, ',', '.') . ") melebihi sisa pagu (Rp " . number_format($sisaAlokasi, 0, ',', '.') . ").",
            ]);
        }

        $program = BudgetProgram::create([
            'budget_allocation_id' => $validated['budget_allocation_id'],
            'name'                 => $validated['name'],
            'notes'                => $validated['notes'] ?? null,
            'is_active'            => true,
        ]);

        foreach ($lines as $line) {
            $nominal = (float) ($line['nominal'] ?? 0);
            $program->details()->create([
                'account_id'  => $line['account_id'] ?? null,
                'description' => $line['description'],
                'quantity'    => 1,
                'unit_price'  => $nominal,
            ]);
        }

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function show(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod', 'account', 'details.account']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $orgId    = $budgetProgram->budgetAllocation->department->organization_id;
        $accounts = Account::where('organization_id', $orgId)
            ->where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.show', compact('budgetProgram', 'accounts'));
    }

    public function edit(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod', 'account']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $accounts = Account::where('organization_id', $budgetProgram->budgetAllocation->department->organization_id)
            ->where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.edit', compact('budgetProgram', 'accounts'));
    }

    public function update(Request $request, BudgetProgram $budgetProgram)
    {
        $budgetProgram->load('budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'account_id' => 'nullable|exists:accounts,id',
            'name'       => 'required|string|max:255',
            'notes'      => 'nullable|string|max:1000',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $budgetProgram->update($validated);

        return redirect()
            ->route('budget-programs.index')
            ->with('success', 'Program kerja berhasil diperbarui.');
    }

    public function destroy(BudgetProgram $budgetProgram)
    {
        $allocationId = $budgetProgram->budget_allocation_id;
        $budgetProgram->load('budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $budgetProgram->delete();

        return redirect()
            ->route('budget-programs.index')
            ->with('success', 'Program kerja berhasil dihapus.');
    }
}
