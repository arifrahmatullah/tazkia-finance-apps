<?php

namespace App\Notifications;

use App\Models\FundRequestApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundRequestNeedsApproval extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FundRequestApproval $approval)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fundRequest = $this->approval->fundRequest;

        return (new MailMessage)
            ->subject('Pengajuan Dana Menunggu Persetujuan Anda')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Ada pengajuan dana baru yang menunggu persetujuan Anda:")
            ->line("Referensi: {$fundRequest->reference}")
            ->line("Judul: {$fundRequest->title}")
            ->line('Jumlah: Rp ' . number_format((float) $fundRequest->amount, 0, ',', '.'))
            ->line('Pengaju: ' . ($fundRequest->requester->name ?? '-'))
            ->action('Lihat & Proses Pengajuan', url('/fund-approvals/inbox'))
            ->line('Mohon segera diproses agar alur pencairan dana tidak tertunda.');
    }

    public function toArray(object $notifiable): array
    {
        $fundRequest = $this->approval->fundRequest;

        return [
            'type'             => 'fund_request_needs_approval',
            'fund_request_id'  => $fundRequest->id,
            'reference'        => $fundRequest->reference,
            'title'            => $fundRequest->title,
            'amount'           => $fundRequest->amount,
            'requester_name'   => $fundRequest->requester->name ?? '-',
            'url'              => '/fund-approvals/inbox',
        ];
    }
}
