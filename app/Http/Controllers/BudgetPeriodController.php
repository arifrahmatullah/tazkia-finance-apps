<?php

namespace App\Http\Controllers;

use App\Models\BudgetPeriod;
use App\Models\Organization;
use Illuminate\Http\Request;

class BudgetPeriodController extends Controller
{
    public function index()
    {
        $orgIds = auth()->user()->organizationIds();

        $periods = BudgetPeriod::with('organization')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderBy('period_start', 'desc')
            ->get();

        return view('budget-periods.index', compact('periods'));
    }

    private function allowedOrgs(): \Illuminate\Database\Eloquent\Builder
    {
        $orgIds = auth()->user()->organizationIds();
        $query  = Organization::where('is_active', true);
        return $orgIds !== null ? $query->whereIn('id', $orgIds) : $query;
    }

    public function create()
    {
        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('budget-periods.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'code'            => 'required|string|max:20|unique:budget_periods,code',
            'name'            => 'required|string|max:150',
            'planning_start'  => 'nullable|date',
            'planning_end'    => 'nullable|date|after_or_equal:planning_start',
            'period_start'    => 'required|date',
            'period_end'      => 'required|date|after:period_start',
        ], [
            'period_end.after'            => 'Tanggal akhir anggaran harus setelah tanggal mulai.',
            'planning_end.after_or_equal' => 'Tanggal akhir perencanaan harus setelah tanggal mulai.',
        ]);

        $validated['is_active'] = true;
        $validated['code']      = strtoupper($validated['code']);

        \DB::transaction(function () use ($validated) {
            BudgetPeriod::where('organization_id', $validated['organization_id'])
                ->update(['is_active' => false]);

            BudgetPeriod::create($validated);
        });

        return redirect()->route('budget-periods.index')
            ->with('success', 'Periode anggaran berhasil ditambahkan. Periode lain di organisasi ini dinonaktifkan.');
    }

    public function edit(BudgetPeriod $budgetPeriod)
    {
        abort_unless(auth()->user()->canAccessOrganization($budgetPeriod->organization_id), 403);

        $organizations = $this->allowedOrgs()->orderBy('name')->get();
        return view('budget-periods.edit', compact('budgetPeriod', 'organizations'));
    }

    public function update(Request $request, BudgetPeriod $budgetPeriod)
    {
        abort_unless(auth()->user()->canAccessOrganization($budgetPeriod->organization_id), 403);
        abort_unless(auth()->user()->canAccessOrganization($request->organization_id), 403);
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'code'            => 'required|string|max:20|unique:budget_periods,code,' . $budgetPeriod->id,
            'name'            => 'required|string|max:150',
            'planning_start'  => 'nullable|date',
            'planning_end'    => 'nullable|date|after_or_equal:planning_start',
            'period_start'    => 'required|date',
            'period_end'      => 'required|date|after:period_start',
            'is_active'       => 'boolean',
        ], [
            'period_end.after'            => 'Tanggal akhir anggaran harus setelah tanggal mulai.',
            'planning_end.after_or_equal' => 'Tanggal akhir perencanaan harus setelah tanggal mulai.',
        ]);

        $isActive               = $request->boolean('is_active');
        $validated['is_active'] = $isActive;
        $validated['code']      = strtoupper($validated['code']);

        \DB::transaction(function () use ($budgetPeriod, $validated, $isActive) {
            if ($isActive) {
                BudgetPeriod::where('organization_id', $validated['organization_id'])
                    ->where('id', '!=', $budgetPeriod->id)
                    ->update(['is_active' => false]);
            }

            $budgetPeriod->update($validated);
        });

        $message = $isActive
            ? 'Periode anggaran berhasil diaktifkan. Periode lain di organisasi ini dinonaktifkan.'
            : 'Periode anggaran berhasil diperbarui.';

        return redirect()->route('budget-periods.index')
            ->with('success', $message);
    }

    public function activePeriod(Request $request)
    {
        $orgId = $request->organization_id;
        abort_unless(auth()->user()->canAccessOrganization($orgId), 403);

        $period = BudgetPeriod::where('organization_id', $orgId)
            ->where('is_active', true)
            ->first(['id', 'name', 'period_start', 'period_end']);

        return response()->json($period);
    }

    public function destroy(BudgetPeriod $budgetPeriod)
    {
        abort_unless(auth()->user()->canAccessOrganization($budgetPeriod->organization_id), 403);

        $budgetPeriod->delete();

        return redirect()->route('budget-periods.index')
            ->with('success', 'Periode anggaran berhasil dihapus.');
    }
}
