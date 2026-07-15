<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\FundRefund;
use App\Models\FundReport;
use App\Models\FundRequest;
use App\Models\FundRequestFile;
use App\Models\Organization;
use App\Services\FundJournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    public function __construct(private FundJournalService $journal)
    {
    }

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

        $fundRequests = $query->orderByDesc('approved_at')->paginate(10)->withQueryString();

        // Pencairan yang sudah cair tapi belum ada bukti transfer (pengingat untuk keuangan)
        $missingProofCount = FundRequest::when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->whereNotNull('disbursed_at')
            ->whereDoesntHave('disbursementProofs')
            ->count();

        // Akun bank dari COA: semua akun dengan kode di bawah 1.1.01.01 (REKENING BANK)
        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $filterStatus = $request->get('status', '');

        return view('finance.index', compact('fundRequests', 'organizations', 'filterStatus', 'bankAccounts', 'missingProofCount'));
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

        [$entry, $warning] = $this->journal->postDisbursement($fundRequest, $user);

        $message = 'Pengajuan ' . $fundRequest->reference . ' berhasil dicairkan via ' . $account->name . '.';
        if ($entry) {
            $message .= ' Jurnal ' . $entry->reference . ' diposting.';
        }

        return redirect()->route('finance.index')
            ->with('success', $message)
            ->with('warning', $warning);
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

        $fundRequest = $fundReport->fundRequest;
        $sisa = (float) $fundRequest->amount - (float) $fundReport->amount_used;

        DB::transaction(function () use ($request, $fundReport, $fundRequest, $sisa) {
            $fundReport->update([
                'status'       => 'approved',
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'review_notes' => $request->input('review_notes'),
            ]);

            // Sisa dana yang tidak terpakai wajib dikembalikan oleh pengaju
            if ($sisa > 0) {
                FundRefund::firstOrCreate(
                    ['fund_report_id' => $fundReport->id],
                    [
                        'fund_request_id' => $fundRequest->id,
                        'amount'          => $sisa,
                        'status'          => 'pending',
                    ]
                );
            }
        });

        [$entry, $warning] = $this->journal->postReportApproval($fundReport, auth()->user());

        $message = 'Laporan berhasil disetujui.';
        if ($sisa > 0) {
            $message .= ' Tagihan pengembalian dana Rp ' . number_format($sisa, 0, ',', '.') . ' dibuat untuk pengaju.';
        }
        if ($entry) {
            $message .= ' Jurnal ' . $entry->reference . ' diposting.';
        }

        return back()->with('success', $message)->with('warning', $warning);
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

    public function pengembalianIndex(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $refunds = FundRefund::with([
            'fundRequest.department', 'fundRequest.requester',
            'fundReport', 'payer', 'refundAccount',
        ])
            ->whereHas('fundRequest', function ($q) use ($orgIds) {
                $q->when($orgIds !== null, fn($sq) => $sq->whereIn('organization_id', $orgIds));
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->whereHas('fundRequest', fn($sq) => $sq->where('reference', 'like', $s)->orWhere('title', 'like', $s));
            })
            ->orderByRaw("FIELD(status, 'waiting', 'pending', 'confirmed')")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('finance.pengembalian', compact('refunds'));
    }

    public function confirmRefund(Request $request, FundRefund $fundRefund)
    {
        abort_unless($fundRefund->isWaiting(), 422, 'Pengembalian ini belum dibayar atau sudah dikonfirmasi.');

        $fundRefund->update([
            'status'             => 'confirmed',
            'confirmed_by'       => auth()->id(),
            'confirmed_at'       => now(),
            'confirmation_notes' => $request->input('confirmation_notes'),
        ]);

        [$entry, $warning] = $this->journal->postRefundConfirmation($fundRefund, auth()->user());

        $message = 'Pengembalian dana ' . $fundRefund->fundRequest->reference . ' dikonfirmasi diterima.';
        if ($entry) {
            $message .= ' Jurnal ' . $entry->reference . ' diposting.';
        }

        return back()->with('success', $message)->with('warning', $warning);
    }

    public function rejectRefund(Request $request, FundRefund $fundRefund)
    {
        abort_unless($fundRefund->isWaiting(), 422, 'Pengembalian ini belum dibayar atau sudah dikonfirmasi.');

        $request->validate([
            'confirmation_notes' => 'required|string|max:1000',
        ], ['confirmation_notes.required' => 'Alasan penolakan wajib diisi.']);

        // Kembalikan ke pending agar pengaju bisa kirim ulang bukti
        $fundRefund->update([
            'status'             => 'pending',
            'confirmed_by'       => auth()->id(),
            'confirmed_at'       => now(),
            'confirmation_notes' => $request->confirmation_notes,
        ]);

        return back()->with('success', 'Bukti pengembalian ditolak, pengaju diminta mengirim ulang.');
    }
}
