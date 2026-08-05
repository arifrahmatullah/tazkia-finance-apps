<?php

namespace App\Notifications;

use App\Models\FundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FundRequest $fundRequest, public string $notes = '')
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isApproved = $this->fundRequest->status === 'approved';

        $mail = (new MailMessage)
            ->subject($isApproved ? 'Pengajuan Dana Disetujui' : 'Pengajuan Dana Ditolak')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Pengajuan dana Anda berikut telah " . ($isApproved ? 'DISETUJUI' : 'DITOLAK') . ':')
            ->line("Referensi: {$this->fundRequest->reference}")
            ->line("Judul: {$this->fundRequest->title}")
            ->line('Jumlah: Rp ' . number_format((float) $this->fundRequest->amount, 0, ',', '.'));

        if ($this->notes) {
            $mail->line('Catatan: ' . $this->notes);
        }

        return $mail->action('Lihat Pengajuan', url('/fund-requests/' . $this->fundRequest->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'fund_request_status_changed',
            'fund_request_id' => $this->fundRequest->id,
            'reference'       => $this->fundRequest->reference,
            'title'           => $this->fundRequest->title,
            'status'          => $this->fundRequest->status,
            'notes'           => $this->notes,
            'url'             => '/fund-requests/' . $this->fundRequest->id,
        ];
    }
}
