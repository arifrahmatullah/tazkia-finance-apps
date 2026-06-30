<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOrganizationRole extends Model
{
    protected $fillable = ['user_id', 'organization_id', 'role_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
