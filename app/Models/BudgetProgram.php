<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProgram extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'budget_allocation_id', 'account_id', 'name', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function budgetAllocation()
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function details()
    {
        return $this->hasMany(BudgetProgramDetail::class);
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->details()->sum('total_amount');
    }
}
