<?php

namespace App\Notifications;

use App\Models\CashTopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CashTopupRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CashTopupRequest $topup, public ?string $notes = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $topup    = $this->topup;
        $approved = $topup->isApproved();

        $mail = (new MailMessage)
            ->subject('Pengajuan Saldo ' . ($approved ? 'Disetujui' : 'Ditolak') . ' -- ' . $topup->reference)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Pengajuan saldo Anda telah ' . ($approved ? 'disetujui' : 'ditolak') . ' oleh Yayasan:')
            ->line("Referensi: {$topup->reference}")
            ->line('Jumlah: Rp ' . number_format((float) $topup->amount, 0, ',', '.'));

        if ($approved) {
            $mail->line('Rekening ' . ($topup->targetAccount->name ?? '-') . ' sudah bertambah saldonya dan bisa digunakan untuk mencairkan pengajuan dana.');
        } elseif ($this->notes) {
            $mail->line('Alasan: ' . $this->notes);
        }

        return $mail->action('Lihat Detail Pengajuan', url('/cash-topup-requests/' . $topup->id));
    }

    public function toArray(object $notifiable): array
    {
        $topup = $this->topup;

        return [
            'type'      => 'cash_topup_request_status_changed',
            'topup_id'  => $topup->id,
            'reference' => $topup->reference,
            'status'    => $topup->status,
            'amount'    => $topup->amount,
            'url'       => '/cash-topup-requests/' . $topup->id,
        ];
    }
}
