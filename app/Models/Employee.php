<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id', 'organization_id', 'nik', 'name', 'title',
        'gender', 'birth_date', 'nidn', 'email', 'phone', 'rfid', 'is_active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active'  => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function positions()
    {
        return $this->hasMany(EmployeePosition::class);
    }

    public function activePosition()
    {
        return $this->hasOne(EmployeePosition::class)->where('is_active', true)->latest('start_date');
    }

    // Data lama punya ID integer, bukan UUID — skip validasi format UUID
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}
