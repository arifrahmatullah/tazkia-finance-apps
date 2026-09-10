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
        $program = BudgetProgram::with('budgetAllocation.department', 'budgetAllocation.budgetPeriod')->findOrFail($request->budget_program_id);

        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'budget_program_id' => 'required|exists:budget_programs,id',
            'account_id'        => 'nullable|exists:accounts,id',
            'description'       => 'required|string|max:255',
            'unit_price'        => 'required|numeric|min:0.01',
        ], [
            'unit_price.min' => 'Nominal harus lebih dari 0.',
        ]);

        // unit_price adalah nominal per termin -- total baris ini = unit_price × frekuensi program
        $newItemTotal = (float) $validated['unit_price'] * $program->frequency;
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
                $program, $request->user(), 'add_detail', null, $payload,
                'Tambah rincian: ' . $validated['description']
            );

            return redirect()->route('budget-programs.show', $program)
                ->with('success', 'Periode perencanaan sudah lewat. Rincian baru menunggu approval Keuangan.');
        }

        BudgetProgramDetail::create(array_merge($payload, ['quantity' => $program->frequency]));

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
        $budgetProgramDetail->load('budgetProgram.budgetAllocation.department', 'budgetProgram.budgetAllocation.budgetPeriod');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgramDetail->budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'account_id'  => 'nullable|exists:accounts,id',
            'description' => 'required|string|max:255',
            'unit_price'  => 'required|numeric|min:0.01',
        ], [
            'unit_price.min' => 'Nominal harus lebih dari 0.',
        ]);

        $program      = $budgetProgramDetail->budgetProgram;
        // unit_price adalah nominal per termin -- total baris ini = unit_price × frekuensi program
        $newItemTotal = (float) $validated['unit_price'] * $program->frequency;
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
                $program, $request->user(), 'update_detail', $budgetProgramDetail->id, $payload,
                'Ubah rincian: ' . $budgetProgramDetail->description . ' → ' . $validated['description']
            );

            return redirect()->route('budget-programs.show', $program)
                ->with('success', 'Periode perencanaan sudah lewat. Perubahan menunggu approval Keuangan.');
        }

        $budgetProgramDetail->update(array_merge($payload, ['quantity' => $program->frequency]));

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

        $budgetProgramDetail->delete();

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
