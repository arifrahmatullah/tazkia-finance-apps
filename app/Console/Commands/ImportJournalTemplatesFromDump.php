<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\JournalTemplate;
use App\Models\JournalTemplateDetail;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportJournalTemplatesFromDump extends Command
{
    protected $signature = 'journal-templates:import-from-dump {--dry-run : Lihat perubahan tanpa eksekusi}';
    protected $description = 'Import template jurnal dari dump aplikasi akunting lama ke semua organisasi';

    public function handle(): void
    {
        $dryRun   = $this->option('dry-run');
        $jsonPath = storage_path('app/legacy-journal-templates.json');

        if (!file_exists($jsonPath)) {
            $this->error("File tidak ditemukan: $jsonPath");
            return;
        }

        $templates = json_decode(file_get_contents($jsonPath), true);
        $organizations = Organization::all();

        $created         = 0;
        $skippedExisting = 0;
        $skippedRows     = [];

        foreach ($organizations as $org) {
            $accountIdsByCode = Account::where('organization_id', $org->id)
                ->where('is_header', false)
                ->pluck('id', 'code');

            foreach ($templates as $tpl) {
                if (JournalTemplate::where('organization_id', $org->id)->where('code', $tpl['code'])->exists()) {
                    $skippedExisting++;
                    continue;
                }

                $missingCode = collect($tpl['details'])
                    ->first(fn($d) => !$accountIdsByCode->has($d['account_code']));

                if ($missingCode) {
                    $skippedRows[] = [$org->code, $tpl['code'], $tpl['name'], "akun {$missingCode['account_code']} tidak ada di organisasi ini"];
                    continue;
                }

                if (!$dryRun) {
                    DB::transaction(function () use ($org, $tpl, $accountIdsByCode) {
                        $template = JournalTemplate::create([
                            'organization_id' => $org->id,
                            'code'            => $tpl['code'],
                            'name'            => $tpl['name'],
                            'category'        => $tpl['category'],
                            'tags'            => $tpl['tag'] ? [$tpl['tag']] : null,
                            'is_active'       => true,
                        ]);

                        foreach ($tpl['details'] as $d) {
                            JournalTemplateDetail::create([
                                'journal_template_id' => $template->id,
                                'account_id'          => $accountIdsByCode[$d['account_code']],
                                'balance_type'        => $d['balance_type'],
                                'sequence'            => $d['sequence'],
                            ]);
                        }
                    });
                }

                $created++;
            }
        }

        if ($skippedRows) {
            $this->table(['Organisasi', 'Kode', 'Nama', 'Alasan Skip'], $skippedRows);
        }

        $this->newLine();
        $this->info('Selesai:');
        $this->line("  Dibuat           : $created");
        $this->line("  Sudah ada (skip) : $skippedExisting");
        $this->line('  Akun tidak ada   : ' . count($skippedRows));

        if ($dryRun) {
            $this->warn('(Dry-run — tidak ada yang disimpan. Jalankan tanpa --dry-run untuk eksekusi)');
        }
    }
}
