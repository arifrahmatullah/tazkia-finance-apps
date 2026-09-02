<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            'Bank Mandiri', 'Bank Negara Indonesia (BNI)', 'Bank Rakyat Indonesia (BRI)',
            'Bank Central Asia (BCA)', 'Bank Syariah Indonesia (BSI)', 'Bank Tabungan Negara (BTN)',
            'CIMB Niaga', 'Bank Danamon', 'Bank Permata', 'Bank Panin', 'OCBC NISP',
            'Maybank Indonesia', 'Bank Mega', 'Bank Muamalat', 'Bank Bukopin',
            'Bank BJB', 'Bank DKI', 'Bank Jateng', 'Bank Jatim', 'Bank Sinarmas',
            'Bank UOB Indonesia', 'Bank HSBC Indonesia', 'Bank Commonwealth', 'Bank Mayapada',
        ];

        foreach ($banks as $name) {
            Bank::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $this->command->info('Banks seeded.');
    }
}
