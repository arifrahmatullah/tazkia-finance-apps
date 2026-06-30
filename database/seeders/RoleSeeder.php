<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'superadmin',
                'description' => 'Akses penuh ke semua organisasi dan fitur',
                'icon'        => 'ri-shield-star-line',
                'color'       => '#6366f1',
            ],
            [
                'name'        => 'Keuangan',
                'slug'        => 'keuangan',
                'description' => 'Kelola anggaran, pencairan dana, dan laporan keuangan',
                'icon'        => 'ri-money-dollar-circle-line',
                'color'       => '#10b981',
            ],
            [
                'name'        => 'Akunting',
                'slug'        => 'akunting',
                'description' => 'Kelola jurnal, laporan akuntansi, dan audit',
                'icon'        => 'ri-file-chart-line',
                'color'       => '#3b82f6',
            ],
            [
                'name'        => 'Staf',
                'slug'        => 'staf',
                'description' => 'Buat pengajuan dana dan budgeting',
                'icon'        => 'ri-user-line',
                'color'       => '#f59e0b',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
