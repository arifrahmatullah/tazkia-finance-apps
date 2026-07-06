<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetAllocation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'budget_period_id', 'department_id', 'amount',
        'percentage', 'source', 'notes', 'is_blocking', 'is_active',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'percentage'  => 'decimal:4',
        'is_blocking' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function budgetPeriod()
    {
        return $this->belongsTo(BudgetPeriod::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function programs()
    {
        return $this->hasMany(BudgetProgram::class);
    }
}
