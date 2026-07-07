<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SyncRolesFromDump extends Command
{
    protected $signature = 'users:sync-roles-from-dump {--dry-run : Lihat perubahan tanpa eksekusi}';
    protected $description = 'Sinkronisasi role user dari dump aplikasi lama';

    // Pemetaan role lama → role baru
    private array $roleMap = [
        'superuser'   => 'superadmin',
        'keuangan'    => 'keuangan',
        'kasir'       => 'keuangan',
        'anggaran'    => 'keuangan',
        'auditor'     => 'akunting',
        'adminytc'    => 'staf',
        'warek'       => 'staf',
        'warek2'      => 'staf',
        'mutu'        => 'staf',
        'magang'      => 'staf',
        'pembinaytc'  => 'staf',
        'pengurusytc' => 'staf',
        'staffkeu'    => 'keuangan',
        'keuanganytc' => 'keuangan',
        'staff'       => 'staf',
    ];

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $csvPath = 'C:\\Users\\User\\AppData\\Local\\Temp\\dump_users.csv';

        if (!file_exists($csvPath)) {
            $this->error("File tidak ditemukan: $csvPath");
            return;
        }

        // Load CSV
        $rows = array_map('str_getcsv', file($csvPath));
        array_shift($rows); // skip header

        $dumpMap = []; // email => old_role
        foreach ($rows as $row) {
            if (count($row) >= 2) {
                $dumpMap[strtolower(trim($row[0]))] = trim($row[1]);
            }
        }

        $this->info("Email dari dump: " . count($dumpMap));

        // Load roles
        $roles = Role::all()->keyBy('slug');

        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $unmapped = 0;

        $rows = collect($dumpMap)->map(function ($oldRole, $email) use ($roles, $dryRun, &$updated, &$skipped, &$notFound, &$unmapped) {
            $newRoleSlug = $this->roleMap[$oldRole] ?? null;

            if (!$newRoleSlug) {
                $unmapped++;
                return ['email' => $email, 'lama' => $oldRole, 'baru' => '-', 'status' => 'role tidak dikenal'];
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                $notFound++;
                return null;
            }

            $currentRole = $roles[$user->role_id ? Role::find($user->role_id)?->slug : ''] ?? null;
            $currentSlug = $user->role?->slug ?? 'no-role';

            if ($currentSlug === $newRoleSlug) {
                $skipped++;
                return null;
            }

            $newRole = $roles[$newRoleSlug] ?? null;
            if (!$newRole) {
                return ['email' => $email, 'lama_role' => $oldRole, 'baru' => $newRoleSlug, 'status' => 'role baru tidak ada di db'];
            }

            if (!$dryRun) {
                $user->role_id = $newRole->id;
                $user->save();
            }

            $updated++;
            return ['email' => $email, 'role_dump' => $oldRole, 'dari' => $currentSlug, 'menjadi' => $newRoleSlug, 'status' => $dryRun ? 'akan diubah' : 'diubah'];
        })->filter()->values();

        if ($rows->isNotEmpty()) {
            $this->table(['Email', 'Role di Dump', 'Dari', 'Menjadi', 'Status'], $rows->map(fn($r) => [
                $r['email'],
                $r['role_dump'] ?? $r['lama'] ?? '-',
                $r['dari'] ?? '-',
                $r['menjadi'] ?? $r['baru'] ?? '-',
                $r['status'],
            ]));
        }

        $this->newLine();
        $this->info("Selesai:");
        $this->line("  Diubah    : $updated");
        $this->line("  Sama      : $skipped");
        $this->line("  Tidak ada : $notFound");
        $this->line("  Unmapped  : $unmapped");

        if ($dryRun) {
            $this->warn("(Dry-run — tidak ada yang disimpan. Jalankan tanpa --dry-run untuk eksekusi)");
        }
    }
}
