<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\Department;
use App\Services\BudgetProgramChangeService;
use Illuminate\Http\Request;

class BudgetProgramController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        // Staf hanya melihat program dari departemen jabatan-jabatan aktifnya sendiri
        // (bisa lebih dari satu jabatan/departemen sekaligus);
        // superadmin & keuangan (pencairan dana) melihat semua departemen di organisasinya
        $isRestricted    = !$user->isSuperAdmin() && !$user->hasPermission('menu.pencairan-dana');
        $restrictDeptIds = [];
        $activeEmployee  = null;
        if ($isRestricted) {
            $activeEmployee  = $user->employee()->with('activePositions.position.department')->first();
            $restrictDeptIds = $activeEmployee?->activeDepartmentIds() ?? [];
        }

        // Departemen/jabatan yang sedang ditampilkan: kunjungan pertama (belum pernah pilih filter)
        // staf otomatis diarahkan ke jabatan pertamanya saja (bukan gabungan semua jabatan sekaligus);
        // pilih "Semua Jabatan"/"Semua Departemen" (department_id=all atau kosong) untuk lihat gabungan.
        $rawDeptId = $request->query('department_id');
        $selectedDeptId = match (true) {
            $rawDeptId === null && $isRestricted => $restrictDeptIds[0] ?? null,
            $rawDeptId === null, $rawDeptId === '', $rawDeptId === 'all' => null,
            default => $rawDeptId,
        };

        // Periode anggaran wajib satu — tidak ada pilihan "Semua Periode", default ke periode aktif
        $budgetPeriods    = BudgetPeriod::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->where('is_active', true)->orderBy('name')->get();
        $selectedPeriodId = $request->query('budget_period_id') ?: $budgetPeriods->first()?->id;

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

        if ($isRestricted) {
            if (!empty($restrictDeptIds)) {
                $query->whereHas('budgetAllocation', fn($a) => $a->whereIn('department_id', $restrictDeptIds));
            } else {
                // Tidak punya jabatan aktif → tidak ada program yang bisa dilihat
                $query->whereRaw('1 = 0');
            }
        }

        if ($selectedPeriodId) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('budget_period_id', $selectedPeriodId));
        }

        if ($selectedDeptId) {
            $query->whereHas('budgetAllocation', fn($a) => $a->where('department_id', $selectedDeptId));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $programs = $query->orderBy('name')->paginate(15)->withQueryString();

        $jabatanRows = collect();
        if ($isRestricted) {
            // Staf: bukan dropdown departemen, tapi tabel jabatan aktifnya sendiri —
            // tiap baris bisa langsung difilter ("Lihat") atau dipakai bikin program ("+ Buat Program")
            $filterLabel = 'Jabatan';
            $departments = collect();

            $deptIdsWithAllocation = BudgetAllocation::where('budget_period_id', $selectedPeriodId)
                ->whereIn('department_id', $restrictDeptIds)
                ->where('is_active', true)
                ->pluck('department_id');

            $jabatanRows = ($activeEmployee?->activePositions ?? collect())
                ->filter(fn($ep) => $ep->position && $ep->position->department_id)
                ->unique('position.department_id')
                ->map(fn($ep) => (object) [
                    'id'             => $ep->position->department_id,
                    'jabatan'        => $ep->position->name,
                    'departemen'     => $ep->position->department->name ?? '-',
                    'can_create'     => (bool) $ep->position->can_create_program,
                    'has_allocation' => $deptIdsWithAllocation->contains($ep->position->department_id),
                    'is_selected'    => $selectedDeptId === $ep->position->department_id,
                ])
                ->values();
        } else {
            $filterLabel = 'Departemen';
            $departments = Department::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
                ->where('is_active', true)->where('has_budget', true)->orderBy('name')->get();
        }

        // Ringkasan pagu untuk departemen yang sedang dilihat — tampil meski belum ada program
        // sama sekali (bukan cuma berdasarkan program yang kebetulan sudah dibuat).
        $allocationQuery = BudgetAllocation::with(['department', 'budgetPeriod'])
            ->whereHas('department', function ($q) use ($orgIds) {
                if ($orgIds !== null) {
                    $q->whereIn('organization_id', $orgIds);
                }
                $q->where('is_active', true);
            })
            ->where('is_active', true);

        if ($isRestricted) {
            if (!empty($restrictDeptIds)) {
                $allocationQuery->whereIn('department_id', $restrictDeptIds);
            } else {
                $allocationQuery->whereRaw('1 = 0');
            }
        }

        if ($selectedPeriodId) {
            $allocationQuery->where('budget_period_id', $selectedPeriodId);
        }

        if ($selectedDeptId) {
            $allocationQuery->where('department_id', $selectedDeptId);
        }

        $allocationSummaries = $allocationQuery->get()
            ->map(function ($alloc) {
                $terpakai = BudgetProgram::with('details')
                    ->where('budget_allocation_id', $alloc->id)->get()
                    ->sum(fn($p) => (float) $p->total_amount);
                return [
                    'dept'     => $alloc->department->name,
                    'periode'  => $alloc->budgetPeriod->name,
                    'pagu'     => (float) $alloc->amount,
                    'terpakai' => $terpakai,
                    'sisa'     => (float) $alloc->amount - $terpakai,
                ];
            });

        // Bisa bikin program jika salah satu jabatan aktifnya (bisa lebih dari satu) berhak membuat program
        $creatablePositions = ($activeEmployee ?? $user->employee()->with('activePositions.position')->first())
            ?->activePositions
            ->filter(fn($ep) => $ep->position?->can_create_program)
            ?? collect();
        $canCreate = $user->isSuperAdmin() || $creatablePositions->isNotEmpty();

        // Peringatan pagu belum tersedia — khusus untuk departemen/jabatan yang sedang ditampilkan
        // (bukan gabungan semua jabatan), supaya staf tahu harus hubungi Keuangan untuk departemen itu.
        $hasAllocation = true;
        $selectedDeptName = null;
        if ($selectedDeptId) {
            $hasAllocation    = BudgetAllocation::where('budget_period_id', $selectedPeriodId)
                ->where('department_id', $selectedDeptId)
                ->where('is_active', true)
                ->exists();
            if (!$hasAllocation) {
                $selectedDeptName = Department::find($selectedDeptId)?->name;
            }
        }

        return view('budget-programs.index', compact('programs', 'budgetPeriods', 'departments', 'filterLabel', 'allocationSummaries', 'canCreate', 'hasAllocation', 'selectedDeptId', 'selectedDeptName', 'jabatanRows', 'selectedPeriodId'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $activePosition = $user->employee()->with('activePosition.position.department')->first()?->activePosition?->position;
            $department     = $activePosition?->department;
        } else {
            $employee  = $user->employee()->with('activePositions.position.department')->first();
            $creatable = ($employee?->activePositions ?? collect())
                ->filter(fn($ep) => $ep->position && $ep->position->can_create_program)
                ->unique('position.department_id')
                ->values();

            abort_if($creatable->isEmpty(), 403, 'Jabatan aktifmu belum diatur atau tidak memiliki akses untuk membuat program kerja. Hubungi admin.');

            // Staf dengan lebih dari satu jabatan yang berhak: minta pilih jabatan/departemen dulu
            if ($creatable->count() > 1) {
                $selectedDeptId = $request->query('department_id');
                $selected       = $selectedDeptId
                    ? $creatable->first(fn($ep) => $ep->position->department_id === $selectedDeptId)
                    : null;

                if (!$selected) {
                    return view('budget-programs.create-select', ['positions' => $creatable]);
                }

                $activePosition = $selected->position;
            } else {
                $activePosition = $creatable->first()->position;
            }

            $department = $activePosition->department;
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
            ->where('organization_id', $department->organization_id)
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
        $this->assertDepartmentAccess($allocation->department_id, 'Anda hanya dapat membuat program kerja untuk departemen Anda sendiri.');

        $validated = $request->validate([
            'budget_allocation_id' => 'required|exists:budget_allocations,id',
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:pengadaan,kegiatan,pembayaran',
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

        if ($lines->isEmpty()) {
            return back()->withInput()->withErrors([
                'lines' => 'Isi minimal satu baris rincian dengan deskripsi dan nominal.',
            ]);
        }

        if ($lines->contains(fn($l) => (float) ($l['nominal'] ?? 0) <= 0)) {
            return back()->withInput()->withErrors([
                'lines' => 'Nominal setiap baris rincian harus lebih dari 0.',
            ]);
        }

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
                'type'                 => $validated['type'],
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
            ->withFragment('jadwal')
            ->with('success', 'Program kerja berhasil ditambahkan. Silakan isi estimasi jadwal pengeluaran.');
    }

    public function show(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod', 'account', 'details.account', 'schedules']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );
        $this->assertDepartmentAccess($budgetProgram->budgetAllocation->department_id);

        $accounts = Account::where('account_type', 'beban')
            ->where('organization_id', $budgetProgram->budgetAllocation->department->organization_id)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        $pendingChangeRequests = $budgetProgram->changeRequests()->where('status', 'pending')->orderByDesc('created_at')->get();

        // Sisa pagu ALOKASI (departemen), bukan sisa program ini saja -- satu alokasi
        // dipakai bersama oleh banyak program, jadi harus dikurangi total SEMUA program
        // di alokasi yang sama supaya konsisten dengan ringkasan di daftar Program Kerja.
        $terpakaiAlokasi = BudgetProgram::with('details')
            ->where('budget_allocation_id', $budgetProgram->budget_allocation_id)
            ->get()
            ->sum(fn($p) => (float) $p->total_amount);
        $sisaAlokasi = (float) $budgetProgram->budgetAllocation->amount - $terpakaiAlokasi;

        return view('budget-programs.show', compact('budgetProgram', 'accounts', 'pendingChangeRequests', 'terpakaiAlokasi', 'sisaAlokasi'));
    }

    public function edit(BudgetProgram $budgetProgram)
    {
        $budgetProgram->load(['budgetAllocation.department.organization', 'budgetAllocation.budgetPeriod']);

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );
        $this->assertDepartmentAccess($budgetProgram->budgetAllocation->department_id);

        return view('budget-programs.edit', compact('budgetProgram'));
    }

    public function update(Request $request, BudgetProgram $budgetProgram)
    {
        $budgetProgram->load('budgetAllocation.department', 'budgetAllocation.budgetPeriod');

        abort_unless(
            auth()->user()->canAccessOrganization($budgetProgram->budgetAllocation->department->organization_id),
            403
        );
        $this->assertDepartmentAccess($budgetProgram->budgetAllocation->department_id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:pengadaan,kegiatan,pembayaran',
            'notes'     => 'nullable|string|max:1000',
            'frequency' => 'required|integer|min:1|max:366',
            'is_active' => 'boolean',
        ]);

        $payload = [
            'name'      => $validated['name'],
            'type'      => $validated['type'],
            'notes'     => $validated['notes'] ?? null,
            'frequency' => (int) $validated['frequency'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!$budgetProgram->isWithinPlanningWindow()) {
            app(BudgetProgramChangeService::class)->requestChange(
                $budgetProgram, $request->user(), 'update_info', null, $payload,
                'Ubah info program: ' . $budgetProgram->name
            );

            return redirect()->route('budget-programs.index')
                ->with('success', 'Periode perencanaan sudah lewat. Perubahan disimpan sebagai permintaan dan menunggu approval Keuangan.');
        }

        \DB::transaction(function () use ($budgetProgram, $payload) {
            $budgetProgram->update($payload);

            $budgetProgram->load('details');
            foreach ($budgetProgram->details as $detail) {
                $detail->update(['quantity' => $payload['frequency']]);
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
        $this->assertDepartmentAccess($budgetProgram->budgetAllocation->department_id);

        $budgetProgram->delete();

        return redirect()
            ->route('budget-programs.index')
            ->with('success', 'Program kerja berhasil dihapus.');
    }

    // Staf hanya boleh mengakses/membuat program untuk departemennya sendiri;
    // superadmin & keuangan (pencairan dana) bebas dalam organisasinya
    private function assertDepartmentAccess(string $departmentId, string $errorMessage = 'Anda hanya dapat mengakses program kerja departemen Anda sendiri.'): void
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->hasPermission('menu.pencairan-dana')) {
            return;
        }

        $userDeptIds = $user->employee()
            ->with('activePositions.position')->first()
            ?->activeDepartmentIds() ?? [];

        abort_unless(in_array($departmentId, $userDeptIds), 403, $errorMessage);
    }
}
