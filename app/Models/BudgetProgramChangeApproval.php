<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetProgramChangeApproval extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'change_request_id', 'step', 'approver_role',
        'approver_user_id', 'status', 'notes', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function changeRequest()
    {
        return $this->belongsTo(BudgetProgramChangeRequest::class, 'change_request_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
