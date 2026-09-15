<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// Pengajuan tambahan saldo dari Kampus/STMIK ke Yayasan (organisasi induknya), dipakai saat
// rekening pencairan Kampus/STMIK tidak cukup saldo buat mencairkan pengajuan dana yang sudah
// disetujui. Daftar pengajuan dana yang disertakan (fundRequests) sifatnya cuma konteks/
// justifikasi buat Yayasan -- begitu saldo cair, dana itu jadi pool bersama, tidak terikat
// cuma buat yang disertakan di sini.
class CashTopupRequest extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'requesting_organization_id', 'target_account_id', 'source_credit_account_id',
        'requested_by', 'reference', 'amount', 'notes', 'status',
        'reviewed_by', 'reviewed_at', 'review_notes',
        'yayasan_source_account_id', 'yayasan_debit_account_id',
        'proof_path', 'proof_name',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function requestingOrganization()
    {
        return $this->belongsTo(Organization::class, 'requesting_organization_id');
    }

    public function targetAccount()
    {
        return $this->belongsTo(Account::class, 'target_account_id');
    }

    public function sourceCreditAccount()
    {
        return $this->belongsTo(Account::class, 'source_credit_account_id');
    }

    public function yayasanSourceAccount()
    {
        return $this->belongsTo(Account::class, 'yayasan_source_account_id');
    }

    public function yayasanDebitAccount()
    {
        return $this->belongsTo(Account::class, 'yayasan_debit_account_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function fundRequests()
    {
        return $this->belongsToMany(FundRequest::class, 'cash_topup_request_fund_requests')
            ->withTimestamps();
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? \Illuminate\Support\Facades\Storage::url($this->proof_path) : null;
    }

    public static function generateReference(string $orgId, string $date): string
    {
        $year   = date('Y', strtotime($date));
        $month  = date('m', strtotime($date));
        $prefix = "SLD-{$year}{$month}-";

        $last = self::where('requesting_organization_id', $orgId)
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
