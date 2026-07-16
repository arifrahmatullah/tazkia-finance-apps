<?php

namespace App\Http\Controllers;

use App\Models\FundRefund;
use App\Models\FundRequest;
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

        return view('dashboard', compact('stafStats'));
    }
}
