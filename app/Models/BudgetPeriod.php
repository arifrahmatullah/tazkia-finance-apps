<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id', 'code', 'name',
        'planning_start', 'planning_end',
        'period_start', 'period_end',
        'is_active',
    ];

    protected $casts = [
        'planning_start' => 'date',
        'planning_end'   => 'date',
        'period_start'   => 'date',
        'period_end'     => 'date',
        'is_active'      => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
