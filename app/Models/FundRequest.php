<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id', 'department_id', 'budget_period_id',
        'requester_id', 'requester_position_id', 'reference',
        'title', 'purpose', 'amount', 'status',
        'current_step', 'total_steps', 'notes',
        'submitted_at', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function budgetPeriod()
    {
        return $this->belongsTo(BudgetPeriod::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function requesterPosition()
    {
        return $this->belongsTo(Position::class, 'requester_position_id');
    }

    public function approvals()
    {
        return $this->hasMany(FundRequestApproval::class)->orderBy('step');
    }

    public function currentApproval()
    {
        return $this->hasOne(FundRequestApproval::class)
            ->where('step', $this->current_step)
            ->where('status', 'waiting');
    }

    public function isDraft(): bool    { return $this->status === 'draft'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public static function generateReference(int $orgId, string $date): string
    {
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "PD-{$year}{$month}-";

        $last = self::where('organization_id', $orgId)
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
