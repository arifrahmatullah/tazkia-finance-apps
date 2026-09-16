<?php

namespace App\Notifications;

use App\Models\CashTopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashTopupRequestDirectFunded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CashTopupRequest $topup)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $topup = $this->topup;

        return (new MailMessage)
            ->subject('Saldo Rekening Anda Ditambah oleh Yayasan')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Yayasan baru saja mengisi saldo rekening organisasi Anda tanpa perlu pengajuan:')
            ->line("Referensi: {$topup->reference}")
            ->line('Rekening: ' . ($topup->targetAccount->name ?? '-'))
            ->line('Jumlah: Rp ' . number_format((float) $topup->amount, 0, ',', '.'))
            ->action('Lihat Detail', url('/cash-topup-requests/' . $topup->id))
            ->line('Saldo ini sudah bisa digunakan untuk mencairkan pengajuan dana.');
    }

    public function toArray(object $notifiable): array
    {
        $topup = $this->topup;

        return [
            'type'      => 'cash_topup_request_direct_funded',
            'topup_id'  => $topup->id,
            'reference' => $topup->reference,
            'amount'    => $topup->amount,
            'url'       => '/cash-topup-requests/' . $topup->id,
        ];
    }
}
