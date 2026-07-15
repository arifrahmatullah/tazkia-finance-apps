<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\Department;
use App\Models\FundRequest;
use App\Models\FundRequestApproval;
use App\Models\FundRequestFile;
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
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('reference', 'like', $s)->orWhere('title', 'like', $s));
        }

        $fundRequests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('fund-requests.index', compact('fundRequests', 'organizations'));
    }

    public function create(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun belum terhubung dengan data karyawan.');

        $employee->load('organization', 'activePosition.position.department');
        $activePosition = $employee->activePosition?->position;

        return view('fund-requests.create', compact('employee', 'activePosition'));
    }

    public function store(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403);

        $request->validate([
            'budget_program_id'  => 'required|exists:budget_programs,id',
            'title'              => 'required|string|max:200',
            'purpose'            => 'required|string|max:1000',
            'amount'             => 'required|numeric|min:1000',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account_number'=> ['required', 'regex:/^[0-9]{1,50}$/'],
            'bank_account_name'  => 'required|string|max:150',
            'attachments'        => 'required|array|min:1',
            'attachments.*'      => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
        ], [
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'purpose.required'          => 'Tujuan / keterangan wajib diisi.',
        ]);

        $employee->load('organization', 'activePosition.position.department');
        $activePosition = $employee->activePosition?->position;
        abort_unless($activePosition, 422, 'Anda tidak memiliki jabatan aktif. Hubungi HRD.');

        $department = $activePosition->department;
        abort_unless($department, 422, 'Jabatan tidak terhubung dengan departemen.');

        $program = BudgetProgram::with('budgetAllocation')->findOrFail($request->budget_program_id);
        abort_unless($program->budgetAllocation->department_id === $department->id, 403, 'Program tidak sesuai departemen Anda.');

        $programTotal = (float) $program->total_amount;
        if ((float) $request->amount > $programTotal) {
            return back()->withInput()->withErrors([
                'amount' => 'Nominal melebihi pagu program (Rp ' . number_format($programTotal, 0, ',', '.') . ').',
            ]);
        }

        $orgId          = $employee->organization_id;
        $deptId         = $department->id;
        $budgetPeriodId = $program->budgetAllocation->budget_period_id;

        $fundRequest = DB::transaction(function () use ($request, $employee, $activePosition, $orgId, $deptId, $budgetPeriodId) {
            $reference = FundRequest::generateReference($orgId, now()->toDateString());

            return FundRequest::create([
                'organization_id'       => $orgId,
                'department_id'         => $deptId,
                'budget_period_id'      => $budgetPeriodId,
                'budget_program_id'     => $request->budget_program_id,
                'requester_id'          => $employee->id,
                'requester_position_id' => $activePosition->id,
                'reference'             => $reference,
                'title'                 => $request->title,
                'purpose'               => $request->purpose,
                'amount'                => $request->amount,
                'bank_name'             => $request->bank_name,
                'bank_account_number'   => $request->bank_account_number,
                'bank_account_name'     => $request->bank_account_name,
                'status'                => 'draft',
                'current_step'          => 0,
                'total_steps'           => 0,
            ]);
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
        abort_unless(
            $fundRequest->requester->user_id === $user->id || $user->canAccessOrganization($fundRequest->organization_id),
            403
        );

        $fundRequest->load([
            'organization', 'department', 'budgetPeriod',
            'budgetProgram.details.account', 'budgetProgram.schedules',
            'requester', 'requesterPosition',
            'approvals.approverPosition', 'approvals.approverUser',
            'attachments.uploader', 'disbursementProofs.uploader', 'disburseAccount',
        ]);

        $canApprove  = $this->currentUserCanApprove($fundRequest, $user);
        $isRequester = $fundRequest->requester->user_id === $user->id;

        return view('fund-requests.show', compact('fundRequest', 'canApprove', 'isRequester'));
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

        return view('fund-requests.edit', compact('fundRequest', 'departments', 'budgetPeriods'));
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
            'amount'             => 'required|numeric|min:1000',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account_number'=> ['required', 'regex:/^[0-9]{1,50}$/'],
            'bank_account_name'  => 'required|string|max:150',
        ], [
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'purpose.required'          => 'Tujuan / keterangan wajib diisi.',
        ]);

        $fundRequest->update([
            'department_id'      => $request->department_id,
            'budget_period_id'   => $request->budget_period_id ?: null,
            'title'              => $request->title,
            'purpose'            => $request->purpose,
            'amount'             => $request->amount,
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

        return redirect()->route('fund-requests.show', $fundRequest)
            ->with('success', 'Pengajuan berhasil disubmit dan menunggu approval.');
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

        $programs = BudgetProgram::with(['details.account', 'schedules'])
            ->where('budget_allocation_id', $allocation->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'                => $p->id,
                    'name'              => $p->name,
                    'type'              => $p->type,
                    'type_label'        => $p->type_label,
                    'total_amount'      => (float) $p->total_amount,
                    'frequency'         => $p->frequency,
                    'nominal_per_termin'=> (float) $p->nominal_per_termin,
                    'details'           => $p->details->map(fn($d) => [
                        'account'      => $d->account?->name ?? '-',
                        'description'  => $d->description,
                        'quantity'     => (float) $d->quantity,
                        'unit'         => $d->unit ?? '',
                        'unit_price'   => (float) $d->unit_price,
                        'total_amount' => (float) $d->total_amount,
                    ])->values(),
                    'schedules'         => $p->schedules->map(fn($s) => [
                        'termin'         => $s->termin,
                        'estimated_date' => $s->estimated_date?->format('d/m/Y') ?? '-',
                        'notes'          => $s->notes ?? '',
                    ])->values(),
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

        $activePosition = $employee->activePosition?->position;
        if (!$activePosition) return false;

        return $activePosition->id === $currentApproval->approver_position_id;
    }
}
