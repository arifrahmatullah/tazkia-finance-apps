<?php

namespace App\Http\Controllers;

use App\Models\BudgetPeriod;
use App\Models\IncomeEstimate;
use App\Models\IncomeEstimateDetail;
use App\Models\Organization;
use Illuminate\Http\Request;

class IncomeEstimateController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $query = IncomeEstimate::with(['organization', 'budgetPeriod'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds));

        if ($request->filled('organization_id')) {
            abort_unless($user->canAccessOrganization($request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('budget_period_id')) {
            $query->where('budget_period_id', $request->budget_period_id);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $estimates     = $query->latest()->paginate(10)->withQueryString();
        $budgetPeriods = BudgetPeriod::with(['organization'])
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Total estimasi aktif per periode aktif
        $periodSummaries = $budgetPeriods->map(function ($period) {
            $total = IncomeEstimate::where('budget_period_id', $period->id)
                ->where('is_active', true)
                ->sum('total_amount');
            $count = IncomeEstimate::where('budget_period_id', $period->id)
                ->where('is_active', true)
                ->count();
            return (object) [
                'period' => $period,
                'total'  => (float) $total,
                'count'  => $count,
            ];
        });

        return view('income-estimates.index', compact('estimates', 'organizations', 'budgetPeriods', 'periodSummaries'));
    }

    public function create()
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $defaultOrgId = $organizations->count() === 1 ? $organizations->first()->id : null;

        return view('income-estimates.create', compact('organizations', 'defaultOrgId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'description'     => 'required|string|max:255',
            'unit_price'      => 'required|numeric|min:0',
        ]);
        $data['unit'] = null;

        abort_unless($user->canAccessOrganization($data['organization_id']), 403);

        $activePeriod = BudgetPeriod::where('organization_id', $data['organization_id'])
            ->where('is_active', true)->first();

        if (!$activePeriod) {
            return back()->withInput()->withErrors([
                'organization_id' => 'Organisasi ini belum memiliki periode anggaran aktif.',
            ]);
        }

        $data['budget_period_id'] = $activePeriod->id;
        $data['total_amount']     = $data['unit_price'];

        IncomeEstimate::create($data);

        return redirect()->route('income-estimates.index')
            ->with('success', 'Estimasi pendapatan berhasil ditambahkan.');
    }

    public function show(IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);

        $incomeEstimate->load(['organization', 'budgetPeriod', 'details' => fn($q) => $q->orderBy('estimate_date')]);

        return view('income-estimates.show', compact('incomeEstimate'));
    }

    public function edit(IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);

        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();
        $budgetPeriods = BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->orderBy('name')->get();

        return view('income-estimates.edit', compact('incomeEstimate', 'organizations', 'budgetPeriods'));
    }

    public function update(Request $request, IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);

        $data = $request->validate([
            'organization_id'  => 'required|exists:organizations,id',
            'budget_period_id' => 'required|exists:budget_periods,id',
            'description'      => 'required|string|max:255',
            'unit_price'       => 'required|numeric|min:0',
        ]);
        $data['unit'] = null;

        $incomeEstimate->update($data);
        $incomeEstimate->recalculateTotal();

        return redirect()->route('income-estimates.show', $incomeEstimate)
            ->with('success', 'Estimasi pendapatan berhasil diperbarui.');
    }

    public function destroy(IncomeEstimate $incomeEstimate)
    {
        abort_unless(auth()->user()->canAccessOrganization($incomeEstimate->organization_id), 403);
        $incomeEstimate->delete();

        return redirect()->route('income-estimates.index')
            ->with('success', 'Estimasi pendapatan berhasil dihapus.');
    }
}
