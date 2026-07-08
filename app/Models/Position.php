<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'department_id', 'code', 'name', 'description',
        'is_finance_related', 'can_create_program', 'is_active',
    ];

    protected $casts = [
        'is_finance_related'  => 'boolean',
        'can_create_program'  => 'boolean',
        'is_active'           => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function employeePositions()
    {
        return $this->hasMany(EmployeePosition::class);
    }
}
