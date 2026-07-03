<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundRequestApproval extends Model
{
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
}
