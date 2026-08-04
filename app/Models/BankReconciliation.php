<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'account_id', 'period', 'statement_balance',
        'status', 'notes', 'created_by', 'completed_by', 'completed_at',
    ];

    protected $casts = [
        'period'            => 'date',
        'statement_balance' => 'float',
        'completed_at'      => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items()
    {
        return $this->hasMany(BankReconciliationItem::class)->orderBy('created_at');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    // Tanggal terakhir bulan periode — dipakai sebagai batas kumulatif saldo buku & tanggal jurnal penyesuaian
    public function periodEnd(): \Carbon\Carbon
    {
        return $this->period->copy()->endOfMonth();
    }
}
