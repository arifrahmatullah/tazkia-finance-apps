<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateUsersFromEmployees extends Command
{
    protected $signature   = 'employees:generate-users {--dry-run : Preview saja tanpa simpan}';
    protected $description = 'Generate akun user dari data karyawan yang belum punya akun';

    public function handle(): void
    {
        $roles = Role::pluck('id', 'slug');

        // Ambil email yang unik (tidak duplikat antar karyawan)
        $duplicateEmails = Employee::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('is_active', true)
            ->selectRaw('email, COUNT(*) as cnt')
            ->groupBy('email')
            ->having('cnt', '>', 1)
            ->pluck('email')
            ->toArray();

        $employees = Employee::with(['activePosition.position'])
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('is_active', true)
            ->whereNotIn('email', $duplicateEmails)
            ->get();

        if (!empty($duplicateEmails)) {
            $this->warn('Email duplikat dilewati (' . count($duplicateEmails) . ' email, perlu diperbaiki manual):');
            foreach ($duplicateEmails as $email) {
                $this->line("    - {$email}");
            }
            $this->newLine();
        }

        if ($employees->isEmpty()) {
            $this->info('Tidak ada karyawan aktif yang perlu dibuatkan akun.');
            return;
        }

        $dryRun  = $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        $this->info("Memproses {$employees->count()} karyawan..." . ($dryRun ? ' [DRY RUN]' : ''));
        $this->newLine();

        foreach ($employees as $employee) {
            if (User::where('email', $employee->email)->exists()) {
                $this->line("  <comment>SKIP</comment>  {$employee->email} — email sudah terdaftar");
                $skipped++;
                continue;
            }

            $roleSlug = $this->resolveRole($employee->activePosition?->position?->name ?? '');
            $roleId   = $roles[$roleSlug] ?? $roles['staf'];

            if (!$dryRun) {
                $user = User::create([
                    'name'      => $employee->name,
                    'email'     => $employee->email,
                    'password'  => Hash::make('tazkia123'),
                    'role_id'   => $roleId,
                    'is_active' => true,
                ]);

                $employee->update(['user_id' => $user->id]);
            }

            $this->line("  <info>OK</info>    {$employee->email} — {$employee->name} [{$roleSlug}]");
            $created++;
        }

        $this->newLine();
        $this->info("Selesai. Dibuat: {$created} | Dilewati (email duplikat): {$skipped}");

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang disimpan. Jalankan tanpa --dry-run untuk eksekusi.');
        } else {
            $this->info('Password default semua akun baru: tazkia123');
        }
    }

    private function resolveRole(string $positionName): string
    {
        $lower = mb_strtolower($positionName);

        if (str_contains($lower, 'accounting') || str_contains($lower, 'akuntansi')) {
            return 'akunting';
        }

        if (str_contains($lower, 'keuangan') || str_contains($lower, 'bendahara')) {
            return 'keuangan';
        }

        if ($lower === 'superadmin') {
            return 'superadmin';
        }

        return 'staf';
    }
}
