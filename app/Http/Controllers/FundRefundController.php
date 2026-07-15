<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FundRefund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FundRefundController extends Controller
{
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
}
