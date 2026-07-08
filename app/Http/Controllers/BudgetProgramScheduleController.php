<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgram;
use App\Models\BudgetProgramSchedule;
use Illuminate\Http\Request;

class BudgetProgramScheduleController extends Controller
{
    public function update(Request $request, BudgetProgramSchedule $schedule)
    {
        $schedule->load('budgetProgram.budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($schedule->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'estimated_date' => 'nullable|date',
            'notes'          => 'nullable|string|max:255',
        ]);

        $schedule->update($validated);

        return response()->json(['success' => true]);
    }

    public function bulkUpdate(Request $request, BudgetProgram $budgetProgram)
    {
        $budgetProgram->load('budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $request->validate([
            'start_date' => 'required|date',
            'pattern'    => 'required|in:monthly,weekly,quarterly,custom',
            'interval'   => 'nullable|integer|min:1',
        ]);

        $start   = \Carbon\Carbon::parse($request->start_date);
        $pattern = $request->pattern;
        $schedules = $budgetProgram->schedules()->orderBy('termin')->get();

        \DB::transaction(function () use ($schedules, $pattern, $start, $request) {
            foreach ($schedules as $i => $schedule) {
                $date = match ($pattern) {
                    'monthly'   => $start->copy()->addMonths($i),
                    'weekly'    => $start->copy()->addWeeks($i),
                    'quarterly' => $start->copy()->addMonths($i * 3),
                    'custom'    => $start->copy()->addDays($i * max(1, (int) $request->interval)),
                };
                $schedule->update(['estimated_date' => $date->toDateString()]);
            }
        });

        return redirect()
            ->route('budget-programs.show', $budgetProgram)
            ->with('success', 'Estimasi tanggal berhasil diisi otomatis.');
    }
}
