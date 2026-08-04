<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'code', 'name',
        'account_id', 'accumulated_depreciation_account_id', 'depreciation_expense_account_id',
        'acquisition_date', 'acquisition_cost', 'salvage_value', 'useful_life_months',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'acquisition_date'   => 'date',
        'acquisition_cost'   => 'float',
        'salvage_value'      => 'float',
        'useful_life_months' => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function depreciations()
    {
        return $this->hasMany(FixedAssetDepreciation::class)->orderBy('period');
    }

    public function getDepreciableBaseAttribute(): float
    {
        return max($this->acquisition_cost - $this->salvage_value, 0);
    }

    public function getMonthlyDepreciationAttribute(): float
    {
        return $this->useful_life_months > 0
            ? round($this->depreciable_base / $this->useful_life_months, 2)
            : 0.0;
    }

    public function getAccumulatedDepreciationAttribute(): float
    {
        return $this->relationLoaded('depreciations')
            ? (float) $this->depreciations->sum('amount')
            : (float) $this->depreciations()->sum('amount');
    }

    public function getBookValueAttribute(): float
    {
        return $this->acquisition_cost - $this->accumulated_depreciation;
    }

    public function getIsFullyDepreciatedAttribute(): bool
    {
        return $this->accumulated_depreciation >= $this->depreciable_base - 0.5;
    }

    // Sisa penyusutan yang boleh diposting bulan ini, dibatasi agar tidak melebihi nilai residu
    public function remainingDepreciationFor(float $monthly): float
    {
        $remaining = $this->depreciable_base - $this->accumulated_depreciation;
        return max(min($monthly, $remaining), 0);
    }
}
