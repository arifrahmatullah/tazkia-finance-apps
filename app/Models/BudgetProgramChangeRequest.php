<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetProgramChangeRequest extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'budget_program_id', 'requested_by', 'action', 'subject_id',
        'payload', 'summary', 'status', 'current_step', 'total_steps',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvals()
    {
        return $this->hasMany(BudgetProgramChangeApproval::class, 'change_request_id')->orderBy('step');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
