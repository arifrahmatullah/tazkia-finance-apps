<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FundReport extends Model
{
    use HasUuids;

    protected $fillable = [
        'fund_request_id', 'reported_by',
        'report_date', 'description', 'amount_used',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'amount_used' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function files()
    {
        return $this->hasMany(FundReportFile::class)->latest();
    }

    public function fundRefund()
    {
        return $this->hasOne(FundRefund::class);
    }

    public function isWaiting(): bool   { return $this->status === 'waiting'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
}
