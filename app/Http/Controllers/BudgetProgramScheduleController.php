<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgram;
use App\Models\BudgetProgramSchedule;
use App\Models\BudgetProgramScheduleDetail;
use App\Services\BudgetProgramChangeService;
use Illuminate\Http\Request;

class BudgetProgramScheduleController extends Controller
{
    public function update(Request $request, BudgetProgramSchedule $schedule)
    {
        $schedule->load('budgetProgram.budgetAllocation.department', 'budgetProgram.budgetAllocation.budgetPeriod', 'budgetProgram.details');

        abort_unless(
            auth()->user()->canAccessOrganization($schedule->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'estimated_date' => 'nullable|date',
            'notes'          => 'nullable|string|max:255',
            'amount'         => 'nullable|numeric|min:0',
            'details'        => 'nullable|array',
            'details.*.budget_program_detail_id' => 'required_with:details|exists:budget_program_details,id',
            'details.*.unit_price'               => 'required_with:details|numeric|min:0',
        ]);

        $program         = $schedule->budgetProgram;
        $amountProvided  = $request->has('amount');
        $amountChanged   = $amountProvided && round((float) $validated['amount'], 2) !== round((float) $schedule->amount, 2);
        $detailsProvided = !empty($validated['details']);

        // Breakdown per rincian (kalau program py >1 rincian) wajib mencakup SEMUA rincian
        // program ini dan jumlahnya harus PERSIS sama dengan Nominal Termin Ini -- supaya
        // total termin dan rincian tidak pernah kontradiksi satu sama lain.
        if ($detailsProvided) {
            $targetAmount = $amountProvided ? (float) $validated['amount'] : (float) $schedule->amount;
            $sumDetails   = collect($validated['details'])->sum(fn($d) => (float) $d['unit_price']);

            if (round($sumDetails, 2) !== round($targetAmount, 2)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah breakdown rincian (Rp ' . number_format($sumDetails, 0, ',', '.') . ') harus sama persis dengan Nominal Termin Ini (Rp ' . number_format($targetAmount, 0, ',', '.') . ').',
                ], 422);
            }

            $providedIds = collect($validated['details'])->pluck('budget_program_detail_id')->sort()->values();
            $programDetailIds = $program->details->pluck('id')->sort()->values();
            if ($providedIds->all() !== $programDetailIds->all()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Breakdown harus mencakup semua rincian kegiatan program ini.',
                ], 422);
            }
        }

        if ($amountChanged) {
            $otherTotal = $program->schedules()->where('id', '!=', $schedule->id)->sum('amount');
            if (($otherTotal + (float) $validated['amount']) > $program->total_amount) {
                $sisa = number_format(max($program->total_amount - $otherTotal, 0), 0, ',', '.');
                return response()->json([
                    'success' => false,
                    'message' => "Total nominal per termin tidak boleh melebihi total pagu program. Sisa: Rp {$sisa}",
                ], 422);
            }
        }

        if (($amountChanged || $detailsProvided) && !$program->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $program, $request->user(), 'update_schedule', $schedule->id, $validated,
                "Ubah estimasi termin {$schedule->termin}: " . $program->name
            );

            return response()->json(['success' => true, 'pending' => true]);
        }

        \DB::transaction(function () use ($schedule, $validated, $detailsProvided) {
            $schedule->update($validated);

            if ($detailsProvided) {
                foreach ($validated['details'] as $d) {
                    BudgetProgramScheduleDetail::updateOrCreate(
                        [
                            'budget_program_schedule_id' => $schedule->id,
                            'budget_program_detail_id'   => $d['budget_program_detail_id'],
                        ],
                        ['unit_price' => $d['unit_price']]
                    );
                }
            }
        });

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
            'pattern'    => 'required|in:weekly,biweekly,monthly,quarterly,semiannual,annual,custom',
            'interval'   => 'nullable|integer|min:1',
        ]);

        $start   = \Carbon\Carbon::parse($request->start_date);
        $pattern = $request->pattern;
        $schedules = $budgetProgram->schedules()->orderBy('termin')->get();

        \DB::transaction(function () use ($schedules, $pattern, $start, $request) {
            foreach ($schedules as $i => $schedule) {
                $date = match ($pattern) {
                    'weekly'     => $start->copy()->addWeeks($i),
                    'biweekly'   => $start->copy()->addWeeks($i * 2),
                    'monthly'    => $start->copy()->addMonths($i),
                    'quarterly'  => $start->copy()->addMonths($i * 3),
                    'semiannual' => $start->copy()->addMonths($i * 6),
                    'annual'     => $start->copy()->addMonths($i * 12),
                    'custom'     => $start->copy()->addDays($i * max(1, (int) $request->interval)),
                };
                $schedule->update(['estimated_date' => $date->toDateString()]);
            }
        });

        return redirect()
            ->route('budget-programs.show', $budgetProgram)
            ->with('success', 'Estimasi tanggal berhasil diisi otomatis.');
    }
}
