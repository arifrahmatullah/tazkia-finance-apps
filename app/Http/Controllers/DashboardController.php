<?php

namespace App\Http\Controllers;

use App\Models\FundRefund;
use App\Models\FundRequest;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $employee = $user->employee;

        // Ringkasan personal untuk staf pengaju
        $stafStats = null;
        if ($employee && $user->hasPermission('menu.pengajuan-dana')) {
            $base = FundRequest::where('requester_id', $employee->id);

            $refundBase = FundRefund::whereHas('fundRequest',
                fn($q) => $q->where('requester_id', $employee->id));

            $stafStats = [
                // Semua pengajuan miliknya
                'total_pengajuan' => (clone $base)->count(),
                'sedang_proses'   => (clone $base)->where('status', 'pending')->count(),

                // Sudah cair dan laporannya sudah dikirim (menunggu verifikasi / disetujui)
                'sudah_laporan' => (clone $base)->whereNotNull('disbursed_at')
                    ->whereHas('fundReports', fn($q) => $q->whereIn('status', ['waiting', 'approved']))
                    ->count(),

                // Sudah cair tapi belum ada laporan (jenis pembayaran tidak butuh laporan)
                'belum_laporan' => (clone $base)->whereNotNull('disbursed_at')
                    ->whereDoesntHave('fundReports', fn($q) => $q->whereIn('status', ['waiting', 'approved']))
                    ->whereDoesntHave('budgetProgram', fn($p) => $p->where('type', 'pembayaran'))
                    ->count(),

                // Tagihan pengembalian sisa dana
                'refund_total'   => (clone $refundBase)->count(),
                'refund_pending' => (clone $refundBase)->where('status', 'pending')->count(),

                // Selesai (closed): sudah cair, kewajiban laporan tuntas, tidak ada refund yang menggantung
                'closed' => (clone $base)->whereNotNull('disbursed_at')
                    ->where(function ($q) {
                        $q->whereHas('budgetProgram', fn($p) => $p->where('type', 'pembayaran'))
                          ->orWhereHas('fundReports', fn($r) => $r->where('status', 'approved'));
                    })
                    ->whereDoesntHave('fundRefunds', fn($r) => $r->where('status', '!=', 'confirmed'))
                    ->count(),
            ];
        }

        // Ringkasan Laba Rugi tahun berjalan -- khusus role Akunting dan Superadmin (Keuangan
        // tidak ditampilkan, meski punya izin laporan akuntansi).
        $labaRugi = null;
        if (($user->isSuperAdmin() || $user->hasRole('akunting')) && $user->hasPermission('menu.laporan-akuntansi')) {
            $orgIds  = $user->organizationIds();
            $from    = now()->startOfYear()->toDateString();
            $to      = now()->toDateString();

            $lines = fn ($type, $column) => JournalEntryLine::whereHas('account', fn ($q) => $q->where('account_type', $type))
                ->whereHas('journalEntry', function ($q) use ($orgIds, $from, $to) {
                    $q->where('status', 'posted')
                        ->whereDate('entry_date', '>=', $from)
                        ->whereDate('entry_date', '<=', $to)
                        ->when($orgIds !== null, fn ($qq) => $qq->whereIn('organization_id', $orgIds));
                })
                ->sum($column);

            $pendapatan = (float) $lines('pendapatan', 'credit');
            $beban      = (float) $lines('beban', 'debit');

            $labaRugi = [
                'pendapatan' => $pendapatan,
                'beban'      => $beban,
                'laba'       => $pendapatan - $beban,
                'tahun'      => now()->year,
            ];

            // Beban per bulan (Jan s.d. bulan berjalan) buat grafik tren -- pola query sama
            // dengan $lines di atas, tinggal disaring per bulan satu-satu.
            $monthlyBeban = [];
            for ($m = 1; $m <= now()->month; $m++) {
                $monthlyBeban[$m] = (float) JournalEntryLine::whereHas('account', fn ($q) => $q->where('account_type', 'beban'))
                    ->whereHas('journalEntry', function ($q) use ($orgIds, $m) {
                        $q->where('status', 'posted')
                            ->whereYear('entry_date', now()->year)
                            ->whereMonth('entry_date', $m)
                            ->when($orgIds !== null, fn ($qq) => $qq->whereIn('organization_id', $orgIds));
                    })
                    ->sum('debit');
            }
            $labaRugi['monthlyBeban'] = $monthlyBeban;
        }

        $hour     = now()->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));

        return view('dashboard', compact('stafStats', 'labaRugi', 'greeting'));
    }
}
