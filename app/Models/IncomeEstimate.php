<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeEstimate extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'budget_period_id', 'description',
        'unit', 'unit_price', 'total_amount', 'is_active',
    ];

    protected $casts = [
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function organization()  { return $this->belongsTo(Organization::class); }
    public function budgetPeriod()  { return $this->belongsTo(BudgetPeriod::class); }
    public function details()       { return $this->hasMany(IncomeEstimateDetail::class); }

    public function recalculateTotal(): void
    {
        $total = $this->details()->sum('total') ?: $this->unit_price;
        $this->update(['total_amount' => $total]);
    }
}
