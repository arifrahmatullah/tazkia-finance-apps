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
            'account_id'        => 'nullable|exists:accounts,id',
            'description'       => 'required|string|max:255',
            'unit_price'        => 'required|numeric|min:0',
        ]);

        $newItemTotal = (float) $validated['unit_price'];
        $currentTotal = $program->details()->sum('total_amount');
        $paguAmount   = $program->budgetAllocation->amount;

        if ($paguAmount > 0 && ($currentTotal + $newItemTotal) > $paguAmount) {
            $sisa = number_format($paguAmount - $currentTotal, 0, ',', '.');
            return back()->withInput()->withErrors([
                'unit_price' => "Nominal melebihi sisa pagu. Sisa: Rp {$sisa}",
            ]);
        }

        BudgetProgramDetail::create(array_merge($validated, ['quantity' => 1]));

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil ditambahkan.');
    }

    public function edit(BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load([
            'budgetProgram.budgetAllocation.department.organization',
            'budgetProgram.budgetAllocation.budgetPeriod',
            'account',
        ]);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $orgId    = $budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id;
        $accounts = \App\Models\Account::where('organization_id', $orgId)
            ->where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-program-details.edit', compact('budgetProgramDetail', 'accounts'));
    }

    public function update(Request $request, BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load('budgetProgram.budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'account_id'  => 'nullable|exists:accounts,id',
            'description' => 'required|string|max:255',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $program      = $budgetProgramDetail->budgetProgram;
        $newItemTotal = (float) $validated['unit_price'];
        $currentTotal = $program->details()->where('id', '!=', $budgetProgramDetail->id)->sum('total_amount');
        $paguAmount   = $program->budgetAllocation->amount;

        if ($paguAmount > 0 && ($currentTotal + $newItemTotal) > $paguAmount) {
            $sisa = number_format($paguAmount - $currentTotal, 0, ',', '.');
            return back()->withInput()->withErrors([
                'unit_price' => "Nominal melebihi sisa pagu. Sisa: Rp {$sisa}",
            ]);
        }

        $budgetProgramDetail->update(array_merge($validated, ['quantity' => 1]));

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil diperbarui.');
    }

    public function destroy(BudgetProgramDetail $budgetProgramDetail)
    {
        $program = $budgetProgramDetail->budgetProgram()->with('budgetAllocation.department')->first();

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        $budgetProgramDetail->delete();

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil dihapus.');
    }
}
