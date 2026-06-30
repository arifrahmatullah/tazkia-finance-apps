<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganizationRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('slug', 'superadmin')->first();

        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@tazkia.ac.id'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'role_id'   => $superadminRole->id,
                'is_active' => true,
            ]
        );

        // superadmin tidak terikat ke org tertentu (organization_id = null)
        UserOrganizationRole::updateOrCreate(
            [
                'user_id'         => $superadmin->id,
                'organization_id' => null,
                'role_id'         => $superadminRole->id,
            ]
        );
    }
}
