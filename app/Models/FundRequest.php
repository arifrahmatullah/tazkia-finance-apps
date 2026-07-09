<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequest extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'department_id', 'budget_period_id', 'budget_program_id',
        'requester_id', 'requester_position_id', 'reference',
        'title', 'purpose', 'amount',
        'bank_name', 'bank_account_number', 'bank_account_name',
        'status', 'current_step', 'total_steps', 'notes',
        'submitted_at', 'approved_at', 'rejected_at',
        'disbursed_at', 'disbursement_notes', 'disbursed_by', 'disburse_account_id',
        'receipt_status', 'receipt_confirmed_at', 'receipt_notes', 'auto_confirmed',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'submitted_at'         => 'datetime',
        'approved_at'          => 'datetime',
        'rejected_at'          => 'datetime',
        'disbursed_at'         => 'datetime',
        'receipt_confirmed_at' => 'datetime',
        'auto_confirmed'       => 'boolean',
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

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
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

    public function disburseAccount()
    {
        return $this->belongsTo(Account::class, 'disburse_account_id');
    }

    public function files()
    {
        return $this->hasMany(FundRequestFile::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(FundRequestFile::class)->where('type', 'attachment')->latest();
    }

    public function disbursementProofs()
    {
        return $this->hasMany(FundRequestFile::class)->where('type', 'disbursement_proof')->latest();
    }

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isDisbursed(): bool  { return !is_null($this->disbursed_at); }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public static function generateReference(string $orgId, string $date): string
    {
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "PD-{$year}{$month}-";

        $last = self::withTrashed()
            ->where('organization_id', $orgId)
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('reference')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
