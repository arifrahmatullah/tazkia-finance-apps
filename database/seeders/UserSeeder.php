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
        $roles = Role::pluck('id', 'slug');
        $orgs  = \App\Models\Organization::pluck('id', 'code');

        $users = [
            [
                'name'    => 'Super Admin',
                'email'   => 'superadmin@tazkia.ac.id',
                'role'    => 'superadmin',
                'org'     => null, // akses semua
            ],
            [
                'name'    => 'Keuangan Yayasan',
                'email'   => 'keuangan.yayasan@tazkia.ac.id',
                'role'    => 'keuangan',
                'org'     => 'YAYASAN',
            ],
            [
                'name'    => 'Keuangan Tazkia',
                'email'   => 'keuangan.tazkia@tazkia.ac.id',
                'role'    => 'keuangan',
                'org'     => 'TAZKIA',
            ],
            [
                'name'    => 'Keuangan STMIK',
                'email'   => 'keuangan.stmik@tazkia.ac.id',
                'role'    => 'keuangan',
                'org'     => 'STMIK',
            ],
            [
                'name'    => 'Akunting Tazkia',
                'email'   => 'akunting.tazkia@tazkia.ac.id',
                'role'    => 'akunting',
                'org'     => 'TAZKIA',
            ],
            [
                'name'    => 'Akunting STMIK',
                'email'   => 'akunting.stmik@tazkia.ac.id',
                'role'    => 'akunting',
                'org'     => 'STMIK',
            ],
            [
                'name'    => 'Staf Tazkia',
                'email'   => 'staf.tazkia@tazkia.ac.id',
                'role'    => 'staf',
                'org'     => 'TAZKIA',
            ],
            [
                'name'    => 'Staf STMIK',
                'email'   => 'staf.stmik@tazkia.ac.id',
                'role'    => 'staf',
                'org'     => 'STMIK',
            ],
        ];

        foreach ($users as $data) {
            $roleId = $roles[$data['role']];
            $orgId  = $data['org'] ? $orgs[$data['org']] : null;

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => Hash::make('password'),
                    'role_id'   => $roleId,
                    'is_active' => true,
                ]
            );

            UserOrganizationRole::updateOrCreate(
                ['user_id' => $user->id, 'organization_id' => $orgId, 'role_id' => $roleId]
            );
        }
    }
}
