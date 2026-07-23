<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IncomeReceipt extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'income_estimate_id', 'receipt_date', 'description',
        'qty', 'unit_price', 'total',
        'proof_path', 'proof_name', 'recorded_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'qty'          => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total'        => 'decimal:2',
    ];

    public function incomeEstimate()
    {
        return $this->belongsTo(IncomeEstimate::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? Storage::url($this->proof_path) : null;
    }
}
