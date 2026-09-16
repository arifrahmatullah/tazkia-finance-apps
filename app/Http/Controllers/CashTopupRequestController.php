<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashTopupRequest;
use App\Models\FundRequest;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganizationRole;
use App\Notifications\CashTopupRequestDirectFunded;
use App\Notifications\CashTopupRequestStatusChanged;
use App\Notifications\CashTopupRequestSubmitted;
use App\Services\FundJournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CashTopupRequestController extends Controller
{
    public function __construct(private FundJournalService $journal)
    {
    }

    public function create(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $organizations = Organization::whereNotNull('parent_id')->with('parent')
            ->when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get();

        abort_if($organizations->isEmpty(), 403, 'Organisasi Anda tidak memiliki organisasi induk (Yayasan), tidak bisa mengajukan saldo.');

        $organizationId = $request->get('organization_id', $organizations->first()->id);
        $organization   = $organizations->firstWhere('id', $organizationId);
        abort_unless($organization, 403);

        $targetAccounts = Account::where('organization_id', $organizationId)
            ->where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)->where('is_header', false)
            ->orderBy('code')->get(['id', 'code', 'name'])
            ->each(fn($a) => $a->balance = $a->currentBalance());

        $autoContraAccount = $this->detectContraAccount($organization);

        // Kalau tidak ketemu akun hutang antar-entitas yang jelas, tampilkan pilihan
        // manual -- dipersempit ke akun KEWAJIBAN saja (bukan seluruh COA) supaya
        // Keuangan yang bukan latar belakang akunting tidak bingung pilih dari semua tipe akun.
        $ledgerAccounts = $autoContraAccount ? collect() : Account::where('organization_id', $organizationId)
            ->where('account_type', 'kewajiban')
            ->where('is_active', true)->where('is_header', false)
            ->orderBy('code')->get(['id', 'code', 'name']);

        $candidateFundRequests = FundRequest::with(['department', 'budgetProgram', 'cashTopupRequests' => function ($q) {
                $q->orderByDesc('created_at');
            }])
            ->where('organization_id', $organizationId)
            ->where('status', 'approved')
            ->whereNull('disbursed_at')
            ->orderByDesc('approved_at')
            ->get();

        return view('cash-topup-requests.create', compact(
            'organizations', 'organizationId', 'targetAccounts', 'ledgerAccounts', 'autoContraAccount', 'candidateFundRequests'
        ));
    }

    // Cari otomatis akun kewajiban "Hutang Antar Entitas" milik organisasi yang namanya
    // menyebut organisasi induknya (mis. "Hutang ke Yayasan Tazkia Cendekia" untuk Kampus
    // yang induknya "Yayasan Tazkia") -- supaya Keuangan tidak perlu pilih akun akunting
    // sendiri. Kalau ketemu tidak persis satu, biarkan caller jatuh balik ke dropdown manual.
    private function detectContraAccount(Organization $organization): ?Account
    {
        if (!$organization->parent) {
            return null;
        }

        $matches = Account::where('organization_id', $organization->id)
            ->where('account_type', 'kewajiban')
            ->where('is_header', false)
            ->where('is_active', true)
            ->where('name', 'LIKE', '%' . $organization->parent->name . '%')
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    // User Keuangan yang perlu dinotifikasi buat organisasi tertentu: superadmin, atau
    // siapapun yang punya permission menu.pencairan-dana lewat role yang di-assign ke
    // organisasi itu (atau role tanpa organisasi = akses semua organisasi). Dipakai untuk
    // dua arah: notifikasi ke Yayasan saat Kampus mengajukan, DAN notifikasi ke Kampus saat
    // Yayasan mengisi saldo langsung.
    private function financeUsersFor(string $organizationId): Collection
    {
        $roleIds = Role::where('slug', 'superadmin')
            ->orWhereHas('permissions', fn($q) => $q->where('slug', 'menu.pencairan-dana'))
            ->pluck('id');

        $userIds = UserOrganizationRole::whereIn('role_id', $roleIds)
            ->where(function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)->orWhereNull('organization_id');
            })
            ->pluck('user_id')
            ->unique();

        return User::whereIn('id', $userIds)->where('is_active', true)->get();
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'organization_id'          => 'required|exists:organizations,id',
            'target_account_id'        => 'required|exists:accounts,id',
            'source_credit_account_id' => 'nullable|exists:accounts,id|different:target_account_id',
            'amount'                   => 'required|numeric|min:1',
            'notes'                    => 'nullable|string|max:1000',
            'fund_request_ids'         => 'nullable|array',
            'fund_request_ids.*'       => 'exists:fund_requests,id',
        ]);

        abort_unless($user->canAccessOrganization($data['organization_id']), 403);

        $organization = Organization::with('parent')->findOrFail($data['organization_id']);
        abort_unless($organization->parent_id, 422, 'Organisasi ini tidak memiliki organisasi induk (Yayasan).');

        $targetAccount = Account::where('id', $data['target_account_id'])
            ->where('organization_id', $organization->id)->firstOrFail();

        // Utamakan hasil auto-detect server-side (bukan percaya input client) -- kalau tidak
        // ketemu, baru pakai akun yang dipilih manual dari dropdown (fallback).
        $creditAccount = $this->detectContraAccount($organization);
        if (!$creditAccount) {
            abort_unless(!empty($data['source_credit_account_id']), 422, 'Akun lawan wajib dipilih.');
            $creditAccount = Account::where('id', $data['source_credit_account_id'])
                ->where('organization_id', $organization->id)
                ->where('account_type', 'kewajiban')
                ->firstOrFail();
        }

        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun ini belum terhubung dengan data karyawan.');

        $topup = DB::transaction(function () use ($data, $organization, $targetAccount, $creditAccount, $employee) {
            $date = now()->toDateString();

            $topup = CashTopupRequest::create([
                'requesting_organization_id' => $organization->id,
                'target_account_id'          => $targetAccount->id,
                'source_credit_account_id'   => $creditAccount->id,
                'requested_by'               => $employee->id,
                'reference'                  => CashTopupRequest::generateReference($organization->id, $date),
                'amount'                     => $data['amount'],
                'notes'                      => $data['notes'] ?? null,
                'status'                     => 'pending',
            ]);

            if (!empty($data['fund_request_ids'])) {
                $topup->fundRequests()->attach(
                    FundRequest::where('organization_id', $organization->id)
                        ->whereIn('id', $data['fund_request_ids'])
                        ->pluck('id')
                );
            }

            return $topup;
        });

        $this->financeUsersFor($organization->parent_id)
            ->each(fn($approver) => $approver->notify(new CashTopupRequestSubmitted($topup)));

        return redirect()->route('cash-topup-requests.show', $topup)
            ->with('success', 'Pengajuan saldo ' . $topup->reference . ' berhasil dikirim ke Yayasan.');
    }

    // Kebalikan dari create()/store(): Yayasan langsung mengisi saldo Kampus/STMIK tanpa
    // menunggu pengajuan -- satu form, langsung efektif (status 'approved' sejak dibuat,
    // tidak lewat 'pending'), karena Yayasan yang menginisiasi sekaligus yang biasanya approve.
    public function directCreate(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $childOrgs = Organization::whereNotNull('parent_id')->with('parent')
            ->when($orgIds !== null, fn($q) => $q->whereIn('parent_id', $orgIds))
            ->orderBy('name')->get();

        abort_if($childOrgs->isEmpty(), 403, 'Anda tidak memiliki akses ke organisasi manapun yang bisa diisi saldo langsung.');

        $organizationId = $request->get('organization_id', $childOrgs->first()->id);
        $organization   = $childOrgs->firstWhere('id', $organizationId);
        abort_unless($organization, 403);

        $targetAccounts = Account::where('organization_id', $organizationId)
            ->where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)->where('is_header', false)
            ->orderBy('code')->get(['id', 'code', 'name'])
            ->each(fn($a) => $a->balance = $a->currentBalance());

        $autoContraAccount = $this->detectContraAccount($organization);

        $ledgerAccounts = $autoContraAccount ? collect() : Account::where('organization_id', $organizationId)
            ->where('account_type', 'kewajiban')
            ->where('is_active', true)->where('is_header', false)
            ->orderBy('code')->get(['id', 'code', 'name']);

        $yayasanAccounts = Account::where('organization_id', $organization->parent_id)
            ->where('is_active', true)->where('is_header', false)
            ->orderBy('code')->get(['id', 'code', 'name'])
            ->each(fn($a) => $a->balance = $a->currentBalance());

        return view('cash-topup-requests.direct-create', compact(
            'childOrgs', 'organizationId', 'targetAccounts', 'ledgerAccounts', 'autoContraAccount', 'yayasanAccounts'
        ));
    }

    public function directStore(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'organization_id'           => 'required|exists:organizations,id',
            'target_account_id'         => 'required|exists:accounts,id',
            'source_credit_account_id'  => 'nullable|exists:accounts,id|different:target_account_id',
            'amount'                    => 'required|numeric|min:1',
            'notes'                     => 'nullable|string|max:1000',
            'yayasan_source_account_id' => 'required|exists:accounts,id',
            'yayasan_debit_account_id'  => 'required|exists:accounts,id|different:yayasan_source_account_id',
            'proof'                     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $organization = Organization::with('parent')->findOrFail($data['organization_id']);
        abort_unless($organization->parent_id, 422, 'Organisasi ini tidak memiliki organisasi induk (Yayasan).');
        abort_unless($user->isSuperAdmin() || $user->canAccessOrganization($organization->parent_id), 403);

        $targetAccount = Account::where('id', $data['target_account_id'])
            ->where('organization_id', $organization->id)->firstOrFail();

        $creditAccount = $this->detectContraAccount($organization);
        if (!$creditAccount) {
            abort_unless(!empty($data['source_credit_account_id']), 422, 'Akun lawan sisi Kampus/STMIK wajib dipilih.');
            $creditAccount = Account::where('id', $data['source_credit_account_id'])
                ->where('organization_id', $organization->id)
                ->where('account_type', 'kewajiban')
                ->firstOrFail();
        }

        $sourceAccount = Account::where('id', $data['yayasan_source_account_id'])
            ->where('organization_id', $organization->parent_id)->firstOrFail();
        $debitAccount = Account::where('id', $data['yayasan_debit_account_id'])
            ->where('organization_id', $organization->parent_id)->firstOrFail();

        $balance = $sourceAccount->currentBalance();
        $amount  = (float) $data['amount'];
        if ($balance < $amount) {
            return back()->withErrors(['yayasan_source_account_id' =>
                'Saldo rekening ' . $sourceAccount->name . ' (Rp ' . number_format($balance, 0, ',', '.') .
                ') tidak cukup untuk mengisi Rp ' . number_format($amount, 0, ',', '.') . '.'])
                ->with('insufficientBalanceOrgId', $organization->parent_id);
        }

        $employee = $user->employee;
        abort_unless($employee, 403, 'Akun ini belum terhubung dengan data karyawan.');

        $proof = $request->file('proof');

        $topup = DB::transaction(function () use ($data, $organization, $targetAccount, $creditAccount, $sourceAccount, $debitAccount, $employee, $user, $amount, $proof) {
            $date = now()->toDateString();

            $topup = CashTopupRequest::create([
                'requesting_organization_id' => $organization->id,
                'origin'                     => 'yayasan_initiated',
                'target_account_id'          => $targetAccount->id,
                'source_credit_account_id'   => $creditAccount->id,
                'requested_by'               => $employee->id,
                'reference'                  => CashTopupRequest::generateReference($organization->id, $date),
                'amount'                     => $amount,
                'notes'                      => $data['notes'] ?? null,
                'status'                     => 'approved',
                'reviewed_by'                => $user->id,
                'reviewed_at'                => now(),
                'yayasan_source_account_id'  => $sourceAccount->id,
                'yayasan_debit_account_id'   => $debitAccount->id,
            ]);

            $path = $proof->store('cash-topup-requests/' . $topup->id, 'public');
            $topup->update(['proof_path' => $path, 'proof_name' => $proof->getClientOriginalName()]);

            return $topup;
        });

        [$childEntry, $yayasanEntry, $warning] = $this->journal->postCashTopupApproval($topup, $user);

        $this->financeUsersFor($organization->id)
            ->each(fn($recipient) => $recipient->notify(new CashTopupRequestDirectFunded($topup)));

        $message = 'Saldo rekening ' . $targetAccount->name . ' di ' . $organization->name . ' berhasil diisi langsung.';
        if ($childEntry && $yayasanEntry) {
            $message .= ' Jurnal ' . $childEntry->reference . ' & ' . $yayasanEntry->reference . ' diposting.';
        }

        return redirect()->route('cash-topup-requests.show', $topup)
            ->with('success', $message)->with('warning', $warning);
    }

    public function index(Request $request)
    {
        $user   = auth()->user();
        $orgIds = $user->organizationIds();

        $topups = CashTopupRequest::with(['requestingOrganization', 'targetAccount', 'requestedBy'])
            ->when($orgIds !== null, function ($q) use ($orgIds) {
                $q->where(function ($sq) use ($orgIds) {
                    $sq->whereIn('requesting_organization_id', $orgIds)
                       ->orWhereHas('requestingOrganization', fn($oq) => $oq->whereIn('parent_id', $orgIds));
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->latest()
            ->paginate(15)->withQueryString();

        $canApprove = $orgIds === null
            || $user->isSuperAdmin()
            || Organization::whereIn('parent_id', $orgIds)->exists();

        $canRequest = $orgIds === null
            || Organization::whereNotNull('parent_id')->whereIn('id', $orgIds)->exists();

        $filterStatus = $request->get('status', '');

        return view('cash-topup-requests.index', compact('topups', 'canApprove', 'canRequest', 'filterStatus'));
    }

    public function show(CashTopupRequest $cashTopupRequest)
    {
        $user = auth()->user();
        $cashTopupRequest->load(['requestingOrganization.parent']);
        $requestingOrg = $cashTopupRequest->requestingOrganization;
        $parentId      = $requestingOrg->parent_id;

        $canView = $user->canAccessOrganization($requestingOrg->id)
            || ($parentId && $user->canAccessOrganization($parentId));
        abort_unless($canView, 403);

        $cashTopupRequest->load([
            'targetAccount', 'sourceCreditAccount', 'yayasanSourceAccount', 'yayasanDebitAccount',
            'requestedBy', 'reviewer', 'fundRequests.department', 'fundRequests.budgetProgram',
        ]);

        $canApprove = $cashTopupRequest->isPending()
            && $parentId
            && ($user->isSuperAdmin() || $user->canAccessOrganization($parentId));

        $yayasanAccounts = $canApprove
            ? Account::where('organization_id', $parentId)
                ->where('is_active', true)->where('is_header', false)
                ->orderBy('code')->get(['id', 'code', 'name'])
                ->each(fn($a) => $a->balance = $a->currentBalance())
            : collect();

        return view('cash-topup-requests.show', compact('cashTopupRequest', 'canApprove', 'yayasanAccounts'));
    }

    public function approve(Request $request, CashTopupRequest $cashTopupRequest)
    {
        if (!$cashTopupRequest->isPending()) {
            return redirect()->route('cash-topup-requests.show', $cashTopupRequest)
                ->withErrors(['general' => 'Pengajuan ini sudah diproses sebelumnya.']);
        }

        $user = auth()->user();
        $cashTopupRequest->loadMissing('requestingOrganization');
        $parentId = $cashTopupRequest->requestingOrganization->parent_id;

        abort_unless($parentId && ($user->isSuperAdmin() || $user->canAccessOrganization($parentId)), 403);

        $data = $request->validate([
            'yayasan_source_account_id' => 'required|exists:accounts,id',
            'yayasan_debit_account_id'  => 'required|exists:accounts,id|different:yayasan_source_account_id',
            'review_notes'              => 'nullable|string|max:1000',
            'proof'                     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $sourceAccount = Account::where('id', $data['yayasan_source_account_id'])
            ->where('organization_id', $parentId)->firstOrFail();
        $debitAccount = Account::where('id', $data['yayasan_debit_account_id'])
            ->where('organization_id', $parentId)->firstOrFail();

        $balance = $sourceAccount->currentBalance();
        $amount  = (float) $cashTopupRequest->amount;
        if ($balance < $amount) {
            return back()->withErrors(['yayasan_source_account_id' =>
                'Saldo rekening ' . $sourceAccount->name . ' (Rp ' . number_format($balance, 0, ',', '.') .
                ') tidak cukup untuk menyetujui Rp ' . number_format($amount, 0, ',', '.') . '.'])
                ->with('insufficientBalanceOrgId', $parentId);
        }

        $proof = $request->file('proof');
        $path  = $proof->store('cash-topup-requests/' . $cashTopupRequest->id, 'public');

        $cashTopupRequest->update([
            'status'                    => 'approved',
            'reviewed_by'               => $user->id,
            'reviewed_at'               => now(),
            'review_notes'              => $data['review_notes'] ?? null,
            'yayasan_source_account_id' => $sourceAccount->id,
            'yayasan_debit_account_id'  => $debitAccount->id,
            'proof_path'                => $path,
            'proof_name'                => $proof->getClientOriginalName(),
        ]);

        [$childEntry, $yayasanEntry, $warning] = $this->journal->postCashTopupApproval($cashTopupRequest, $user);

        $requesterUser = $cashTopupRequest->requestedBy?->user;
        if ($requesterUser) {
            $requesterUser->notify(new CashTopupRequestStatusChanged($cashTopupRequest->fresh()));
        }

        $message = 'Pengajuan saldo ' . $cashTopupRequest->reference . ' disetujui.';
        if ($childEntry && $yayasanEntry) {
            $message .= ' Jurnal ' . $childEntry->reference . ' & ' . $yayasanEntry->reference . ' diposting.';
        }

        return redirect()->route('cash-topup-requests.show', $cashTopupRequest)
            ->with('success', $message)->with('warning', $warning);
    }

    public function reject(Request $request, CashTopupRequest $cashTopupRequest)
    {
        if (!$cashTopupRequest->isPending()) {
            return redirect()->route('cash-topup-requests.show', $cashTopupRequest)
                ->withErrors(['general' => 'Pengajuan ini sudah diproses sebelumnya.']);
        }

        $user = auth()->user();
        $cashTopupRequest->loadMissing('requestingOrganization');
        $parentId = $cashTopupRequest->requestingOrganization->parent_id;

        abort_unless($parentId && ($user->isSuperAdmin() || $user->canAccessOrganization($parentId)), 403);

        $data = $request->validate([
            'review_notes' => 'required|string|max:1000',
        ], ['review_notes.required' => 'Alasan penolakan wajib diisi.']);

        $cashTopupRequest->update([
            'status'       => 'rejected',
            'reviewed_by'  => $user->id,
            'reviewed_at'  => now(),
            'review_notes' => $data['review_notes'],
        ]);

        $requesterUser = $cashTopupRequest->requestedBy?->user;
        if ($requesterUser) {
            $requesterUser->notify(new CashTopupRequestStatusChanged($cashTopupRequest->fresh(), $data['review_notes']));
        }

        return redirect()->route('cash-topup-requests.show', $cashTopupRequest)
            ->with('success', 'Pengajuan saldo ditolak.');
    }
}
