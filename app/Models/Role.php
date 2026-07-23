<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids, Auditable;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'color'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function userOrganizationRoles()
    {
        return $this->hasMany(UserOrganizationRole::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
}
