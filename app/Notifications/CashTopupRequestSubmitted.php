<?php

namespace App\Notifications;

use App\Models\CashTopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashTopupRequestSubmitted extends Notification implements ShouldQueue
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
            ->subject('Pengajuan Saldo Baru Menunggu Persetujuan')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada pengajuan tambahan saldo baru yang menunggu persetujuan Anda:')
            ->line("Referensi: {$topup->reference}")
            ->line('Organisasi Pengaju: ' . $topup->requestingOrganization->name)
            ->line('Jumlah: Rp ' . number_format((float) $topup->amount, 0, ',', '.'))
            ->line('Diajukan Oleh: ' . ($topup->requestedBy->name ?? '-'))
            ->action('Lihat & Proses Pengajuan', url('/cash-topup-requests/' . $topup->id))
            ->line('Mohon segera diproses agar pencairan dana di organisasi tersebut tidak tertunda.');
    }

    public function toArray(object $notifiable): array
    {
        $topup = $this->topup;

        return [
            'type'         => 'cash_topup_request_submitted',
            'topup_id'     => $topup->id,
            'reference'    => $topup->reference,
            'organization' => $topup->requestingOrganization->name,
            'amount'       => $topup->amount,
            'url'          => '/cash-topup-requests/' . $topup->id,
        ];
    }
}
