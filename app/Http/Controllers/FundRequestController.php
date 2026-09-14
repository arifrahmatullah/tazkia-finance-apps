<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\BudgetProgramSchedule;
use App\Models\Department;
use App\Models\FundRequest;
use App\Models\FundRequestApproval;
use App\Models\FundRequestFile;
use App\Models\Bank;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FundRequestController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $employee = $user->employee;

        abort_unless($employee, 403, 'Akun ini belum terhubung dengan data karyawan.');

        $orgIds = $user->organizationIds();
        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $query = FundRequest::with(['organization', 'department', 'budgetProgram', 'requester', 'requesterPosition', 'approvals.approverPosition.activeHolder', 'approvals.approverUser'])
            ->where('requester_id', $employee->id);

        if ($request->filled('organization_id')) {
            abort_unless($user->canAccessOrganization($request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'draft'               => $query->where('status', 'draft'),
                'pending'             => $query->where('status', 'pending'),
                'diproses'            => $query->where('status', 'approved')->whereNull('disbursed_at'),
                'rejected'            => $query->where('status', 'rejected'),
                'cancelled'           => $query->where('status', 'cancelled'),
                'menunggu_konfirmasi' => $query->whereNotNull('disbursed_at')->whereNull('receipt_status'),
                'sudah_cair'          => $query->whereNotNull('disbursed_at'),
                default               => null,
            };
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('reference', 'like', $s)->orWhere('title', 'like', $s));
        }

        $fundRequests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Statistik ringkas (seluruh pengajuan milik pengaju, tanpa filter)
        $statsBase = FundRequest::where('requester_id', $employee->id);
        $stats = [
            'total'        => (clone $statsBase)->count(),
            'total_amount' => (float) (clone $statsBase)->where('status', '!=', 'draft')->sum('amount'),
            'cair'         => (clone $statsBase)->whereNotNull('disbursed_at')->count(),
            'pending'      => (clone $statsBase)->where('status', 'pending')->count(),
        ];

        return view('fund-requests.index', compact('fundRequests', 'organizations', 'stats'));
    }

    public function create(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun belum terhubung dengan data karyawan.');

        if ($employee->organization?->fund_request_blocked) {
            return redirect()->route('fund-requests.index')->withErrors([
                'blocked' => 'Pengajuan dana baru untuk "' . $employee->organization->name . '" sedang ditutup sementara.'
                    . ($employee->organization->fund_request_block_reason ? ' Alasan: ' . $employee->organization->fund_request_block_reason : ''),
            ]);
        }

        $employee->load('organization', 'activePositions.position.department');
        $creatablePositions = $employee->activePositions
            ->filter(fn($ep) => $ep->position && $ep->position->department_id)
            ->unique('position.department_id')
            ->values();

        abort_if($creatablePositions->isEmpty(), 422, 'Anda tidak memiliki jabatan aktif. Hubungi HRD.');

        // Staf dengan lebih dari satu jabatan: minta pilih jabatan/departemen dulu
        if ($creatablePositions->count() > 1) {
            $selectedDeptId = $request->query('department_id');
            $selected       = $selectedDeptId
                ? $creatablePositions->first(fn($ep) => $ep->position->department_id === $selectedDeptId)
                : null;

            if (!$selected) {
                return view('fund-requests.create-select', ['positions' => $creatablePositions]);
            }

            $activePosition = $selected->position;
        } else {
            $activePosition = $creatablePositions->first()->position;
        }

        $banks = Bank::where('is_active', true)->orderBy('name')->get();

        return view('fund-requests.create', compact('employee', 'activePosition', 'banks'));
    }

    public function store(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403);

        $employee->loadMissing('organization');
        abort_if($employee->organization?->fund_request_blocked, 422, 'Pengajuan dana baru untuk organisasi ini sedang ditutup sementara.');

        $request->validate([
            'budget_program_id'  => 'required|exists:budget_programs,id',
            'title'              => 'required|string|max:200',
            'purpose'            => 'required|string|max:1000',
            'lines'              => 'required|array|min:1',
            'lines.*.budget_program_detail_id' => 'required|exists:budget_program_details,id',
            'lines.*.unit_price' => 'required|numeric|min:0.01',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account_number'=> ['required', 'regex:/^[0-9]{1,50}$/'],
            'bank_account_name'  => 'required|string|max:150',
            'attachments'        => 'required|array|min:1',
            'attachments.*'      => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ], [
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'purpose.required'          => 'Tujuan / keterangan wajib diisi.',
            'lines.required'            => 'Rincian kegiatan program kerja tidak ditemukan.',
        ]);

        $employee->load('organization', 'activePositions.position.department');

        $program = BudgetProgram::with(['budgetAllocation', 'details'])->findOrFail($request->budget_program_id);
        $departmentId = $program->budgetAllocation->department_id;

        // Departemen ditentukan dari program yang dipilih, dicocokkan ke SEMUA jabatan aktifnya
        // (bisa lebih dari satu) -- bukan cuma jabatan dengan start_date paling baru.
        $matchingPosition = $employee->activePositions
            ->first(fn($ep) => $ep->position && $ep->position->department_id === $departmentId);
        abort_unless($matchingPosition, 403, 'Program tidak sesuai departemen jabatan Anda.');

        $activePosition = $matchingPosition->position;
        $department     = $activePosition->department;

        if (!$program->hasCompleteSchedule()) {
            return back()->withInput()->withErrors(['budget_program_id' => 'Estimasi Jadwal program kerja ini belum lengkap. Lengkapi dulu semua termin di halaman Program Kerja sebelum membuat pengajuan.']);
        }

        // Termin ditentukan dari tanggal berjalan (bulan ini), bukan dipilih manual atau
        // nomor urut -- jadwal jadi acuan kapan rincian itu semestinya dicairkan.
        $schedule = $program->scheduleForMonth();
        if (!$schedule) {
            return back()->withInput()->withErrors(['budget_program_id' => 'Tidak ada termin di Estimasi Jadwal program ini untuk bulan ' . now()->locale('id')->translatedFormat('F Y') . '. Cek/koordinasikan jadwalnya dengan bagian Keuangan.']);
        }

        [$amount, $preparedLines, $lineError] = $this->prepareRequestLines($program, $schedule, $request->input('lines', []));
        if ($lineError) {
            return back()->withInput()->withErrors(['lines' => $lineError]);
        }

        // Plafon termin ini digabung lintas rincian (selain plafon per-rincian yang sudah
        // dicek di prepareRequestLines()) -- menjaga total SEMUA pengajuan untuk periode ini
        // (biarpun terpisah per-rincian, dari beberapa pengajuan berbeda) tidak melebihi
        // nominal yang sudah ditetapkan untuk termin tsb.
        $remainingTermin = $program->scheduleRemainingCapacity($schedule);
        if ($amount > $remainingTermin + 0.01) {
            return back()->withInput()->withErrors(['lines' => 'Total pengajuan (Rp ' . number_format($amount, 0, ',', '.') . ') melebihi sisa plafon termin ' . $schedule->monthLabel() . ' (Rp ' . number_format(max($remainingTermin, 0), 0, ',', '.') . ').']);
        }

        if ($error = $this->programBudgetError($program, $amount)) {
            return back()->withInput()->withErrors(['lines' => $error]);
        }

        $orgId          = $employee->organization_id;
        $deptId         = $department->id;
        $budgetPeriodId = $program->budgetAllocation->budget_period_id;

        $fundRequest = DB::transaction(function () use ($request, $employee, $activePosition, $orgId, $deptId, $budgetPeriodId, $amount, $preparedLines, $program, $schedule) {
            // Cek ulang plafon termin di dalam transaksi supaya tidak kebobolan kalau ada
            // dua pengajuan (rincian berbeda) untuk termin yang sama nyaris bersamaan.
            $remainingTermin = $program->scheduleRemainingCapacity($schedule);
            abort_if($amount > $remainingTermin + 0.01, 422, 'Sisa plafon termin ' . $schedule->monthLabel() . ' sudah berubah (dipakai pengajuan lain). Muat ulang halaman dan coba lagi.');

            $reference = FundRequest::generateReference($orgId, now()->toDateString());

            $fundRequest = FundRequest::create([
                'organization_id'       => $orgId,
                'department_id'         => $deptId,
                'budget_period_id'      => $budgetPeriodId,
                'budget_program_id'     => $request->budget_program_id,
                'budget_program_schedule_id' => $schedule->id,
                'requester_id'          => $employee->id,
                'requester_position_id' => $activePosition->id,
                'reference'             => $reference,
                'title'                 => $request->title,
                'purpose'               => $request->purpose,
                'amount'                => $amount,
                'bank_name'             => $request->bank_name,
                'bank_account_number'   => $request->bank_account_number,
                'bank_account_name'     => $request->bank_account_name,
                'status'                => 'draft',
                'current_step'          => 0,
                'total_steps'           => 0,
            ]);

            foreach ($preparedLines as $line) {
                $fundRequest->details()->create($line);
            }

            return $fundRequest;
        });

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('fund-requests/' . $fundRequest->id . '/attachments', 'public');
                $fundRequest->files()->create([
                    'uploaded_by' => $user->id,
                    'type'        => 'attachment',
                    'file_path'   => $path,
                    'file_name'   => $file->getClientOriginalName(),
                    'mime_type'   => $file->getMimeType(),
                    'file_size'   => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('fund-requests.show', $fundRequest)
            ->with('success', 'Pengajuan dana berhasil disimpan sebagai draft.');
    }

    public function show(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($this->canViewFundRequest($fundRequest, $user), 403);

        $fundRequest->load([
            'organization', 'department', 'budgetPeriod',
            'budgetProgram.details.account', 'budgetProgram.schedules',
            'schedule',
            'details.account',
            'requester', 'requesterPosition',
            'approvals.approverPosition', 'approvals.approverUser',
            'attachments.uploader', 'disbursementProofs.uploader', 'disburseAccount',
        ]);

        $canApprove  = $this->currentUserCanApprove($fundRequest, $user);
        $isRequester = $fundRequest->requester->user_id === $user->id;
        $canCancel   = $fundRequest->canBeCancelled() && $this->canViewFundRequest($fundRequest, $user);

        return view('fund-requests.show', compact('fundRequest', 'canApprove', 'isRequester', 'canCancel'));
    }

    public function edit(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDraft(), 403, 'Hanya pengajuan draft yang bisa diedit.');

        $employee = $user->employee;
        $organizations = Organization::when($user->organizationIds() !== null, fn($q) => $q->whereIn('id', $user->organizationIds()))
            ->orderBy('name')->get();

        $departments   = Department::where('organization_id', $fundRequest->organization_id)->where('is_active', true)->orderBy('name')->get();
        $budgetPeriods = BudgetPeriod::where('organization_id', $fundRequest->organization_id)->where('is_active', true)->orderByDesc('year')->get();
        $banks         = Bank::where('is_active', true)->orderBy('name')->get();

        return view('fund-requests.edit', compact('fundRequest', 'departments', 'budgetPeriods', 'banks'));
    }

    public function update(Request $request, FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDraft(), 403);

        $request->validate([
            'department_id'      => 'required|exists:departments,id',
            'budget_period_id'   => 'nullable|exists:budget_periods,id',
            'title'              => 'required|string|max:200',
            'purpose'            => 'required|string|max:1000',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account_number'=> ['required', 'regex:/^[0-9]{1,50}$/'],
            'bank_account_name'  => 'required|string|max:150',
        ], [
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'purpose.required'          => 'Tujuan / keterangan wajib diisi.',
        ]);

        // Jumlah dana tidak diedit di sini -- tetap mengikuti rincian kegiatan yang
        // sudah tersimpan saat pengajuan dibuat (lihat BudgetProgramController::store).
        $fundRequest->update([
            'department_id'      => $request->department_id,
            'budget_period_id'   => $request->budget_period_id ?: null,
            'title'              => $request->title,
            'purpose'            => $request->purpose,
            'bank_name'          => $request->bank_name,
            'bank_account_number'=> $request->bank_account_number,
            'bank_account_name'  => $request->bank_account_name,
        ]);

        return redirect()->route('fund-requests.show', $fundRequest)
            ->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function destroy(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDraft(), 403, 'Hanya draft yang bisa dihapus.');

        $fundRequest->delete();

        return redirect()->route('fund-requests.index')->with('success', 'Pengajuan berhasil dihapus.');
    }

    public function cancel(Request $request, FundRequest $fundRequest)
    {
        $user = auth()->user();

        // Sama seperti hak lihat detail: pengaju sendiri, Keuangan, superadmin, atau
        // siapa pun di rantai approval-nya (langkah manapun) -- termasuk yang approval-nya
        // sendiri sudah lewat, supaya kalau ada yang baru sadar ada salah setelah dia
        // approve (kayak kasus Dina), dia tetap bisa menghentikannya sebelum cair.
        abort_unless($this->canViewFundRequest($fundRequest, $user), 403);
        abort_unless($fundRequest->canBeCancelled(), 422, 'Pengajuan ini tidak bisa dibatalkan (sudah dicairkan, ditolak, atau sudah dibatalkan sebelumnya).');

        $request->validate([
            'notes' => 'required|string|max:500',
        ], ['notes.required' => 'Alasan pembatalan wajib diisi.']);

        $fundRequest->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->name ?? $user->email,
            'notes'        => $request->notes,
        ]);

        return redirect()->route('fund-requests.show', $fundRequest)
            ->with('success', 'Pengajuan berhasil dibatalkan.');
    }

    // Bikin draft baru terisi dari pengajuan yang sudah dibatalkan, supaya pengaju tinggal
    // koreksi yang salah lalu submit -- pengajuan lama tetap utuh sebagai riwayat "Dibatalkan",
    // tidak diedit/dihidupkan lagi. Cuma pengaju aslinya yang boleh (draft baru ini atas nama dia).
    public function resubmit(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isCancelled(), 422, 'Cuma pengajuan yang dibatalkan yang bisa diajukan ulang.');

        $fundRequest->loadMissing('details', 'budgetProgram');
        $schedule = $fundRequest->budgetProgram?->nextAvailableSchedule();

        $duplicate = DB::transaction(function () use ($fundRequest, $schedule) {
            $reference = FundRequest::generateReference($fundRequest->organization_id, now()->toDateString());

            $duplicate = FundRequest::create([
                'organization_id'            => $fundRequest->organization_id,
                'department_id'              => $fundRequest->department_id,
                'budget_period_id'           => $fundRequest->budget_period_id,
                'budget_program_id'          => $fundRequest->budget_program_id,
                'budget_program_schedule_id' => $schedule?->id,
                'requester_id'               => $fundRequest->requester_id,
                'requester_position_id'      => $fundRequest->requester_position_id,
                'reference'                  => $reference,
                'title'                      => $fundRequest->title,
                'purpose'                    => $fundRequest->purpose,
                'amount'                     => $fundRequest->amount,
                'bank_name'                  => $fundRequest->bank_name,
                'bank_account_number'        => $fundRequest->bank_account_number,
                'bank_account_name'          => $fundRequest->bank_account_name,
                'status'                     => 'draft',
                'current_step'               => 0,
                'total_steps'                => 0,
            ]);

            foreach ($fundRequest->details as $detail) {
                $duplicate->details()->create([
                    'budget_program_detail_id' => $detail->budget_program_detail_id,
                    'account_id'               => $detail->account_id,
                    'description'              => $detail->description,
                    'quantity'                 => $detail->quantity,
                    'unit'                     => $detail->unit,
                    'ceiling_unit_price'       => $detail->ceiling_unit_price,
                    'unit_price'               => $detail->unit_price,
                ]);
            }

            return $duplicate;
        });

        return redirect()->route('fund-requests.show', $duplicate)
            ->with('success', 'Draft baru dibuat dari pengajuan ' . $fundRequest->reference . ' yang dibatalkan. Periksa datanya (lampiran perlu diunggah ulang) lalu Submit.');
    }

    public function submit(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDraft(), 403, 'Pengajuan sudah disubmit.');

        $chain = ApprovalSetting::getChainFor(
            $fundRequest->organization_id,
            $fundRequest->requester_position_id,
            (float) $fundRequest->amount
        );

        if ($chain->isEmpty()) {
            return back()->withErrors(['submit' => 'Tidak ada konfigurasi approval untuk jabatan dan nominal ini. Hubungi administrator.']);
        }

        DB::transaction(function () use ($fundRequest, $chain) {
            foreach ($chain as $setting) {
                FundRequestApproval::create([
                    'fund_request_id'     => $fundRequest->id,
                    'step'                => $setting->step,
                    'approver_position_id' => $setting->approver_position_id,
                    'status'              => 'waiting',
                ]);
            }

            $fundRequest->update([
                'status'       => 'pending',
                'current_step' => $chain->first()->step,
                'total_steps'  => $chain->count(),
                'submitted_at' => now(),
            ]);
        });

        $this->notifyCurrentStepApprovers($fundRequest);

        return redirect()->route('fund-requests.show', $fundRequest)
            ->with('success', 'Pengajuan berhasil disubmit dan menunggu approval.');
    }

    private function notifyCurrentStepApprovers(FundRequest $fundRequest): void
    {
        $approval = $fundRequest->approvals()
            ->where('step', $fundRequest->current_step)
            ->where('status', 'waiting')
            ->first();

        if (!$approval) {
            return;
        }

        foreach ($approval->approverUsers() as $user) {
            $user->notify(new \App\Notifications\FundRequestNeedsApproval($approval));
        }
    }

    public function getDependencies(Request $request)
    {
        $user  = auth()->user();
        $orgId = $request->organization_id;
        abort_unless($orgId && $user->canAccessOrganization($orgId), 403);

        return response()->json([
            'departments'    => Department::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'budget_periods' => BudgetPeriod::where('organization_id', $orgId)->where('is_active', true)->orderByDesc('year')->get(['id', 'name']),
        ]);
    }

    public function getPrograms(Request $request)
    {
        $user   = auth()->user();
        $orgId  = $request->organization_id;
        $deptId = $request->department_id;

        abort_unless($orgId && $deptId && $user->canAccessOrganization($orgId), 403);

        $allocation = BudgetAllocation::where('department_id', $deptId)
            ->where('is_active', true)
            ->whereHas('budgetPeriod', fn($q) => $q->where('is_active', true))
            ->first();

        if (!$allocation) {
            return response()->json(['programs' => [], 'allocation' => null]);
        }

        $programs = BudgetProgram::with(['details.account', 'schedules.fundRequests'])
            ->where('budget_allocation_id', $allocation->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                // Termin bulan berjalan -- satu-satunya termin yang relevan untuk pengajuan
                // baru saat ini (null kalau tidak ada termin yang tanggalnya cocok).
                $currentSchedule = $p->hasCompleteSchedule() ? $p->scheduleForMonth() : null;

                return [
                    'id'                => $p->id,
                    'name'              => $p->name,
                    'type'              => $p->type,
                    'type_label'        => $p->type_label,
                    'total_amount'      => (float) $p->total_amount,
                    'frequency'         => $p->frequency,
                    'nominal_per_termin'=> (float) $p->nominal_per_termin,
                    'has_complete_schedule' => $p->hasCompleteSchedule(),
                    'current_termin'    => $currentSchedule ? [
                        'termin'         => $currentSchedule->termin,
                        'estimated_date' => $currentSchedule->estimated_date?->format('d/m/Y'),
                        'month_label'    => $currentSchedule->monthLabel(),
                        'ceiling'        => (float) ($currentSchedule->amount ?? $p->nominal_per_termin),
                        'remaining'      => (float) $p->scheduleRemainingCapacity($currentSchedule),
                    ] : null,
                    'details'           => $p->details->map(function ($d) use ($p, $currentSchedule) {
                        return [
                            'id'                  => $d->id,
                            'account'             => $d->account?->name ?? '-',
                            'description'         => $d->description,
                            'unit'                => $d->unit ?? '',
                            'unit_price'          => (float) $d->unit_price,
                            'remaining_in_termin' => $currentSchedule ? (float) $p->detailRemainingCapacity($d, $currentSchedule) : null,
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'programs'   => $programs,
            'allocation' => [
                'id'     => $allocation->id,
                'amount' => (float) $allocation->amount,
            ],
        ]);
    }

    public function uploadFile(Request $request, FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ]);

        $file = $request->file('file');
        $path = $file->store('fund-requests/' . $fundRequest->id . '/attachments', 'public');

        $fundRequest->files()->create([
            'uploaded_by' => $user->id,
            'type'        => 'attachment',
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
        ]);

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    public function deleteFile(FundRequestFile $fundRequestFile)
    {
        $user = auth()->user();
        $fr   = $fundRequestFile->fundRequest;

        if ($fundRequestFile->type === 'attachment') {
            abort_unless($fr->requester->user_id === $user->id, 403);
        } else {
            abort_unless($user->hasPermission('menu.pencairan-dana'), 403);
        }

        Storage::disk('public')->delete($fundRequestFile->file_path);
        $fundRequestFile->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }

    public function confirmReceipt(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDisbursed(), 422, 'Pengajuan belum dicairkan.');
        abort_unless(is_null($fundRequest->receipt_status), 422, 'Status penerimaan sudah dikonfirmasi.');

        $fundRequest->update([
            'receipt_status'       => 'confirmed',
            'receipt_confirmed_at' => now(),
            'auto_confirmed'       => false,
        ]);

        return back()->with('success', 'Dana berhasil dikonfirmasi diterima. Terima kasih!');
    }

    public function disputeReceipt(Request $request, FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless($fundRequest->requester->user_id === $user->id, 403);
        abort_unless($fundRequest->isDisbursed(), 422, 'Pengajuan belum dicairkan.');
        abort_unless(is_null($fundRequest->receipt_status), 422, 'Status penerimaan sudah dikonfirmasi.');

        $request->validate([
            'receipt_notes' => 'required|string|max:500',
        ]);

        $fundRequest->update([
            'receipt_status'       => 'disputed',
            'receipt_confirmed_at' => now(),
            'receipt_notes'        => $request->receipt_notes,
            'auto_confirmed'       => false,
        ]);

        return back()->with('success', 'Kendala berhasil dilaporkan. Tim keuangan akan menindaklanjuti.');
    }

    /**
     * Menghitung sisa pagu program (total_amount dikurangi pengajuan lain yang masih berlaku)
     * dan mengembalikan pesan error jika nominal baru melebihi sisa tersebut, atau null jika aman.
     */
    // Cocokkan lines yang dikirim user dengan rincian program kerja -- BOLEH cuma sebagian
    // rincian (tidak wajib semua), tapi tiap baris yang dipilih tetap divalidasi ke program
    // (bukan input bebas): harga satuan tidak boleh melebihi plafon rincian itu sendiri, DAN
    // tidak boleh melebihi sisa plafon rincian itu KHUSUS di termin ($schedule) yang sedang
    // diajukan (rincian yang sama bisa saja sudah sebagian dipakai pengajuan lain di termin
    // yang sama sebelumnya).
    private function prepareRequestLines(BudgetProgram $program, BudgetProgramSchedule $schedule, array $lines): array
    {
        $submitted   = collect($lines)->keyBy('budget_program_detail_id');
        $detailsById = $program->details->keyBy('id');

        if ($submitted->isEmpty()) {
            return [0, [], 'Pilih minimal satu rincian kegiatan untuk diajukan.'];
        }

        $amount   = 0;
        $prepared = [];

        foreach ($submitted as $detailId => $line) {
            $detail = $detailsById->get($detailId);
            if (!$detail) {
                return [0, [], 'Rincian pengajuan tidak valid. Muat ulang halaman dan coba lagi.'];
            }

            $unitPrice = (float) $line['unit_price'];
            if ($unitPrice > (float) $detail->unit_price) {
                return [0, [], "Harga satuan \"{$detail->description}\" tidak boleh melebihi Rp " . number_format($detail->unit_price, 0, ',', '.') . '.'];
            }

            $remainingForDetail = $program->detailRemainingCapacity($detail, $schedule);
            if ($unitPrice > $remainingForDetail + 0.01) {
                return [0, [], "Rincian \"{$detail->description}\" sudah terpakai sebagian/semua di termin {$schedule->monthLabel()}. Sisa yang bisa diajukan: Rp " . number_format(max($remainingForDetail, 0), 0, ',', '.') . '.'];
            }

            // unit_price di program adalah nominal per termin -- pengajuan dana selalu untuk
            // 1 termin pencairan, jadi total baris = harga satuan itu sendiri (quantity = 1),
            // bukan dikalikan frekuensi/quantity program (yang merepresentasikan total seluruh periode).
            $amount    += round($unitPrice, 2);
            $prepared[] = [
                'budget_program_detail_id' => $detail->id,
                'account_id'               => $detail->account_id,
                'description'              => $detail->description,
                'quantity'                 => 1,
                'unit'                     => $detail->unit,
                'ceiling_unit_price'       => $detail->unit_price,
                'unit_price'               => $unitPrice,
            ];
        }

        return [$amount, $prepared, null];
    }

    private function programBudgetError(BudgetProgram $program, float $newAmount, ?string $excludeFundRequestId = null): ?string
    {
        $programTotal = (float) $program->total_amount;

        $usedByOthers = (float) FundRequest::where('budget_program_id', $program->id)
            ->whereNotIn('status', FundRequest::VOID_STATUSES)
            ->when($excludeFundRequestId, fn($q) => $q->where('id', '!=', $excludeFundRequestId))
            ->sum('amount');

        $remaining = $programTotal - $usedByOthers;

        if ($newAmount > $remaining) {
            return 'Nominal melebihi sisa pagu program (Rp ' . number_format(max($remaining, 0), 0, ',', '.')
                . ' dari total pagu Rp ' . number_format($programTotal, 0, ',', '.') . ').';
        }

        return null;
    }

    // Siapa saja yang boleh membuka detail pengajuan: pengaju sendiri, tim Keuangan,
    // superadmin, atau siapapun yang ada di alur approval-nya (langkah manapun -- bukan
    // cuma langkah yang lagi berjalan, supaya approver langkah sebelumnya tetap bisa lihat
    // riwayatnya). Sebelumnya cek ini hanya "satu organisasi" (canAccessOrganization), yang
    // berarti SEMUA staf di organisasi yang sama bisa buka pengajuan siapapun -- kebocoran privasi.
    private function canViewFundRequest(FundRequest $fundRequest, $user): bool
    {
        if ($user->isSuperAdmin()) return true;
        if ($fundRequest->requester->user_id === $user->id) return true;
        if ($user->hasPermission('menu.pencairan-dana')) return true;

        $employee = $user->employee;
        if (!$employee) return false;

        $positionIds = $employee->activePositions()->pluck('position_id');
        return $fundRequest->approvals()->whereIn('approver_position_id', $positionIds)->exists();
    }

    private function currentUserCanApprove(FundRequest $fundRequest, $user): bool
    {
        if (!$fundRequest->isPending()) return false;

        $currentApproval = $fundRequest->approvals
            ->where('step', $fundRequest->current_step)
            ->where('status', 'waiting')
            ->first();

        if (!$currentApproval) return false;

        $employee = $user->employee;
        if (!$employee) return false;

        // Cek ke semua jabatan aktifnya (bisa lebih dari satu), bukan cuma yang paling baru mulai
        return $employee->activePositions()
            ->where('position_id', $currentApproval->approver_position_id)
            ->exists();
    }
}
