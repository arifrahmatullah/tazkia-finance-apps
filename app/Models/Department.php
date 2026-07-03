<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id', 'code', 'name', 'description',
        'has_budget', 'budget_blocking', 'is_active',
    ];

    protected $casts = [
        'has_budget'      => 'boolean',
        'budget_blocking' => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
