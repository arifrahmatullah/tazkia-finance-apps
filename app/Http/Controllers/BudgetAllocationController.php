<?php

namespace App\Http\Controllers;

use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\Department;
use App\Models\IncomeEstimate;
use Illuminate\Http\Request;

class BudgetAllocationController extends Controller
{
    public function index(Request $request)
    {
        $orgIds    = auth()->user()->organizationIds();
        $periodId  = $request->input('budget_period_id');
        $search    = $request->input('search');

        // Periode yang boleh diakses user
        $periods = BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderByDesc('is_active')
            ->orderByDesc('period_start')
            ->get();

        // Default ke periode aktif jika tidak ada pilihan
        if (!$periodId && $periods->isNotEmpty()) {
            $activePeriod = $periods->firstWhere('is_active', true);
            $periodId     = $activePeriod?->id ?? $periods->first()->id;
        }

        $selectedPeriod = $periods->firstWhere('id', $periodId);

        $allocations = collect();
        $totalAmount = 0;
        $totalNett   = 0;

        if ($selectedPeriod) {
            abort_unless(auth()->user()->canAccessOrganization($selectedPeriod->organization_id), 403);

            $allocations = BudgetAllocation::with([
                    'department',
                    'programs' => fn($q) => $q->where('is_active', true)->orderBy('name'),
                    'programs.account',
                    'programs.details',
                ])
                ->where('budget_period_id', $selectedPeriod->id)
                ->where('is_active', true)
                ->whereHas('department', fn($d) => $d->where('is_active', true))
                ->when($search, fn($q) => $q->whereHas('department', fn($d) => $d
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                ))
                ->orderBy('department_id')
                ->get();

            $totalAmount = $allocations->sum('amount');
            $totalNett   = (float) BudgetAllocation::where('budget_period_id', $selectedPeriod->id)
                ->where('is_active', true)
                ->where('source', 'NETT')
                ->sum('amount');
        }

        $totalEstimate = $selectedPeriod
            ? (float) IncomeEstimate::where('budget_period_id', $selectedPeriod->id)
                ->where('is_active', true)
                ->sum('total_amount')
            : 0;

        return view('budget-allocations.index', compact(
            'periods', 'selectedPeriod', 'allocations', 'totalAmount', 'totalNett', 'totalEstimate'
        ));
    }

