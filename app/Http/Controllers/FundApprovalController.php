<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\FundRequestApproval;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FundApprovalController extends Controller
{
    public function inbox(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun belum terhubung dengan data karyawan.');

        $activePosition = $employee->activePosition?->position;
        if (!$activePosition) {
            return view('fund-approvals.inbox', [
                'approvals'    => new LengthAwarePaginator([], 0, 15),
                'positionName' => null,
                'organizations'=> collect(),
                'filterStatus' => 'waiting',
            ]);
        }

        $orgIds       = $user->organizationIds();
        $filterStatus = $request->get('status', 'waiting');
        if (!in_array($filterStatus, ['waiting', 'approved', 'rejected'])) {
            $filterStatus = 'waiting';
        }

        $query = FundRequestApproval::with([
            'fundRequest.organization',
            'fundRequest.department',
            'fundRequest.requester',
            'fundRequest.requesterPosition',
        ])
        ->where('approver_position_id', $activePosition->id)
        ->whereHas('fundRequest', function ($q) use ($orgIds, $request) {
            if ($orgIds !== null) {
                $q->whereIn('organization_id', $orgIds);
            }
            if ($request->filled('organization_id')) {
                $q->where('organization_id', $request->organization_id);
            }
            if ($request->filled('search')) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('reference', 'like', $s)->orWhere('title', 'like', $s));
            }
        });

        if ($filterStatus === 'waiting') {
            $query->where('status', 'waiting')
                ->whereHas('fundRequest', fn($q) => $q->where('status', 'pending'))
                ->whereHas('fundRequest', fn($q) => $q->whereColumn('current_step', 'fund_request_approvals.step'));
        } else {
            $query->where('status', $filterStatus);
        }

        $approvals = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        return view('fund-approvals.inbox', compact('approvals', 'organizations', 'filterStatus'))
            ->with('positionName', $activePosition->name);
    }

    public function approve(Request $request, FundRequestApproval $fundRequestApproval)
    {
        $user = auth()->user();
        $this->authorizeApprover($fundRequestApproval, $user);

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $fundRequestApproval, $user) {
            $fundRequestApproval->update([
                'status'           => 'approved',
                'approver_user_id' => $user->id,
                'notes'            => $request->notes,
                'acted_at'         => now(),
            ]);

            $fundRequest = $fundRequestApproval->fundRequest;

            $nextApproval = $fundRequest->approvals()
                ->where('step', '>', $fundRequestApproval->step)
                ->where('status', 'waiting')
                ->orderBy('step')
                ->first();

            if ($nextApproval) {
                $fundRequest->update(['current_step' => $nextApproval->step]);
            } else {
                $fundRequest->update([
                    'status'       => 'approved',
                    'current_step' => 0,
                    'approved_at'  => now(),
                ]);
            }
        });

        return redirect()->route('fund-approvals.inbox')
            ->with('success', 'Pengajuan berhasil disetujui.');
    }

    public function reject(Request $request, FundRequestApproval $fundRequestApproval)
    {
        $user = auth()->user();
        $this->authorizeApprover($fundRequestApproval, $user);

        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($request, $fundRequestApproval, $user) {
            $fundRequestApproval->update([
                'status'           => 'rejected',
                'approver_user_id' => $user->id,
                'notes'            => $request->notes,
                'acted_at'         => now(),
            ]);

            $fundRequestApproval->fundRequest->update([
                'status'       => 'rejected',
                'current_step' => 0,
                'rejected_at'  => now(),
            ]);
        });

        return redirect()->route('fund-approvals.inbox')
            ->with('success', 'Pengajuan berhasil ditolak.');
    }

    private function authorizeApprover(FundRequestApproval $approval, $user): void
    {
        abort_unless($approval->status === 'waiting', 403, 'Approval sudah diproses.');
        abort_unless($approval->fundRequest->isPending(), 403, 'Pengajuan tidak dalam status pending.');
        abort_unless($approval->fundRequest->current_step === $approval->step, 403, 'Bukan giliran Anda.');

        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun belum terhubung dengan data karyawan.');

        $activePosition = $employee->activePosition?->position;
        abort_unless($activePosition, 403, 'Anda tidak memiliki jabatan aktif.');
        abort_unless($activePosition->id === $approval->approver_position_id, 403, 'Jabatan Anda tidak berwenang untuk approval ini.');
    }
}
