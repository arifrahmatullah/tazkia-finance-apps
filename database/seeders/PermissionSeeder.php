<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Umum
            ['slug' => 'menu.dashboard',            'name' => 'Dashboard',              'group' => 'umum'],

            // Master Data
            ['slug' => 'menu.organisasi',           'name' => 'Organisasi',             'group' => 'master'],
            ['slug' => 'menu.departemen',           'name' => 'Departemen',             'group' => 'master'],
            ['slug' => 'menu.jabatan',              'name' => 'Jabatan',                'group' => 'master'],
            ['slug' => 'menu.karyawan',             'name' => 'Karyawan',               'group' => 'master'],
            ['slug' => 'menu.approval-settings',    'name' => 'Setting Approval',       'group' => 'master'],

            // Keuangan
            ['slug' => 'menu.periode-anggaran',     'name' => 'Periode Anggaran',       'group' => 'keuangan'],
            ['slug' => 'menu.estimasi-pendapatan',  'name' => 'Estimasi Pendapatan',    'group' => 'keuangan'],
            ['slug' => 'menu.pagu-anggaran',        'name' => 'Pagu Anggaran',          'group' => 'keuangan'],
            ['slug' => 'menu.program-kerja',        'name' => 'Program Kerja',          'group' => 'keuangan'],
            ['slug' => 'menu.pengajuan-dana',       'name' => 'Pengajuan Dana',         'group' => 'keuangan'],
            ['slug' => 'menu.inbox-approval',       'name' => 'Inbox Approval',         'group' => 'keuangan'],
            ['slug' => 'menu.pencairan-dana',       'name' => 'Pencairan Dana',         'group' => 'keuangan'],

            // Akunting
            ['slug' => 'menu.jurnal-umum',          'name' => 'Jurnal Umum',            'group' => 'akunting'],
            ['slug' => 'menu.coa',                  'name' => 'Chart of Accounts',      'group' => 'akunting'],

            // Laporan
            ['slug' => 'menu.laporan',              'name' => 'Laporan Keuangan',       'group' => 'laporan'],

            // Sistem
            ['slug' => 'menu.users',                'name' => 'Manajemen User',         'group' => 'sistem'],
            ['slug' => 'menu.role-permissions',     'name' => 'Setting Permission',     'group' => 'sistem'],
            ['slug' => 'menu.audit-logs',           'name' => 'Audit Log',              'group' => 'sistem'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Default permissions per role
        $rolePermissions = [
            'keuangan' => [
                'menu.dashboard', 'menu.approval-settings',
                'menu.periode-anggaran', 'menu.estimasi-pendapatan',
                'menu.pagu-anggaran', 'menu.program-kerja',
                'menu.pengajuan-dana', 'menu.inbox-approval',
                'menu.pencairan-dana',
                'menu.coa', 'menu.laporan',
            ],
            'akunting' => [
                'menu.dashboard',
                'menu.jurnal-umum', 'menu.coa', 'menu.laporan',
            ],
            'staf' => [
                'menu.dashboard',
                'menu.program-kerja', 'menu.pengajuan-dana',
            ],
        ];

        foreach ($rolePermissions as $roleSlug => $slugs) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) continue;

            $ids = Permission::whereIn('slug', $slugs)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);
        }

        $this->command->info('Permissions seeded.');
    }
}
