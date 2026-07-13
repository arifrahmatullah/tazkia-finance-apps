<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FundReport;
use App\Models\FundRequest;
use App\Models\FundRequestFile;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        $query = FundRequest::with([
            'organization', 'department', 'requester', 'requesterPosition',
            'budgetProgram', 'disburseAccount', 'disbursementProofs',
        ])
        ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
        ->whereIn('status', ['approved', 'disbursed'])
        ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
        ->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === 'disbursed') {
                $q->whereNotNull('disbursed_at');
            } elseif ($request->status === 'approved') {
                $q->where('status', 'approved')->whereNull('disbursed_at');
            }
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $s = '%' . $request->search . '%';
            $q->where(fn($sq) => $sq->where('reference', 'like', $s)->orWhere('title', 'like', $s));
        });

        $fundRequests = $query->orderByDesc('approved_at')->paginate(15)->withQueryString();

        // Akun bank dari COA: semua akun dengan kode di bawah 1.1.01.01 (REKENING BANK)
        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $filterStatus = $request->get('status', '');

        return view('finance.index', compact('fundRequests', 'organizations', 'filterStatus', 'bankAccounts'));
    }

    public function disburse(Request $request, FundRequest $fundRequest)
    {
        abort_unless($fundRequest->status === 'approved', 422, 'Hanya pengajuan yang sudah disetujui dapat dicairkan.');
        abort_unless(is_null($fundRequest->disbursed_at), 422, 'Pengajuan ini sudah dicairkan sebelumnya.');

        $request->validate([
            'disburse_account_id' => 'required|exists:accounts,id',
            'disbursement_notes'  => 'nullable|string|max:500',
        ]);

        $account = Account::findOrFail($request->disburse_account_id);
        $user    = auth()->user();

        $fundRequest->update([
            'disbursed_at'        => now(),
            'disburse_account_id' => $account->id,
            'disbursement_notes'  => $request->disbursement_notes,
            'disbursed_by'        => $user->name ?? $user->email,
        ]);

        return redirect()->route('finance.index')
            ->with('success', 'Pengajuan ' . $fundRequest->reference . ' berhasil dicairkan via ' . $account->name . '.');
    }

    public function uploadProof(Request $request, FundRequest $fundRequest)
    {
        abort_unless($fundRequest->isDisbursed(), 422, 'Pengajuan belum dicairkan.');

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $user = auth()->user();
        $file = $request->file('file');
        $path = $file->store('fund-requests/' . $fundRequest->id . '/proofs', 'public');

        $fundRequest->files()->create([
            'uploaded_by' => $user->id,
            'type'        => 'disbursement_proof',
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
        ]);

        return back()->with('success', 'Bukti pencairan berhasil diunggah.');
    }

    public function deleteProof(FundRequestFile $fundRequestFile)
    {
        abort_unless($fundRequestFile->type === 'disbursement_proof', 403);

        Storage::disk('public')->delete($fundRequestFile->file_path);
        $fundRequestFile->delete();

        return back()->with('success', 'Bukti pencairan berhasil dihapus.');
    }

    public function laporanIndex(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $reports = FundReport::with(['fundRequest.organization', 'fundRequest.department', 'reporter', 'files'])
            ->whereHas('fundRequest', function ($q) use ($orgIds) {
                $q->when($orgIds !== null, fn($sq) => $sq->whereIn('organization_id', $orgIds));
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->whereHas('fundRequest', fn($sq) => $sq->where('reference', 'like', $s)->orWhere('title', 'like', $s));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('finance.laporan', compact('reports'));
    }

    public function approveReport(Request $request, FundReport $fundReport)
    {
        abort_unless($fundReport->isWaiting(), 422, 'Laporan sudah diproses sebelumnya.');

        $fundReport->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('review_notes'),
        ]);

        return back()->with('success', 'Laporan berhasil disetujui.');
    }

    public function rejectReport(Request $request, FundReport $fundReport)
    {
        abort_unless($fundReport->isWaiting(), 422, 'Laporan sudah diproses sebelumnya.');

        $request->validate([
            'review_notes' => 'required|string|max:1000',
        ], ['review_notes.required' => 'Catatan penolakan wajib diisi.']);

        $fundReport->update([
            'status'       => 'rejected',
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->review_notes,
        ]);

        return back()->with('success', 'Laporan ditolak.');
    }
}
