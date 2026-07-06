<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgram;
use App\Models\BudgetProgramDetail;
use Illuminate\Http\Request;

class BudgetProgramDetailController extends Controller
{
    public function store(Request $request)
    {
        $program = BudgetProgram::with('budgetAllocation.department')->findOrFail($request->budget_program_id);

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'budget_program_id' => 'required|exists:budget_programs,id',
            'description'       => 'required|string|max:255',
            'quantity'          => 'required|numeric|min:0.01',
            'unit'              => 'nullable|string|max:50',
            'unit_price'        => 'required|numeric|min:0',
            'notes'             => 'nullable|string|max:500',
        ]);

        BudgetProgramDetail::create($validated);

        return redirect()
            ->route('budget-allocations.index', ['budget_period_id' => $program->budgetAllocation->budget_period_id])
            ->with('success', 'Rincian berhasil ditambahkan.');
    }

    public function edit(BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load(['budgetProgram.budgetAllocation.department.organization', 'budgetProgram.budgetAllocation.budgetPeriod']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        return view('budget-program-details.edit', compact('budgetProgramDetail'));
    }

    public function update(Request $request, BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load('budgetProgram.budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'quantity'    => 'required|numeric|min:0.01',
            'unit'        => 'nullable|string|max:50',
            'unit_price'  => 'required|numeric|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);

        $budgetProgramDetail->update($validated);

        return redirect()
            ->route('budget-allocations.index', ['budget_period_id' => $budgetProgramDetail->budgetProgram->budgetAllocation->budget_period_id])
            ->with('success', 'Rincian berhasil diperbarui.');
    }

    public function destroy(BudgetProgramDetail $budgetProgramDetail)
    {
        $program = $budgetProgramDetail->budgetProgram()->with('budgetAllocation.department')->first();

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        $periodId = $program->budgetAllocation->budget_period_id;
        $budgetProgramDetail->delete();

        return redirect()
            ->route('budget-allocations.index', ['budget_period_id' => $periodId])
            ->with('success', 'Rincian berhasil dihapus.');
    }
}
