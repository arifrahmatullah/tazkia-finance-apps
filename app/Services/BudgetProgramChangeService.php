<?php

namespace App\Services;

use App\Models\BudgetProgram;
use App\Models\BudgetProgramApprovalPosition;
use App\Models\BudgetProgramChangeApproval;
use App\Models\BudgetProgramChangeRequest;
use App\Models\BudgetProgramDetail;
use App\Models\BudgetProgramSchedule;
use App\Models\EmployeePosition;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Edit Program Kerja (info, rincian, nominal per termin) di luar periode perencanaan
// (planning_start s.d. planning_end) tidak diterapkan langsung -- harus lewat approval
// berjenjang: Keuangan lalu jabatan step-2 (mis. Warek Bidang Sumberdaya, dikonfigurasi
// per organisasi di budget_program_approval_positions). Kalau organisasi belum
// dikonfigurasi step-2-nya, cukup satu langkah (Keuangan saja).
class BudgetProgramChangeService
{
    public function requestChange(BudgetProgram $program, User $user, string $action, ?string $subjectId, array $payload, string $summary): BudgetProgramChangeRequest
    {
        $orgId    = $program->budgetAllocation->department->organization_id;
        $step2Pos = BudgetProgramApprovalPosition::where('organization_id', $orgId)->first();
        $totalSteps = $step2Pos ? 2 : 1;

        return DB::transaction(function () use ($program, $user, $action, $subjectId, $payload, $summary, $totalSteps, $step2Pos) {
            $request = BudgetProgramChangeRequest::create([
                'budget_program_id' => $program->id,
                'requested_by'      => $user->id,
                'action'            => $action,
                'subject_id'        => $subjectId,
                'payload'           => $payload,
                'summary'           => $summary,
                'status'            => 'pending',
                'current_step'      => 1,
                'total_steps'       => $totalSteps,
            ]);

            $request->approvals()->create(['step' => 1, 'approver_role' => 'keuangan', 'status' => 'waiting']);
            if ($totalSteps === 2) {
                $request->approvals()->create(['step' => 2, 'approver_role' => 'step2', 'status' => 'waiting']);
            }

            return $request;
        });
    }

    public function canApprove(BudgetProgramChangeApproval $approval, User $user): bool
    {
        if ($approval->status !== 'waiting' || $approval->changeRequest->current_step !== $approval->step) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $program = $approval->changeRequest->budgetProgram()->with('budgetAllocation.department')->first();
        $orgId   = $program->budgetAllocation->department->organization_id;

        if (!$user->canAccessOrganization($orgId)) {
            return false;
        }

        if ($approval->approver_role === 'keuangan') {
            return $user->hasPermission('menu.pencairan-dana');
        }

        // step2: pemegang jabatan yang dikonfigurasi utk organisasi ini
        $step2Pos = BudgetProgramApprovalPosition::where('organization_id', $orgId)->first();
        if (!$step2Pos) {
            return false;
        }

        $employee = $user->employee;
        if (!$employee) {
            return false;
        }

        return EmployeePosition::where('employee_id', $employee->id)
            ->where('position_id', $step2Pos->step2_position_id)
            ->where('is_active', true)
            ->exists();
    }

    public function approve(BudgetProgramChangeApproval $approval, User $user, ?string $notes = null): void
    {
        $approval->update(['status' => 'approved', 'notes' => $notes, 'acted_at' => now(), 'approver_user_id' => $user->id]);

        $request = $approval->changeRequest;
        if ($approval->step >= $request->total_steps) {
            $this->apply($request);
            $request->update(['status' => 'approved']);
        } else {
            $request->update(['current_step' => $approval->step + 1]);
        }
    }

    public function reject(BudgetProgramChangeApproval $approval, User $user, string $notes): void
    {
        $approval->update(['status' => 'rejected', 'notes' => $notes, 'acted_at' => now(), 'approver_user_id' => $user->id]);
        $approval->changeRequest->update(['status' => 'rejected']);
    }

    // Semua permintaan yang menunggu approval dari $user pada langkah saat ini
    public function pendingFor(User $user): Collection
    {
        return BudgetProgramChangeApproval::with(['changeRequest.budgetProgram.budgetAllocation.department', 'changeRequest.requestedBy'])
            ->where('status', 'waiting')
            ->whereHas('changeRequest', fn($q) => $q->where('status', 'pending')->whereColumn('current_step', 'budget_program_change_approvals.step'))
            ->get()
            ->filter(fn($approval) => $this->canApprove($approval, $user))
            ->values();
    }

    private function apply(BudgetProgramChangeRequest $request): void
    {
        $program = $request->budgetProgram;
        $payload = $request->payload ?? [];

        match ($request->action) {
            'update_info' => $this->applyUpdateInfo($program, $payload),
            'add_detail'  => $program->details()->create(array_merge($payload, ['quantity' => $program->frequency])),
            'update_detail' => BudgetProgramDetail::find($request->subject_id)?->update($payload),
            'delete_detail' => BudgetProgramDetail::find($request->subject_id)?->delete(),
            'update_schedule' => BudgetProgramSchedule::find($request->subject_id)?->update($payload),
            default => null,
        };
    }

    private function applyUpdateInfo(BudgetProgram $program, array $payload): void
    {
        $program->update($payload);

        if (array_key_exists('frequency', $payload)) {
            $program->details()->update(['quantity' => $payload['frequency']]);
            $program->refresh()->regenerateSchedules();
        }
    }
}
