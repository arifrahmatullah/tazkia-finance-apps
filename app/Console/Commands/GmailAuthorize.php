<?php

namespace App\Console\Commands;

use Google\Client as GoogleClient;
use Illuminate\Console\Command;

class GmailAuthorize extends Command
{
    protected $signature = 'gmail:authorize {code? : Authorization code dari Google (kosongkan untuk cetak link login)}';
    protected $description = 'Otorisasi akun Gmail sekali untuk dapatkan refresh token pengiriman email (Gmail API)';

    public function handle(): int
    {
        $secretPath = storage_path('app/google/client_secret.json');

        if (! file_exists($secretPath)) {
            $this->error("File credential tidak ditemukan di: {$secretPath}");
            $this->line('Taruh file client_secret_*.json dari Google Cloud Console di sana dulu, lalu rename jadi client_secret.json');
            return self::FAILURE;
        }

        $client = new GoogleClient();
        $client->setAuthConfig($secretPath);
        $client->addScope('https://www.googleapis.com/auth/gmail.send');
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setRedirectUri('http://localhost');

        $code = trim((string) $this->argument('code'));

        if ($code === '') {
            $authUrl = $client->createAuthUrl();

            $this->info('1) Buka link ini di browser, login pakai akun email yang mau dipakai kirim, lalu klik Allow:');
            $this->line('');
            $this->line($authUrl);
            $this->line('');
            $this->info('2) Setelah klik Allow, browser akan redirect ke alamat seperti:');
            $this->line('   http://localhost/?code=XXXXXXX&scope=...');
            $this->line('   (halaman akan terlihat error "tidak bisa diakses" — itu wajar, abaikan)');
            $this->info('3) Copy nilai setelah "code=" sampai sebelum "&scope" dari address bar itu.');
            $this->info('4) Jalankan lagi: php artisan gmail:authorize "<code_tadi>"');

            return self::SUCCESS;
        }

        try {
            $token = $client->fetchAccessTokenWithAuthCode($code);
        } catch (\Throwable $e) {
            $this->error('Gagal tukar code jadi token: '.$e->getMessage());
            return self::FAILURE;
        }

        if (isset($token['error'])) {
            $this->error('Google menolak: '.($token['error_description'] ?? $token['error']));
            return self::FAILURE;
        }

        if (empty($token['refresh_token'])) {
            $this->error('Tidak ada refresh_token di respons. Kemungkinan akun ini sudah pernah authorize sebelumnya.');
            $this->line('Coba cabut akses dulu di https://myaccount.google.com/permissions lalu ulangi command ini.');
            return self::FAILURE;
        }

        file_put_contents(storage_path('app/google/token.json'), json_encode($token, JSON_PRETTY_PRINT));

        $this->newLine();
        $this->info('Berhasil! Refresh token tersimpan di storage/app/google/token.json');
        $this->line('Refresh token: '.$token['refresh_token']);

        return self::SUCCESS;
    }
}
