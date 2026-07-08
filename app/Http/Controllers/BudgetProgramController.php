<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\Department;
use Illuminate\Http\Request;

class BudgetProgramController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $query = BudgetProgram::with([
            'budgetAllocation.department',
            'budgetAllocation.budgetPeriod',
            'account',
            'details.account',
            'schedules',
        ])->whereHas('budgetAllocation.department', function ($q) use ($orgIds) {
            if ($orgIds !== null) {
                $q->whereIn('organization_id', $orgIds);
            }
            $q->where('is_active', true);
        });

        if ($request->filled('budget_period_id')) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('budget_period_id', $request->budget_period_id));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('department_id', $request->department_id));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();

        $budgetPeriods = BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->orderBy('name')->get();

        $departments = Department::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->where('has_budget', true)->orderBy('name')->get();

        // Ringkasan per alokasi dari program yang tampil (untuk kartu di luar tabel)
        $allocationIds = $programs->pluck('budget_allocation_id')->unique();
        $allocationSummaries = \App\Models\BudgetAllocation::with(['department', 'budgetPeriod'])
            ->whereIn('id', $allocationIds)
            ->get()
            ->map(function ($alloc) {
                $programs = \App\Models\BudgetProgram::with('details')
                    ->where('budget_allocation_id', $alloc->id)->get();
                $terpakai = $programs->sum(fn($p) => (float) $p->total_amount);
                return [
                    'dept'     => $alloc->department->name,
                    'periode'  => $alloc->budgetPeriod->name,
                    'pagu'     => (float) $alloc->amount,
                    'terpakai' => $terpakai,
                    'sisa'     => (float) $alloc->amount - $terpakai,
                ];
            });

        $activePosition = $user->employee()->with('activePosition.position.department')->first()?->activePosition?->position;
        $canCreate      = $user->isSuperAdmin() || ($activePosition?->can_create_program ?? false);

        $hasAllocation = true;
        if ($canCreate && !$user->isSuperAdmin()) {
            $hasAllocation = BudgetAllocation::whereHas('budgetPeriod', fn($q) => $q->where('is_active', true))
                ->where('department_id', $activePosition?->department?->id)
                ->where('is_active', true)
                ->exists();
        }

        return view('budget-programs.index', compact('programs', 'budgetPeriods', 'departments', 'allocationSummaries', 'canCreate', 'hasAllocation'));
    }

    public function create()
    {
        $user     = auth()->user();
        $employee = $user->employee()->with('activePosition.position.department')->first();

        $activePosition = $employee?->activePosition?->position;
        $department     = $activePosition?->department;

        if (!$user->isSuperAdmin()) {
            abort_if(!$activePosition, 403, 'Jabatan aktifmu belum diatur. Hubungi admin.');
            abort_if(!$activePosition->can_create_program, 403, 'Jabatan kamu (' . $activePosition->name . ') tidak memiliki akses untuk membuat program kerja. Hubungi admin.');
        }

        $allocation = BudgetAllocation::with(['department', 'budgetPeriod'])
            ->whereHas('budgetPeriod', fn($q) => $q->where('is_active', true))
            ->where('department_id', $department->id)
            ->where('is_active', true)
            ->first();

        abort_if(!$allocation, 403, 'Pagu anggaran untuk departemen ' . $department->name . ' belum tersedia. Hubungi bagian Keuangan.');

        // Hitung sisa pagu setelah dikurangi program yang sudah ada
        $usedByPrograms = BudgetProgram::with('details')
            ->where('budget_allocation_id', $allocation->id)
            ->get()
            ->sum(fn($p) => (float) $p->total_amount);
        $sisaAlokasi = (float) $allocation->amount - $usedByPrograms;

        $accounts = Account::where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.create', compact('allocation', 'accounts', 'department', 'sisaAlokasi'));
    }

    public function store(Request $request)
    {
        $allocation = BudgetAllocation::with('department')->findOrFail($request->budget_allocation_id);

        abort_unless(
            auth()->user()->canAccessOrganization($allocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'budget_allocation_id' => 'required|exists:budget_allocations,id',
            'name'                 => 'required|string|max:255',
            'notes'                => 'nullable|string|max:1000',
            'frequency'            => 'required|integer|min:1|max:366',
            'lines'                => 'nullable|array',
            'lines.*.description'  => 'nullable|string|max:255',
            'lines.*.account_id'   => 'nullable|exists:accounts,id',
            'lines.*.nominal'      => 'nullable|numeric|min:0',
        ]);

        $frequency = (int) ($validated['frequency'] ?? 1);

        $lines = collect($validated['lines'] ?? [])
            ->filter(fn($l) => !empty($l['description']));

        // grandTotal = sum(nominal_per_termin × frekuensi)
        $grandTotal   = $lines->sum(fn($l) => (float) ($l['nominal'] ?? 0)) * $frequency;
        $pagu         = (float) $allocation->amount;
        $usedByOthers = BudgetProgram::with('details')
            ->where('budget_allocation_id', $allocation->id)
            ->get()
            ->sum(fn($p) => (float) $p->total_amount);
        $sisaAlokasi = $pagu - $usedByOthers;

        if ($pagu > 0 && $grandTotal > $sisaAlokasi) {
            return back()->withInput()->withErrors([
                'lines' => "Total rincian (Rp " . number_format($grandTotal, 0, ',', '.') . ") melebihi sisa pagu (Rp " . number_format($sisaAlokasi, 0, ',', '.') . ").",
            ]);
        }

        $program = \DB::transaction(function () use ($validated, $frequency, $lines) {
            $program = BudgetProgram::create([
                'budget_allocation_id' => $validated['budget_allocation_id'],
                'name'                 => $validated['name'],
                'notes'                => $validated['notes'] ?? null,
                'frequency'            => $frequency,
                'is_active'            => true,
            ]);

            foreach ($lines as $line) {
                $nominal = (float) ($line['nominal'] ?? 0);
                $program->details()->create([
                    'account_id'  => $line['account_id'] ?? null,
                    'description' => $line['description'],
                    'quantity'    => $frequency,
                    'unit_price'  => $nominal,
                ]);
            }

            $program->regenerateSchedules();

            return $program;
        });

        return redirect()
            ->route('budget-programs.show', $program)
            ->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function show(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod', 'account', 'details.account', 'schedules']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $accounts = Account::where('account_type', 'beban')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('budget-programs.show', compact('budgetProgram', 'accounts'));
    }

    public function edit(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        return view('budget-programs.edit', compact('budgetProgram'));
    }

    public function update(Request $request, BudgetProgram $budgetProgram)
    {
        $budgetProgram->load('budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'notes'     => 'nullable|string|max:1000',
            'frequency' => 'required|integer|min:1|max:366',
            'is_active' => 'boolean',
        ]);

        $frequency = (int) $validated['frequency'];

        \DB::transaction(function () use ($budgetProgram, $validated, $frequency, $request) {
            $budgetProgram->update([
                'name'      => $validated['name'],
                'notes'     => $validated['notes'] ?? null,
                'frequency' => $frequency,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $budgetProgram->load('details');
            foreach ($budgetProgram->details as $detail) {
                $detail->update(['quantity' => $frequency]);
            }

            $budgetProgram->regenerateSchedules();
        });

        return redirect()
            ->route('budget-programs.index')
            ->with('success', 'Program kerja berhasil diperbarui.');
    }

    public function destroy(BudgetProgram $budgetProgram)
    {
        $allocationId = $budgetProgram->budget_allocation_id;
        $budgetProgram->load('budgetAllocation.department');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );

        $budgetProgram->delete();

        return redirect()
            ->route('budget-programs.index')
            ->with('success', 'Program kerja berhasil dihapus.');
    }
}
