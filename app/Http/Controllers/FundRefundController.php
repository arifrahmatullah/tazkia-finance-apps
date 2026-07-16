<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FundRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FundRefundController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        $base = FundRefund::whereHas('fundRequest', function ($q) use ($employee) {
            $q->where('requester_id', $employee?->id ?? '');
        });

        $summary = [
            'pending'        => (clone $base)->where('status', 'pending')->count(),
            'waiting'        => (clone $base)->where('status', 'waiting')->count(),
            'confirmed'      => (clone $base)->where('status', 'confirmed')->count(),
            'pending_amount' => (float) (clone $base)->where('status', 'pending')->sum('amount'),
        ];

        $refunds = (clone $base)
            ->with(['fundRequest.department', 'fundReport', 'refundAccount'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->whereHas('fundRequest', fn($sq) => $sq->where('reference', 'like', $s)->orWhere('title', 'like', $s));
            })
            ->orderByRaw("FIELD(status, 'pending', 'waiting', 'confirmed')")
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Rekening tujuan transfer untuk form pengembalian sekaligus
        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('fund-refunds.index', compact('refunds', 'summary', 'bankAccounts'));
    }

    public function show(FundRefund $fundRefund)
    {
        $user = Auth::user();
        $fundRefund->load(['fundRequest.department', 'fundReport', 'refundAccount', 'payer', 'confirmer']);

        $isRequester = $fundRefund->fundRequest->requester_id === $user->employee?->id;
        $canView = $isRequester || $user->hasPermission('menu.pencairan-dana');
        if (!$canView) abort(403);

        // Rekening tujuan transfer pengembalian (sama dengan rekening pencairan)
        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('fund-refunds.show', compact('fundRefund', 'isRequester', 'bankAccounts'));
    }

    public function pay(Request $request, FundRefund $fundRefund)
    {
        $user = Auth::user();

        if ($fundRefund->fundRequest->requester_id !== $user->employee?->id) {
            abort(403, 'Hanya pengaju yang bisa mengembalikan dana.');
        }

        if (!$fundRefund->isPending()) {
            return back()->with('error', 'Pengembalian ini sudah diproses.');
        }

        $validated = $request->validate([
            'refund_account_id' => 'required|exists:accounts,id',
            'payment_notes'     => 'nullable|string|max:1000',
            'proof'             => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'proof.required' => 'Bukti transfer pengembalian wajib dilampirkan.',
            'proof.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        // Hapus bukti lama jika kirim ulang setelah ditolak
        if ($fundRefund->proof_path) {
            Storage::disk('public')->delete($fundRefund->proof_path);
        }

        $file = $request->file('proof');
        $path = $file->store("fund-refunds/{$fundRefund->id}", 'public');

        $fundRefund->update([
            'status'            => 'waiting',
            'paid_by'           => $user->id,
            'paid_at'           => now(),
            'refund_account_id' => $validated['refund_account_id'],
            'payment_notes'     => $validated['payment_notes'] ?? null,
            'proof_path'        => $path,
            'proof_name'        => $file->getClientOriginalName(),
        ]);

        return redirect()->route('fund-refunds.show', $fundRefund)
            ->with('success', 'Bukti pengembalian dana terkirim, menunggu konfirmasi keuangan.');
    }

    public function payBulk(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'refund_ids'        => 'required|array|min:1',
            'refund_ids.*'      => 'exists:fund_refunds,id',
            'refund_account_id' => 'required|exists:accounts,id',
            'payment_notes'     => 'nullable|string|max:1000',
            'proof'             => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'refund_ids.required' => 'Pilih minimal satu pengembalian dana.',
            'proof.required'      => 'Bukti transfer pengembalian wajib dilampirkan.',
            'proof.max'           => 'Ukuran file maksimal 10 MB.',
        ]);

        $refunds = FundRefund::with('fundRequest')
            ->whereIn('id', $validated['refund_ids'])
            ->get();

        foreach ($refunds as $refund) {
            if ($refund->fundRequest->requester_id !== $user->employee?->id) {
                abort(403, 'Hanya pengaju yang bisa mengembalikan dana.');
            }
            if (!$refund->isPending()) {
                return back()->with('error', 'Pengembalian ' . $refund->fundRequest->reference . ' sudah diproses, silakan pilih ulang.');
            }
        }

        $file  = $request->file('proof');
        $total = 0;

        DB::transaction(function () use ($refunds, $validated, $user, $file, &$total) {
            foreach ($refunds as $refund) {
                // Hapus bukti lama jika kirim ulang setelah ditolak
                if ($refund->proof_path) {
                    Storage::disk('public')->delete($refund->proof_path);
                }

                // Simpan salinan bukti per pengembalian agar aman dihapus/dikirim ulang satu per satu
                $path = $file->store("fund-refunds/{$refund->id}", 'public');

                $refund->update([
                    'status'            => 'waiting',
                    'paid_by'           => $user->id,
                    'paid_at'           => now(),
                    'refund_account_id' => $validated['refund_account_id'],
                    'payment_notes'     => $validated['payment_notes'] ?? null,
                    'proof_path'        => $path,
                    'proof_name'        => $file->getClientOriginalName(),
                ]);

                $total += (float) $refund->amount;
            }
        });

        return redirect()->route('fund-refunds.index')
            ->with('success', $refunds->count() . ' pengembalian dana (total Rp ' . number_format($total, 0, ',', '.') . ') terkirim, menunggu konfirmasi keuangan.');
    }
}