    private function allowedPeriods(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        return BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true);
    }

    private function allowedDepartments(string $orgId): \Illuminate\Database\Eloquent\Builder
    {
        return Department::where('organization_id', $orgId)
            ->where('has_budget', true)
            ->where('is_active', true);
    }

    public function create(Request $request)
    {
        $periods = $this->allowedPeriods()->orderByDesc('period_start')->get();
        $selectedPeriodId = $request->input('budget_period_id', $periods->first()?->id);
        $selectedPeriod   = $periods->firstWhere('id', $selectedPeriodId);

        $departments = collect();
        if ($selectedPeriod) {
            abort_unless(auth()->user()->canAccessOrganization($selectedPeriod->organization_id), 403);
            // Exclude departments that already have allocation in this period
            $assignedDeptIds = BudgetAllocation::where('budget_period_id', $selectedPeriod->id)
                ->pluck('department_id')->toArray();
            $departments = $this->allowedDepartments($selectedPeriod->organization_id)
                ->whereNotIn('id', $assignedDeptIds)
                ->orderBy('name')->get();
        }

        $totalEstimate  = $selectedPeriod
            ? (float) IncomeEstimate::where('budget_period_id', $selectedPeriod->id)->where('is_active', true)->sum('total_amount')
            : 0;
        // Hanya NETT aktif yang dihitung ke ceiling
        $totalAllocated = $selectedPeriod
            ? (float) BudgetAllocation::where('budget_period_id', $selectedPeriod->id)->where('source', 'NETT')->where('is_active', true)->sum('amount')
            : 0;

        return view('budget-allocations.create', compact('periods', 'selectedPeriod', 'departments', 'totalEstimate', 'totalAllocated'));
    }

    public function store(Request $request)
    {
        $period = BudgetPeriod::findOrFail($request->budget_period_id);
        abort_unless(auth()->user()->canAccessOrganization($period->organization_id), 403);

        $validated = $request->validate([
            'budget_period_id' => 'required|exists:budget_periods,id',
            'department_id'    => 'required|exists:departments,id',
            'amount'           => 'required|numeric|min:0',
            'percentage'       => 'nullable|numeric|min:0|max:100',
            'source'           => 'required|in:NETT,DEVIASI',
            'notes'            => 'nullable|string|max:500',
            'is_blocking'      => 'boolean',
        ], [
            'budget_period_id.required' => 'Periode anggaran wajib dipilih.',
            'department_id.required'    => 'Departemen wajib dipilih.',
            'amount.required'           => 'Jumlah pagu wajib diisi.',
        ]);

        $dept = Department::findOrFail($validated['department_id']);
        abort_unless($dept->organization_id === $period->organization_id, 403);

        // Cek duplikat
        $exists = BudgetAllocation::where('budget_period_id', $validated['budget_period_id'])
            ->where('department_id', $validated['department_id'])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['department_id' => 'Departemen ini sudah memiliki pagu di periode tersebut.']);
        }

        // Validasi ceiling: hanya pagu NETT aktif yang dihitung, DEVIASI bebas
        if ($validated['source'] === 'NETT') {
            $totalEstimate = (float) IncomeEstimate::where('budget_period_id', $validated['budget_period_id'])->where('is_active', true)->sum('total_amount');
            if ($totalEstimate > 0) {
                $currentNett = (float) BudgetAllocation::where('budget_period_id', $validated['budget_period_id'])
                    ->where('source', 'NETT')->where('is_active', true)->sum('amount');
                $newTotal = $currentNett + (float) $validated['amount'];
                if ($newTotal > $totalEstimate) {
                    return back()->withInput()->withErrors([
                        'amount' => 'Total pagu NETT Rp ' . number_format($newTotal, 0, ',', '.') .
                            ' melebihi estimasi pendapatan Rp ' . number_format($totalEstimate, 0, ',', '.') .
                            ' (kelebihan Rp ' . number_format($newTotal - $totalEstimate, 0, ',', '.') . ').',
                    ]);
                }
            }
        }

        if ($validated['source'] === 'NETT') {
            $validated['percentage'] = 0;
        }

        $validated['is_blocking'] = $request->boolean('is_blocking');
        $validated['is_active']   = true;

        BudgetAllocation::create($validated);

        return redirect()->route('budget-allocations.index', ['budget_period_id' => $validated['budget_period_id']])
            ->with('success', 'Pagu anggaran berhasil ditambahkan.');
    }

    public function edit(BudgetAllocation $budgetAllocation)
    {
        abort_unless(
            auth()->user()->canAccessOrganization($budgetAllocation->budgetPeriod->organization_id),
            403
        );

        $periods     = $this->allowedPeriods()->orderByDesc('period_start')->get();
        $departments = $this->allowedDepartments($budgetAllocation->budgetPeriod->organization_id)
            ->orderBy('name')->get();

        $totalEstimate  = (float) IncomeEstimate::where('budget_period_id', $budgetAllocation->budget_period_id)->where('is_active', true)->sum('total_amount');
        // Hanya NETT aktif yang dihitung ke ceiling, exclude record ini sendiri
        $totalAllocated = (float) BudgetAllocation::where('budget_period_id', $budgetAllocation->budget_period_id)
            ->where('source', 'NETT')
            ->where('is_active', true)
            ->where('id', '!=', $budgetAllocation->id)
            ->sum('amount');

        return view('budget-allocations.edit', compact('budgetAllocation', 'periods', 'departments', 'totalEstimate', 'totalAllocated'));
    }

    public function update(Request $request, BudgetAllocation $budgetAllocation)
    {
        abort_unless(
            auth()->user()->canAccessOrganization($budgetAllocation->budgetPeriod->organization_id),
            403
        );

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0',
            'percentage'  => 'nullable|numeric|min:0|max:100',
            'source'      => 'required|in:NETT,DEVIASI',
            'notes'       => 'nullable|string|max:500',
            'is_blocking' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        // Validasi ceiling: hanya pagu NETT aktif yang dihitung, DEVIASI bebas
        if ($validated['source'] === 'NETT') {
            $totalEstimate = (float) IncomeEstimate::where('budget_period_id', $budgetAllocation->budget_period_id)->where('is_active', true)->sum('total_amount');
            if ($totalEstimate > 0) {
                $otherNett = (float) BudgetAllocation::where('budget_period_id', $budgetAllocation->budget_period_id)
                    ->where('source', 'NETT')
                    ->where('is_active', true)
                    ->where('id', '!=', $budgetAllocation->id)
                    ->sum('amount');
                $newTotal = $otherNett + (float) $validated['amount'];
                if ($newTotal > $totalEstimate) {
                    return back()->withInput()->withErrors([
                        'amount' => 'Total pagu NETT Rp ' . number_format($newTotal, 0, ',', '.') .
                            ' melebihi estimasi pendapatan Rp ' . number_format($totalEstimate, 0, ',', '.') .
                            ' (kelebihan Rp ' . number_format($newTotal - $totalEstimate, 0, ',', '.') . ').',
                    ]);
                }
            }
        }

        if ($validated['source'] === 'NETT') {
            $validated['percentage'] = 0;
        }

        $validated['is_blocking'] = $request->boolean('is_blocking');
        $validated['is_active']   = $request->boolean('is_active');

        $budgetAllocation->update($validated);

        return redirect()->route('budget-allocations.index', [
            'budget_period_id' => $budgetAllocation->budget_period_id,
        ])->with('success', 'Pagu anggaran berhasil diperbarui.');
    }

    public function destroy(BudgetAllocation $budgetAllocation)
    {
        abort_unless(
            auth()->user()->canAccessOrganization($budgetAllocation->budgetPeriod->organization_id),
            403
        );

        $periodId = $budgetAllocation->budget_period_id;
        $budgetAllocation->delete();

        return redirect()->route('budget-allocations.index', ['budget_period_id' => $periodId])
            ->with('success', 'Pagu anggaran berhasil dihapus.');
    }

    public function getDepartments(Request $request)
    {
        $period = BudgetPeriod::findOrFail($request->budget_period_id);
        abort_unless(auth()->user()->canAccessOrganization($period->organization_id), 403);

        $assignedIds = BudgetAllocation::where('budget_period_id', $period->id)
            ->pluck('department_id')->toArray();

        $departments = Department::where('organization_id', $period->organization_id)
            ->where('has_budget', true)
            ->where('is_active', true)
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($departments);
    }
}
