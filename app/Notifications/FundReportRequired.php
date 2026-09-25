<?php

namespace App\Notifications;

use App\Models\FundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Dikirim ke pengaju saat Keuangan mengubah jenis program dari "pembayaran" jadi kegiatan/pengadaan
// setelah dana cair -- pengajuannya jadi wajib laporan penggunaan dana.
class FundReportRequired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FundRequest $fundRequest, public string $newType)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    private function dueLabel(): string
    {
        return $this->fundRequest->reportDueAt()?->translatedFormat('d F Y') ?? '-';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Dana Anda Wajib Dilaporkan')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Keuangan mengubah jenis program pengajuan dana berikut menjadi "' . $this->newType . '", sehingga sekarang WAJIB dibuatkan laporan penggunaan dana:')
            ->line("Referensi: {$this->fundRequest->reference}")
            ->line("Judul: {$this->fundRequest->title}")
            ->line('Jumlah: Rp ' . number_format((float) $this->fundRequest->amount, 0, ',', '.'))
            ->line('Batas waktu lapor: ' . $this->dueLabel())
            ->line('Pengajuan dana baru tidak dapat dibuat selama masih ada laporan yang melewati batas waktu.')
            ->action('Buat Laporan', url('/fund-reports/create?fund_request=' . $this->fundRequest->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'fund_report_required',
            'fund_request_id' => $this->fundRequest->id,
            'reference'       => $this->fundRequest->reference,
            'title'           => $this->fundRequest->title,
            'new_type'        => $this->newType,
            'due_at'          => $this->fundRequest->reportDueAt()?->toDateString(),
            'url'             => '/fund-reports/create?fund_request=' . $this->fundRequest->id,
        ];
    }
}
