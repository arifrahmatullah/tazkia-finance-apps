<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'name', 'code', 'type', 'parent_id', 'address', 'phone', 'email', 'logo', 'is_active',
        'fund_request_blocked', 'fund_request_block_reason', 'fund_request_blocked_at', 'fund_request_blocked_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active'             => 'boolean',
            'fund_request_blocked'  => 'boolean',
            'fund_request_blocked_at' => 'datetime',
        ];
    }

    public function fundRequestBlockedBy()
    {
        return $this->belongsTo(User::class, 'fund_request_blocked_by');
    }

    public function parent()
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
