<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgram;
use App\Models\BudgetProgramDetail;
use App\Services\BudgetProgramChangeService;
use Illuminate\Http\Request;

class BudgetProgramDetailController extends Controller
{
    public function store(Request $request)
    {
        $program = BudgetProgram::with('budgetAllocation.department', 'budgetAllocation.budgetPeriod', 'schedules')->findOrFail($request->budget_program_id);

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'budget_program_id' => 'required|exists:budget_programs,id',
            'account_id'        => 'nullable|exists:accounts,id',
            'description'       => 'required|string|max:255',
            'unit_price'        => 'required|numeric|min:0.01',
            'termin_ids'        => 'nullable|array',
            'termin_ids.*'      => 'exists:budget_program_schedules,id',
        ], [
            'unit_price.min' => 'Nominal harus lebih dari 0.',
        ]);

        [$selectedTerminIds, $isDefaultSelection, $effectiveQty] = $this->resolveTerminSelection($request, $program, $validated['termin_ids'] ?? null);
        if ($selectedTerminIds === null) {
            return back()->withInput()->withErrors(['termin_ids' => 'Pilih minimal satu termin untuk rincian ini.']);
        }

        // unit_price adalah nominal per termin -- total baris ini = unit_price × jumlah termin efektif
        $newItemTotal = (float) $validated['unit_price'] * $effectiveQty;
        $currentTotal = $this->allocationUsedTotal($program);
        $paguAmount   = $program->budgetAllocation->amount;

        if ($paguAmount > 0 && ($currentTotal + $newItemTotal) > $paguAmount) {
            $sisa = number_format($paguAmount - $currentTotal, 0, ',', '.');
            return back()->withInput()->withErrors([
                'unit_price' => "Nominal melebihi sisa pagu alokasi (dipakai bersama program lain di departemen ini). Sisa: Rp {$sisa}",
            ]);
        }

        $payload = [
            'budget_program_id' => $validated['budget_program_id'],
            'account_id'        => $validated['account_id'] ?? null,
            'description'       => $validated['description'],
            'unit_price'        => $validated['unit_price'],
        ];

        if (!$program->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $program, $request->user(), 'add_detail', null,
                $isDefaultSelection ? $payload : array_merge($payload, ['_termin_ids' => $selectedTerminIds]),
                'Tambah rincian: ' . $validated['description']
            );

            return redirect()->route('budget-programs.show', $program)
                ->with('success', 'Periode perencanaan sudah lewat. Rincian baru menunggu approval Keuangan.');
        }

        $oldNominalPerTermin = $program->nominal_per_termin;
        $detail = BudgetProgramDetail::create(array_merge($payload, ['quantity' => $effectiveQty]));
        if (!$isDefaultSelection) {
            $detail->termins()->sync($selectedTerminIds);
        }
        $program->syncScheduleAmountsAfterTotalChange($oldNominalPerTermin);

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil ditambahkan.');
    }

    // Tentukan termin mana yang berlaku buat rincian ini dari checklist "Berlaku di Termin"
    // (lihat form Tambah/Edit Rincian) -- null di posisi pertama berarti input tidak valid
    // (checklist ditampilkan tapi tidak ada yang dicentang sama sekali). Checklist cuma
    // ditampilkan kalau program frekuensinya > 1, jadi kalau "termin_ids_submitted" tidak ada,
    // anggap default (berlaku di semua termin) -- sama seperti perilaku sebelum fitur ini ada.
    private function resolveTerminSelection(Request $request, BudgetProgram $program, ?array $terminIds): array
    {
        $allTerminIds = $program->schedules->pluck('id')->all();

        if (!$request->boolean('termin_ids_submitted') || empty($allTerminIds)) {
            return [$allTerminIds, true, max(1, (int) $program->frequency)];
        }

        $selected = array_values(array_intersect($terminIds ?? [], $allTerminIds));
        if (empty($selected)) {
            return [null, true, 0];
        }

        $isDefault = count($selected) === count($allTerminIds);
        return [$selected, $isDefault, count($selected)];
    }

    public function edit(BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load([
            'budgetProgram.budgetAllocation.department.organization',
            'budgetProgram.budgetAllocation.budgetPeriod',
            'budgetProgram.schedules',
            'termins',
            'account',
        ]);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $accounts = \App\Models\Account::where('account_type', 'beban')
            ->where('organization_id', $budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-program-details.edit', compact('budgetProgramDetail', 'accounts'));
    }

    public function update(Request $request, BudgetProgramDetail $budgetProgramDetail)
    {
        $budgetProgramDetail->load('budgetProgram.budgetAllocation.department', 'budgetProgram.budgetAllocation.budgetPeriod', 'budgetProgram.schedules');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'account_id'   => 'nullable|exists:accounts,id',
            'description'  => 'required|string|max:255',
            'unit_price'   => 'required|numeric|min:0.01',
            'termin_ids'   => 'nullable|array',
            'termin_ids.*' => 'exists:budget_program_schedules,id',
        ], [
            'unit_price.min' => 'Nominal harus lebih dari 0.',
        ]);

        $program = $budgetProgramDetail->budgetProgram;

        [$selectedTerminIds, $isDefaultSelection, $effectiveQty] = $this->resolveTerminSelection($request, $program, $validated['termin_ids'] ?? null);
        if ($selectedTerminIds === null) {
            return back()->withInput()->withErrors(['termin_ids' => 'Pilih minimal satu termin untuk rincian ini.']);
        }

        // unit_price adalah nominal per termin -- total baris ini = unit_price × jumlah termin efektif
        $newItemTotal = (float) $validated['unit_price'] * $effectiveQty;
        $currentTotal = $this->allocationUsedTotal($program, $budgetProgramDetail->id);
        $paguAmount   = $program->budgetAllocation->amount;

        if ($paguAmount > 0 && ($currentTotal + $newItemTotal) > $paguAmount) {
            $sisa = number_format($paguAmount - $currentTotal, 0, ',', '.');
            return back()->withInput()->withErrors([
                'unit_price' => "Nominal melebihi sisa pagu alokasi (dipakai bersama program lain di departemen ini). Sisa: Rp {$sisa}",
            ]);
        }

        $payload = [
            'account_id'  => $validated['account_id'] ?? null,
            'description' => $validated['description'],
            'unit_price'  => $validated['unit_price'],
        ];

        if (!$program->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $program, $request->user(), 'update_detail', $budgetProgramDetail->id,
                $isDefaultSelection ? $payload : array_merge($payload, ['_termin_ids' => $selectedTerminIds]),
                'Ubah rincian: ' . $budgetProgramDetail->description . ' → ' . $validated['description']
            );

            return redirect()->route('budget-programs.show', $program)
                ->with('success', 'Periode perencanaan sudah lewat. Perubahan menunggu approval Keuangan.');
        }

        $oldNominalPerTermin = $program->nominal_per_termin;
        $budgetProgramDetail->update(array_merge($payload, ['quantity' => $effectiveQty]));
        $budgetProgramDetail->termins()->sync($isDefaultSelection ? [] : $selectedTerminIds);
        $program->syncScheduleAmountsAfterTotalChange($oldNominalPerTermin);

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil diperbarui.');
    }

    public function destroy(Request $request, BudgetProgramDetail $budgetProgramDetail)
    {
        $program = $budgetProgramDetail->budgetProgram()->with('budgetAllocation.department', 'budgetAllocation.budgetPeriod')->first();

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        if (!$program->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $program, $request->user(), 'delete_detail', $budgetProgramDetail->id, [],
                'Hapus rincian: ' . $budgetProgramDetail->description
            );

            return redirect()->route('budget-programs.show', $program)
                ->with('success', 'Periode perencanaan sudah lewat. Penghapusan menunggu approval Keuangan.');
        }

        $oldNominalPerTermin = $program->nominal_per_termin;
        $budgetProgramDetail->delete();
        $program->syncScheduleAmountsAfterTotalChange($oldNominalPerTermin);

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Rincian berhasil dihapus.');
    }

    // Satu pagu alokasi dipakai bersama oleh semua program di departemen yang sama,
    // jadi cek "sisa pagu" harus menjumlahkan total SEMUA program di alokasi itu --
    // bukan cuma program yang sedang diedit -- supaya tidak bisa melebihi pagu
    // gabungan meski masing-masing program terlihat "masih ada sisa" sendiri-sendiri.
    private function allocationUsedTotal(BudgetProgram $program, ?string $excludeDetailId = null): float
    {
        return BudgetProgram::where('budget_allocation_id', $program->budget_allocation_id)
            ->with('details')
            ->get()
            ->sum(function ($p) use ($excludeDetailId) {
                return $excludeDetailId
                    ? (float) $p->details->where('id', '!=', $excludeDetailId)->sum('total_amount')
                    : (float) $p->total_amount;
            });
    }
}
