<?php

namespace App\Http\Controllers;

use App\Models\BudgetProgram;
use App\Models\FundRequest;
use App\Notifications\FundReportRequired;
use App\Services\FundJournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Keuangan mengecek program berjenis "pembayaran" yang dananya sudah cair: memang pembayaran
// (tidak perlu laporan) atau sebenarnya kegiatan/pengadaan (wajib laporan).
class PaymentVerificationController extends Controller
{
    public function __construct(private FundJournalService $journal)
    {
    }

    private function scopedPrograms()
    {
        $orgIds = auth()->user()->organizationIds();

        return BudgetProgram::where('type', 'pembayaran')
            ->whereHas('fundRequests', fn($q) => $q->whereNotNull('disbursed_at')->whereNotIn('status', FundRequest::VOID_STATUSES))
            ->when($orgIds !== null, fn($q) => $q->whereHas('budgetAllocation.department', fn($d) => $d->whereIn('organization_id', $orgIds)));
    }

    public function index(Request $request)
    {
        // Default: yang belum dicek.
        $tab = $request->get('tab', 'belum') === 'sudah' ? 'sudah' : 'belum';

        $disbursed = fn($q) => $q->whereNotNull('disbursed_at')->whereNotIn('status', FundRequest::VOID_STATUSES);

        $belumQuery = $this->scopedPrograms()->whereNull('payment_verified_at');
        $sudahQuery = $this->scopedPrograms()->whereNotNull('payment_verified_at');

        $belumCount = (clone $belumQuery)->count();
        $sudahCount = (clone $sudahQuery)->count();
        $belumRequests = $disbursed(FundRequest::whereIn('budget_program_id', (clone $belumQuery)->pluck('id')));
        $belumReqCount = (clone $belumRequests)->count();
        $belumReqTotal = (float) (clone $belumRequests)->sum('amount');

        $programs = ($tab === 'sudah' ? $sudahQuery : $belumQuery)
            ->with([
                'budgetAllocation.department.organization',
                'account',
                'fundRequests' => fn($q) => $disbursed($q)->with(['requester', 'department', 'disbursementProofs'])->orderBy('disbursed_at'),
            ])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('finance.verifikasi-pembayaran', compact(
            'programs', 'tab', 'belumCount', 'sudahCount', 'belumReqCount', 'belumReqTotal'
        ));
    }

    public function verify(BudgetProgram $budgetProgram)
    {
        $this->authorizeProgram($budgetProgram);

        $budgetProgram->update([
            'payment_verified_at' => now(),
            'payment_verified_by' => auth()->user()->name ?? auth()->user()->email,
        ]);

        return back()->with('success', 'Program "' . $budgetProgram->name . '" dinyatakan memang Pembayaran.');
    }

    public function changeType(Request $request, BudgetProgram $budgetProgram)
    {
        $this->authorizeProgram($budgetProgram);

        $data = $request->validate([
            'type' => 'required|in:kegiatan,pengadaan',
        ]);
        $newType = $data['type'];

        $affected = $budgetProgram->fundRequests()
            ->whereNotNull('disbursed_at')
            ->whereNotIn('status', FundRequest::VOID_STATUSES)
            ->with('requester.user')
            ->get();

        // Cek semua akun Uang Muka dulu -- kalau ada yang kurang, batal total (tidak ada yang berubah).
        foreach ($affected->pluck('organization_id')->unique() as $orgId) {
            if (!$this->journal->hasAdvanceAccount($orgId, $newType)) {
                return back()->withErrors(['type' => 'Akun Uang Muka untuk jenis "' . $newType . '" belum ada di COA salah satu organisasi terkait. Tambahkan akunnya dulu, lalu ulangi.']);
            }
        }

        $user = auth()->user();
        $warnings = [];
        $corrected = 0;
        $correctedTotal = 0.0;
        $dueAt = now()->addDays(FundRequest::REPORT_DEADLINE_DAYS);

        DB::transaction(function () use ($budgetProgram, $newType, $affected, $user, $dueAt, &$warnings, &$corrected, &$correctedTotal) {
            $budgetProgram->update([
                'type'                => $newType,
                'payment_verified_at' => null,
                'payment_verified_by' => null,
            ]);

            foreach ($affected as $fr) {
                [$entry, $warning] = $this->journal->correctPaymentToAdvance($fr, $newType, $user);
                if ($entry) {
                    $corrected++;
                    $correctedTotal += (float) $entry->lines()->sum('debit');
                }
                if ($warning) {
                    $warnings[] = $warning;
                }
                // 14 hari dihitung dari sekarang -- pengaju baru tahu sekarang, bukan dari tanggal cair.
                $fr->update(['report_due_at' => $dueAt]);
            }
        });

        foreach ($affected as $fr) {
            $requesterUser = $fr->requester?->user;
            if ($requesterUser) {
                $requesterUser->notify(new FundReportRequired($fr->fresh(), $newType));
            }
        }

        $message = 'Jenis program "' . $budgetProgram->name . '" diubah menjadi ' . $newType . '. ' .
            $affected->count() . ' pengajuan cair kini wajib laporan (batas ' . $dueAt->translatedFormat('d F Y') . ')';
        if ($corrected > 0) {
            $message .= ', ' . $corrected . ' jurnal koreksi diposting (Rp ' . number_format($correctedTotal, 0, ',', '.') . ')';
        }
        $message .= '. Pengaju sudah diberi notifikasi.';

        return back()->with('success', $message)->with('warning', $warnings ? implode(' ', $warnings) : null);
    }

    private function authorizeProgram(BudgetProgram $program): void
    {
        $program->loadMissing('budgetAllocation.department');
        abort_unless(
            auth()->user()->canAccessOrganization($program->budgetAllocation->department->organization_id),
            403
        );
        abort_unless($program->type === 'pembayaran', 422, 'Program ini bukan berjenis pembayaran.');
    }
}
