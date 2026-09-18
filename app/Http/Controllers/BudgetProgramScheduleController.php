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

        // Dibekukan SEBELUM ada perubahan apapun -- total_amount & nominal_per_termin itu
        // accessor (dihitung ulang tiap diakses dari SUM rincian), jadi begitu rincian mulai
        // ditambah nilainya bisa langsung geser kalau tidak ditangkap duluan.
        $currentNominalPerTermin = (float) $program->nominal_per_termin;
        $currentProgramTotal     = (float) $program->total_amount;
        $growExcess              = null;

        if ($amountChanged) {
            // Sum() SQL mentah mengabaikan termin yang 'amount'-nya masih NULL (belum pernah
            // disimpan eksplisit, masih pakai default nominal_per_termin) -- dianggap 0, padahal
            // seharusnya tetap terhitung nominal_per_termin.
            $otherTotal = $program->schedules()
                ->where('id', '!=', $schedule->id)
                ->get(['amount'])
                ->sum(fn($s) => $s->amount !== null ? (float) $s->amount : $currentNominalPerTermin);

            $requiredTotal = $otherTotal + (float) $validated['amount'];

            if ($requiredTotal > $currentProgramTotal) {
                // Termin ini butuh lebih dari total program SAAT INI -- bukan langsung ditolak,
                // total program boleh IKUT BERTAMBAH menyesuaikan (lewat rincian, lihat
                // BudgetProgram::growTotalAmountBy()), selama sisa alokasi anggaran departemen
                // (dipakai bersama semua program dalam alokasi yang sama, lihat
                // BudgetProgramController::show()'s $sisaAlokasi) masih cukup menampungnya.
                // Termin LAIN yang masih ikut default tidak boleh diam-diam ikut naik gara-gara
                // nominal_per_termin (rata-rata) bergeser -- makanya dikunci ke nilai sekarang.
                $otherProgramsTotal = BudgetProgram::where('budget_allocation_id', $program->budget_allocation_id)
                    ->where('id', '!=', $program->id)
                    ->get()
                    ->sum(fn($p) => (float) $p->total_amount);

                $maxProgramTotal = (float) $program->budgetAllocation->amount - $otherProgramsTotal;

                if ($requiredTotal > $maxProgramTotal) {
                    $sisaAlokasi = number_format(max($maxProgramTotal - $otherTotal, 0), 0, ',', '.');
                    return response()->json([
                        'success' => false,
                        'message' => "Kenaikan ini butuh total program jadi Rp " . number_format($requiredTotal, 0, ',', '.') .
                            ", tapi sisa alokasi anggaran departemen tidak cukup. Sisa yang bisa dipakai buat termin ini: Rp {$sisaAlokasi}",
                    ], 422);
                }

                $growExcess = $requiredTotal - $currentProgramTotal;
            }
        }

        if (($amountChanged || $detailsProvided) && !$program->isWithinPlanningWindow()) {
            $payload = $validated;
            if ($growExcess !== null) {
                $payload['_grow_excess'] = $growExcess;
                $payload['_lock_nominal_per_termin'] = $currentNominalPerTermin;
            }

            app(BudgetProgramChangeService::class)->requestChange(
                $program, $request->user(), 'update_schedule', $schedule->id, $payload,
                "Ubah estimasi termin {$schedule->termin}: " . $program->name
            );

            return response()->json(['success' => true, 'pending' => true]);
        }

        \DB::transaction(function () use ($schedule, $validated, $detailsProvided, $program, $growExcess, $currentNominalPerTermin) {
            if ($growExcess !== null) {
                $program->lockImplicitScheduleAmounts($schedule->id, $currentNominalPerTermin);
                $program->growTotalAmountBy($growExcess);
            }

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

        return response()->json([
            'success'           => true,
            'new_program_total' => $growExcess !== null ? $currentProgramTotal + $growExcess : null,
        ]);
    }

    // Geser saldo antar termin DALAM program yang sama -- total program tidak berubah sama
    // sekali, jadi tidak perlu cek sisa alokasi anggaran departemen (beda dari update() di atas
    // yang bisa menambah total). Dipakai buat mindahin sisa termin yang sudah lewat/tidak
    // terpakai ke termin lain yang butuh lebih, tanpa keliru dianggap "kebutuhan baru".
    public function transfer(Request $request, BudgetProgram $budgetProgram)
    {
        $budgetProgram->load('budgetAllocation.department', 'budgetAllocation.budgetPeriod');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'from_schedule_id' => 'required|exists:budget_program_schedules,id',
            'to_schedule_id'   => 'required|different:from_schedule_id|exists:budget_program_schedules,id',
            'amount'           => 'required|numeric|min:0.01',
        ]);

        $from = $budgetProgram->schedules()->findOrFail($validated['from_schedule_id']);
        $to   = $budgetProgram->schedules()->findOrFail($validated['to_schedule_id']);

        $nominalPerTermin = (float) $budgetProgram->nominal_per_termin;
        $fromCeiling = $from->amount !== null ? (float) $from->amount : $nominalPerTermin;
        $toCeiling   = $to->amount !== null ? (float) $to->amount : $nominalPerTermin;

        $fromUsed = (float) $from->fundRequests()->whereNotIn('status', \App\Models\FundRequest::VOID_STATUSES)->sum('amount');
        $available = $fromCeiling - $fromUsed;

        if ((float) $validated['amount'] > $available + 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Termin sumber cuma punya sisa Rp ' . number_format(max($available, 0), 0, ',', '.') . ' yang bisa dipindah.',
            ], 422);
        }

        $newFromAmount = round($fromCeiling - $validated['amount'], 2);
        $newToAmount   = round($toCeiling + $validated['amount'], 2);

        if (!$budgetProgram->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $budgetProgram, $request->user(), 'transfer_schedule', $from->id,
                ['to_schedule_id' => $to->id, 'from_amount' => $newFromAmount, 'to_amount' => $newToAmount],
                'Pindahkan Rp ' . number_format($validated['amount'], 0, ',', '.') . " dari Termin {$from->termin} ke Termin {$to->termin}: {$budgetProgram->name}"
            );

            return response()->json(['success' => true, 'pending' => true]);
        }

        \DB::transaction(function () use ($from, $to, $newFromAmount, $newToAmount) {
            $from->update(['amount' => $newFromAmount]);
            $to->update(['amount' => $newToAmount]);
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
