<?php

namespace App\Http\Controllers;

use App\Models\ApprovalSetting;
use App\Models\BudgetPeriod;
use App\Models\Department;
use App\Models\FundRequest;
use App\Models\FundRequestApproval;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $query = FundRequest::with(['organization', 'department', 'requester', 'requesterPosition', 'approvals'])
            ->where('requester_id', $employee->id);

        if ($request->filled('organization_id')) {
            abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('reference', 'like', $s)->orWhere('title', 'like', $s));
        }

        $fundRequests = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('fund-requests.index', compact('fundRequests', 'organizations'));
    }

    public function create(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun belum terhubung dengan data karyawan.');

        $orgIds = $user->organizationIds();
        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $selectedOrgId = $request->integer('organization_id') ?: $organizations->first()?->id;

        $departments   = $selectedOrgId ? Department::where('organization_id', $selectedOrgId)->where('is_active', true)->orderBy('name')->get() : collect();
        $budgetPeriods = $selectedOrgId ? BudgetPeriod::where('organization_id', $selectedOrgId)->where('is_active', true)->orderByDesc('year')->get() : collect();

        $activePosition = $employee->activePosition?->position;

        return view('fund-requests.create', compact(
            'organizations', 'departments', 'budgetPeriods',
            'selectedOrgId', 'employee', 'activePosition'
        ));
    }

    public function store(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403);

        $request->validate([
            'organization_id'  => 'required|integer|exists:organizations,id',
            'department_id'    => 'required|integer|exists:departments,id',
            'budget_period_id' => 'nullable|integer|exists:budget_periods,id',
            'title'            => 'required|string|max:200',
            'purpose'          => 'nullable|string|max:1000',
            'amount'           => 'required|numeric|min:1000',
        ]);

        abort_unless($user->canAccessOrganization((int) $request->organization_id), 403);

        $activePosition = $employee->activePosition?->position;
        abort_unless($activePosition, 422, 'Anda tidak memiliki jabatan aktif. Hubungi HRD.');

        DB::transaction(function () use ($request, $employee, $activePosition) {
            $reference = FundRequest::generateReference(
                (int) $request->organization_id,
                now()->toDateString()
            );

            FundRequest::create([
                'organization_id'       => $request->organization_id,
                'department_id'         => $request->department_id,
                'budget_period_id'      => $request->budget_period_id ?: null,
                'requester_id'          => $employee->id,
                'requester_position_id' => $activePosition->id,
                'reference'             => $reference,
                'title'                 => $request->title,
                'purpose'               => $request->purpose,
                'amount'                => $request->amount,
                'status'                => 'draft',
                'current_step'          => 0,
                'total_steps'           => 0,
            ]);
        });

        return redirect()->route('fund-requests.index')
            ->with('success', 'Pengajuan dana berhasil dibuat sebagai draft.');
    }

    public function show(FundRequest $fundRequest)
    {
        $user = auth()->user();
        abort_unless(
            $fundRequest->requester->user_id === $user->id || $user->canAccessOrganization($fundRequest->organization_id),
            403
        );

        $fundRequest->load(['organization', 'department', 'budgetPeriod', 'requester', 'requesterPosition', 'approvals.approverPosition', 'approvals.approverUser']);

        $canApprove = $this->currentUserCanApprove($fundRequest, $user);

        return view('fund-requests.show', compact('fundRequest', 'canApprove'));
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
            'department_id'    => 'required|integer|exists:departments,id',
            'budget_period_id' => 'nullable|integer|exists:budget_periods,id',
            'title'            => 'required|string|max:200',
            'purpose'          => 'nullable|string|max:1000',
            'amount'           => 'required|numeric|min:1000',
        ]);

        $fundRequest->update([
            'department_id'    => $request->department_id,
            'budget_period_id' => $request->budget_period_id ?: null,
            'title'            => $request->title,
            'purpose'          => $request->purpose,
            'amount'           => $request->amount,
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
        $orgId = (int) $request->organization_id;
        abort_unless($orgId && $user->canAccessOrganization($orgId), 403);

        return response()->json([
            'departments'    => Department::where('organization_id', $orgId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'budget_periods' => BudgetPeriod::where('organization_id', $orgId)->where('is_active', true)->orderByDesc('year')->get(['id', 'name']),
        ]);
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
