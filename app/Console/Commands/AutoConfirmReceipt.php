<?php

namespace App\Console\Commands;

use App\Models\FundRequest;
use Illuminate\Console\Command;

class AutoConfirmReceipt extends Command
{
    protected $signature = 'app:auto-confirm-receipt';
    protected $description = 'Auto-confirm disbursed fund requests older than 7 days without receipt confirmation';

    public function handle(): int
    {
        $count = FundRequest::whereNotNull('disbursed_at')
            ->whereNull('receipt_status')
            ->where('disbursed_at', '<=', now()->subDays(7))
            ->update([
                'receipt_status'       => 'confirmed',
                'receipt_confirmed_at' => now(),
                'auto_confirmed'       => true,
            ]);

        $this->info("Auto-confirmed {$count} fund request(s).");
        return self::SUCCESS;
    }
}
