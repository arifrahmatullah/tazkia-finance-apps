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

        return view('budget-programs.index', compact('programs', 'budgetPeriods', 'departments'));
    }

    public function create(Request $request)
    {
        $allocation = BudgetAllocation::with(['department.organization', 'budgetPeriod'])
            ->findOrFail($request->budget_allocation_id);

        abort_unless(
            auth()->user()->canAccessOrganization($allocation->department->organization_id),
            403
        );

        $accounts = Account::where('organization_id', $allocation->department->organization_id)
            ->where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.create', compact('allocation', 'accounts'));
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
            'account_id'           => 'nullable|exists:accounts,id',
            'name'                 => 'required|string|max:255',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = true;

        BudgetProgram::create($validated);

        return redirect()
            ->route('budget-allocations.index', ['budget_period_id' => $allocation->budget_period_id])
            ->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function show(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod', 'account', 'details']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        return view('budget-programs.show', compact('budgetProgram'));
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
            ->route('budget-allocations.index', ['budget_period_id' => $budgetProgram->budgetAllocation->budget_period_id])
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

        $periodId = $budgetProgram->budgetAllocation->budget_period_id;
        $budgetProgram->delete();

        return redirect()
            ->route('budget-allocations.index', ['budget_period_id' => $periodId])
            ->with('success', 'Program kerja berhasil dihapus.');
    }
}
