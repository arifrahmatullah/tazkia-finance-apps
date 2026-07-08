<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IncomeEstimateDetail extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'income_estimate_id', 'estimate_date', 'description',
        'qty', 'unit_price', 'total',
    ];

    protected $casts = [
        'estimate_date' => 'date',
        'qty'           => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    public function incomeEstimate()
    {
        return $this->belongsTo(IncomeEstimate::class);
    }
}
