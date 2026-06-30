<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $yayasan = Organization::updateOrCreate(
            ['code' => 'YAYASAN'],
            [
                'name'      => 'Yayasan Tazkia',
                'type'      => 'yayasan',
                'parent_id' => null,
                'is_active' => true,
            ]
        );

        $kampusTazkia = Organization::updateOrCreate(
            ['code' => 'TAZKIA'],
            [
                'name'      => 'Kampus Tazkia',
                'type'      => 'kampus',
                'parent_id' => $yayasan->id,
                'is_active' => true,
            ]
        );

        Organization::updateOrCreate(
            ['code' => 'STMIK'],
            [
                'name'      => 'STMIK Tazkia',
                'type'      => 'kampus',
                'parent_id' => $yayasan->id,
                'is_active' => true,
            ]
        );
    }
}
