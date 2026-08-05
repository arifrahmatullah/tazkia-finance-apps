<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FundRequestApproval extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'fund_request_id', 'step', 'approver_position_id',
        'approver_user_id', 'status', 'notes', 'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function approverPosition()
    {
        return $this->belongsTo(Position::class, 'approver_position_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    /**
     * User yang saat ini memegang jabatan approver_position_id secara aktif
     * (biasanya satu orang, tapi bisa lebih dari satu kalau data tidak konsisten).
     */
    public function approverUsers(): \Illuminate\Support\Collection
    {
        return EmployeePosition::where('position_id', $this->approver_position_id)
            ->where('is_active', true)
            ->with('employee.user')
            ->get()
            ->pluck('employee.user')
            ->filter()
            ->unique('id')
            ->values();
    }
}
