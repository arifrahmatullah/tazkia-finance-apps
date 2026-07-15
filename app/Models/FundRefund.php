<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FundRefund extends Model
{
    use HasUuids;

    protected $fillable = [
        'fund_request_id', 'fund_report_id', 'amount', 'status',
        'paid_by', 'paid_at', 'refund_account_id', 'payment_notes',
        'proof_path', 'proof_name',
        'confirmed_by', 'confirmed_at', 'confirmation_notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'paid_at'      => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function fundReport()
    {
        return $this->belongsTo(FundReport::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function refundAccount()
    {
        return $this->belongsTo(Account::class, 'refund_account_id');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isWaiting(): bool   { return $this->status === 'waiting'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? Storage::url($this->proof_path) : null;
    }
}
