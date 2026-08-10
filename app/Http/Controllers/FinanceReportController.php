<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BudgetAllocation;
use App\Models\BudgetPeriod;
use App\Models\BudgetProgram;
use App\Models\FundRefund;
use App\Models\FundRequest;
use App\Models\JournalEntryLine;
use App\Models\Organization;
use Illuminate\Http\Request;

class FinanceReportController extends Controller
{
    // Laporan Pengajuan Dana — rekap seluruh pengajuan (non-draft) untuk keuangan/pimpinan
    public function fundRequests(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $orgIds = auth()->user()->organizationIds();

        $base = FundRequest::query()
            ->where('status', '!=', 'draft')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($dateFrom, fn($q) => $q->whereDate('submitted_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('submitted_at', '<=', $dateTo))
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('status'), function ($q) use ($request) {
                match ($request->status) {
                    'pending'   => $q->where('status', 'pending'),
                    'approved'  => $q->where('status', 'approved')->whereNull('disbursed_at'),
                    'disbursed' => $q->whereNotNull('disbursed_at'),
                    'rejected'  => $q->where('status', 'rejected'),
                    default     => null,
                };
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('reference', 'like', $s)
                    ->orWhere('title', 'like', $s)
                    ->orWhereHas('requester', fn($rq) => $rq->where('name', 'like', $s)));
            });

        $summary = (clone $base)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_amount,
            SUM(status = 'pending') as pending_count,
            SUM(status = 'approved' AND disbursed_at IS NULL) as approved_count,
            SUM(status = 'rejected') as rejected_count,
            SUM(disbursed_at IS NOT NULL) as disbursed_count,
            COALESCE(SUM(CASE WHEN disbursed_at IS NOT NULL THEN amount ELSE 0 END), 0) as disbursed_amount
        ")->first();

        $perOrg = $this->recapPerOrganization($base);

        $fundRequests = (clone $base)
            ->with(['organization', 'department', 'requester', 'budgetProgram'])
            ->orderByDesc('submitted_at')
            ->paginate(15)->withQueryString();

        $organizations = $this->organizationOptions($orgIds);

        return view('reports.fund-requests', compact(
            'fundRequests', 'summary', 'perOrg', 'organizations', 'dateFrom', 'dateTo'
        ));
    }

    // Laporan Pencairan Dana — rekap dana yang sudah dicairkan
    public function disbursements(Request $request)
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $orgIds = auth()->user()->organizationIds();

        $base = FundRequest::query()
            ->whereNotNull('disbursed_at')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->when($dateFrom, fn($q) => $q->whereDate('disbursed_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('disbursed_at', '<=', $dateTo))
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('account_id'), fn($q) => $q->where('disburse_account_id', $request->account_id))
            ->when($request->filled('proof'), function ($q) use ($request) {
                $request->proof === 'ada'
                    ? $q->whereHas('disbursementProofs')
                    : $q->whereDoesntHave('disbursementProofs');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%' . $request->search . '%';
                $q->where(fn($sq) => $sq->where('reference', 'like', $s)
                    ->orWhere('title', 'like', $s)
                    ->orWhereHas('requester', fn($rq) => $rq->where('name', 'like', $s)));
            });

        $summary = (clone $base)->selectRaw("
            COUNT(*) as total_count,
            COALESCE(SUM(amount), 0) as total_amount
        ")->first();

        $missingProofCount = (clone $base)->whereDoesntHave('disbursementProofs')->count();

        $perOrg = $this->recapPerOrganization($base);

        // Rekap per rekening sumber pencairan
        $perAccount = (clone $base)
            ->selectRaw('disburse_account_id, COUNT(*) as jumlah, COALESCE(SUM(amount), 0) as total')
            ->groupBy('disburse_account_id')
            ->orderByDesc('total')
            ->get();
        $accountNames = Account::whereIn('id', $perAccount->pluck('disburse_account_id')->filter())
            ->get(['id', 'code', 'name'])->keyBy('id');

        $fundRequests = (clone $base)
            ->with(['organization', 'requester', 'disburseAccount', 'disbursementProofs'])
            ->orderByDesc('disbursed_at')
            ->paginate(15)->withQueryString();

        $organizations = $this->organizationOptions($orgIds);

        $bankAccounts = Account::where('code', 'LIKE', '1.1.01.01.%')
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('reports.disbursements', compact(
            'fundRequests', 'summary', 'missingProofCount', 'perOrg', 'perAccount', 'accountNames',
            'organizations', 'bankAccounts', 'dateFrom', 'dateTo'
        ));
    }

    // Laporan Realisasi Anggaran — pagu & anggaran program kerja vs dana yang sudah dicairkan
    public function budgetRealization(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();

        $periods = BudgetPeriod::with('organization:id,name')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderByDesc('period_start')
            ->get();

        $period = $request->filled('budget_period_id')
            ? $periods->firstWhere('id', $request->budget_period_id)
            : null;
        $period ??= $periods->firstWhere('is_active', true) ?? $periods->first();

        if (!$period) {
            return view('reports.budget-realization', [
                'periods' => $periods, 'period' => null, 'groups' => collect(),
                'departments' => collect(), 'totals' => null,
            ]);
        }

        // Departemen yang punya alokasi pada periode ini (untuk opsi filter)
        $departments = BudgetAllocation::with('department:id,name')
            ->where('budget_period_id', $period->id)
            ->get()
            ->pluck('department')->filter()->unique('id')->sortBy('name')->values();

        $allocations = BudgetAllocation::with('department:id,name')
            ->where('budget_period_id', $period->id)
            ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id))
            ->get();

        $programs = BudgetProgram::whereIn('budget_allocation_id', $allocations->pluck('id'))
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->with('account:id,code,name')
            ->withSum('details as budget_total', 'total_amount')
            ->orderBy('name')
            ->get();

        $programIds = $programs->pluck('id');

        // Realisasi: pengajuan yang sudah dicairkan, per program
        $realized = FundRequest::whereIn('budget_program_id', $programIds)
            ->whereNotNull('disbursed_at')
            ->selectRaw('budget_program_id, COALESCE(SUM(amount), 0) as total, COUNT(*) as jumlah')
            ->groupBy('budget_program_id')
            ->get()->keyBy('budget_program_id');

        // Dana proses: pengajuan pending/approved yang belum dicairkan
        $inProcess = FundRequest::whereIn('budget_program_id', $programIds)
            ->whereNull('disbursed_at')
            ->whereIn('status', ['pending', 'approved'])
            ->selectRaw('budget_program_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('budget_program_id')
            ->get()->keyBy('budget_program_id');

        // Pengembalian dana terkonfirmasi mengurangi realisasi (realisasi netto)
        $refunded = FundRefund::where('fund_refunds.status', 'confirmed')
            ->join('fund_requests', 'fund_requests.id', '=', 'fund_refunds.fund_request_id')
            ->whereIn('fund_requests.budget_program_id', $programIds)
            ->selectRaw('fund_requests.budget_program_id as pid, COALESCE(SUM(fund_refunds.amount), 0) as total')
            ->groupBy('fund_requests.budget_program_id')
            ->get()->keyBy('pid');

        $groups = $allocations->map(function ($alloc) use ($programs, $realized, $inProcess, $refunded) {
            $rows = $programs->where('budget_allocation_id', $alloc->id)->values()->map(function ($p) use ($realized, $inProcess, $refunded) {
                $budget   = (float) ($p->budget_total ?? 0);
                $cair     = (float) ($realized[$p->id]->total ?? 0);
                $kembali  = (float) ($refunded[$p->id]->total ?? 0);
                $netto    = $cair - $kembali;

                return (object) [
                    'name'       => $p->name,
                    'type_label' => $p->type_label,
                    'account'    => $p->account,
                    'budget'     => $budget,
                    'disbursed'  => $cair,
                    'refunded'   => $kembali,
                    'realized'   => $netto,
                    'in_process' => (float) ($inProcess[$p->id]->total ?? 0),
                    'count'      => (int) ($realized[$p->id]->jumlah ?? 0),
                    'remaining'  => $budget - $netto,
                    'pct'        => $budget > 0 ? round($netto / $budget * 100, 1) : null,
                ];
            });

            return (object) [
                'department' => $alloc->department?->name ?? '-',
                'pagu'       => (float) $alloc->amount,
                'programs'   => $rows,
                'budget'     => $rows->sum('budget'),
                'realized'   => $rows->sum('realized'),
                'in_process' => $rows->sum('in_process'),
                'remaining'  => $rows->sum('budget') - $rows->sum('realized'),
            ];
        })
        // Saat mencari program, sembunyikan departemen tanpa hasil
        ->filter(fn($g) => !$request->filled('search') || $g->programs->isNotEmpty())
        ->sortBy('department')->values();

        $totals = (object) [
            'pagu'       => $groups->sum('pagu'),
            'budget'     => $groups->sum('budget'),
            'realized'   => $groups->sum('realized'),
            'in_process' => $groups->sum('in_process'),
            'remaining'  => $groups->sum('remaining'),
            'pct'        => $groups->sum('budget') > 0 ? round($groups->sum('realized') / $groups->sum('budget') * 100, 1) : null,
        ];

        return view('reports.budget-realization', compact('periods', 'period', 'groups', 'departments', 'totals'));
    }

    // Laporan Transaksi Departemen — rincian pengajuan dana satu departemen dalam satu periode, dikelompokkan per bulan
    public function departmentTransactions(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        [$periods, $period] = $this->resolvePeriod($request, $orgIds);

        $departments = collect();
        $department  = null;
        $months      = collect();
        $pagu        = 0.0;
        $totals      = null;

        if ($period) {
            $departments = BudgetAllocation::with('department:id,name')
                ->where('budget_period_id', $period->id)
                ->get()->pluck('department')->filter()->unique('id')->sortBy('name')->values();

            $department = $request->filled('department_id')
                ? $departments->firstWhere('id', $request->department_id)
                : $departments->first();
        }

        if ($period && $department) {
            $pagu = (float) BudgetAllocation::where('budget_period_id', $period->id)
                ->where('department_id', $department->id)->sum('amount');

            $rows = FundRequest::with(['requester', 'budgetProgram'])
                ->where('budget_period_id', $period->id)
                ->where('department_id', $department->id)
                ->where('status', '!=', 'draft')
                ->orderBy('submitted_at')
                ->get();

            $refundedByFr = $this->confirmedRefundsFor($rows->pluck('id'));

            $rows->each(function ($fr) use ($refundedByFr) {
                $fr->refunded = (float) ($refundedByFr[$fr->id] ?? 0);
                $fr->realized = $fr->disbursed_at ? ((float) $fr->amount - $fr->refunded) : 0.0;
            });

            $months = $rows->groupBy(fn($fr) => $fr->submitted_at?->format('Y-m') ?? '-')
                ->map(fn($group, $key) => (object) [
                    'label'     => $key === '-' ? 'Belum Disubmit' : \Carbon\Carbon::createFromFormat('Y-m', $key)->translatedFormat('F Y'),
                    'rows'      => $group->values(),
                    'diajukan'  => $group->sum('amount'),
                    'realisasi' => $group->sum('realized'),
                ])->values();

            $totalRealisasi = $months->sum('realisasi');
            $totals = (object) [
                'diajukan'  => $rows->sum('amount'),
                'realisasi' => $totalRealisasi,
                'pagu'      => $pagu,
                'sisa'      => $pagu - $totalRealisasi,
                'pct'       => $pagu > 0 ? round($totalRealisasi / $pagu * 100, 1) : null,
            ];
        }

        return view('reports.department-transactions', compact(
            'periods', 'period', 'departments', 'department', 'months', 'pagu', 'totals'
        ));
    }

    // Laporan Realisasi Bulanan — tren pengajuan & realisasi dana per bulan dalam satu periode anggaran
    public function monthlyRealization(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        [$periods, $period] = $this->resolvePeriod($request, $orgIds);

        $departments = collect();
        $department  = null;
        $months      = collect();
        $pagu        = 0.0;
        $totals      = null;

        if ($period) {
            $departments = BudgetAllocation::with('department:id,name')
                ->where('budget_period_id', $period->id)
                ->get()->pluck('department')->filter()->unique('id')->sortBy('name')->values();

            $department = $request->filled('department_id')
                ? $departments->firstWhere('id', $request->department_id)
                : null;

            $pagu = (float) BudgetAllocation::where('budget_period_id', $period->id)
                ->when($department, fn($q) => $q->where('department_id', $department->id))
                ->sum('amount');

            $rows = FundRequest::where('budget_period_id', $period->id)
                ->where('status', '!=', 'draft')
                ->when($department, fn($q) => $q->where('department_id', $department->id))
                ->get(['id', 'amount', 'submitted_at', 'disbursed_at']);

            $refundedByFr = $this->confirmedRefundsFor($rows->pluck('id'));

            $rows->each(function ($fr) use ($refundedByFr) {
                $fr->refunded = (float) ($refundedByFr[$fr->id] ?? 0);
                $fr->realized = $fr->disbursed_at ? ((float) $fr->amount - $fr->refunded) : 0.0;
            });

            $byRequestMonth  = $rows->groupBy(fn($fr) => $fr->submitted_at?->format('Y-m'));
            $byDisburseMonth = $rows->filter(fn($fr) => $fr->disbursed_at)->groupBy(fn($fr) => $fr->disbursed_at->format('Y-m'));

            $cursor    = $period->period_start->copy()->startOfMonth();
            $end       = $period->period_end->copy()->startOfMonth();
            $kumulatif = 0.0;

            while ($cursor->lte($end)) {
                $key = $cursor->format('Y-m');
                $reqGroup  = $byRequestMonth->get($key, collect());
                $disGroup  = $byDisburseMonth->get($key, collect());
                $realisasiBulan = $disGroup->sum('realized');
                $kumulatif += $realisasiBulan;

                $months->push((object) [
                    'label'          => $cursor->translatedFormat('F Y'),
                    'jumlah_ajuan'   => $reqGroup->count(),
                    'diajukan'       => $reqGroup->sum('amount'),
                    'jumlah_cair'    => $disGroup->count(),
                    'realisasi'      => $realisasiBulan,
                    'kumulatif'      => $kumulatif,
                    'pct_kumulatif'  => $pagu > 0 ? round($kumulatif / $pagu * 100, 1) : null,
                ]);

                $cursor->addMonth();
            }

            $totals = (object) [
                'diajukan'  => $rows->sum('amount'),
                'realisasi' => $kumulatif,
                'pagu'      => $pagu,
                'sisa'      => $pagu - $kumulatif,
                'pct'       => $pagu > 0 ? round($kumulatif / $pagu * 100, 1) : null,
            ];
        }

        return view('reports.monthly-realization', compact(
            'periods', 'period', 'departments', 'department', 'months', 'pagu', 'totals'
        ));
    }

    // Laporan Detail Transaksi — rincian seluruh pengajuan dana lintas departemen dalam satu periode, dengan selisih diajukan vs dicairkan
    public function detailTransactions(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        [$periods, $period] = $this->resolvePeriod($request, $orgIds);

        $departments  = collect();
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $totals       = null;

        if ($period) {
            $departments = BudgetAllocation::with('department:id,name')
                ->where('budget_period_id', $period->id)
                ->get()->pluck('department')->filter()->unique('id')->sortBy('name')->values();

            $base = FundRequest::where('budget_period_id', $period->id)
                ->where('status', '!=', 'draft')
                ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id))
                ->when($request->filled('status'), function ($q) use ($request) {
                    match ($request->status) {
                        'pending'   => $q->where('status', 'pending'),
                        'approved'  => $q->where('status', 'approved')->whereNull('disbursed_at'),
                        'disbursed' => $q->whereNotNull('disbursed_at'),
                        'rejected'  => $q->where('status', 'rejected'),
                        default     => null,
                    };
                });

            $filteredIds  = (clone $base)->pluck('id');
            $refundedByFr = $this->confirmedRefundsFor($filteredIds);

            $summary = (clone $base)->selectRaw('COUNT(*) as total_count, COALESCE(SUM(amount), 0) as total_amount')->first();
            $disbursedTotal = (clone $base)->whereNotNull('disbursed_at')->sum('amount');

            $transactions = (clone $base)
                ->with(['department', 'requester', 'budgetProgram'])
                ->orderByDesc('submitted_at')
                ->paginate(20)->withQueryString();

            $transactions->getCollection()->transform(function ($fr) use ($refundedByFr) {
                $fr->refunded = (float) ($refundedByFr[$fr->id] ?? 0);
                $fr->realized = $fr->disbursed_at ? ((float) $fr->amount - $fr->refunded) : 0.0;
                $fr->selisih  = (float) $fr->amount - $fr->realized;
                return $fr;
            });

            $totalRealisasi = (float) $disbursedTotal - (float) $refundedByFr->sum();
            $totals = (object) [
                'count'     => $summary->total_count,
                'diajukan'  => (float) $summary->total_amount,
                'realisasi' => $totalRealisasi,
                'selisih'   => (float) $summary->total_amount - $totalRealisasi,
            ];
        }

        return view('reports.detail-transactions', compact(
            'periods', 'period', 'departments', 'transactions', 'totals'
        ));
    }

    // Buku Besar — riwayat mutasi debit/kredit per akun dengan saldo berjalan (hanya jurnal posted)
    public function generalLedger(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        $accounts = Account::where('organization_id', $orgId)
            ->where('is_header', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'account_type', 'normal_balance', 'is_active']);

        [$dateFrom, $dateTo] = $this->dateRange($request);

        $account = $request->filled('account_id')
            ? $accounts->firstWhere('id', $request->account_id)
            : null;

        $lines = collect();
        $opening = 0.0;
        $totals = null;

        if ($account) {
            // Saldo dinyatakan menurut saldo normal akun: debit-normal = D−K, kredit-normal = K−D
            $sign = $account->normal_balance === 'kredit' ? -1 : 1;

            if ($dateFrom) {
                $pre = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                        ->whereDate('entry_date', '<', $dateFrom))
                    ->selectRaw('COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
                    ->first();
                $opening = $sign * ((float) $pre->d - (float) $pre->c);
            }

            $lines = JournalEntryLine::with('journalEntry:id,entry_date,reference,description')
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                    ->when($dateFrom, fn($qq) => $qq->whereDate('entry_date', '>=', $dateFrom))
                    ->when($dateTo, fn($qq) => $qq->whereDate('entry_date', '<=', $dateTo)))
                ->get()
                ->sortBy(fn($l) => [
                    $l->journalEntry->entry_date->toDateString(),
                    $l->journalEntry->reference,
                    $l->sort_order,
                ])->values();

            $running = $opening;
            foreach ($lines as $line) {
                $running += $sign * ((float) $line->debit - (float) $line->credit);
                $line->running_balance = $running;
            }

            $totals = (object) [
                'debit'   => $lines->sum('debit'),
                'credit'  => $lines->sum('credit'),
                'closing' => $running,
            ];
        }

        return view('reports.general-ledger', compact(
            'organizations', 'orgId', 'accounts', 'account', 'lines',
            'opening', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    // Neraca Saldo — saldo awal, mutasi, dan saldo akhir semua akun pada satu periode (hanya jurnal posted)
    public function trialBalance(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        [$dateFrom, $dateTo] = $this->trialBalanceDateRange($request);
        $showZero = $request->boolean('show_zero');

        $rows = collect();
        $totals = null;

        if ($orgId) {
            $accountList = Account::where('organization_id', $orgId)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'account_type', 'normal_balance']);

            $opening  = $this->sumsPerAccount($accountList->pluck('id'), lt: $dateFrom);
            $mutation = $this->sumsPerAccount($accountList->pluck('id'), from: $dateFrom, to: $dateTo);

            $totals = (object) [
                'opening_debit' => 0.0, 'opening_credit' => 0.0,
                'mutation_debit' => 0.0, 'mutation_credit' => 0.0,
                'closing_debit' => 0.0, 'closing_credit' => 0.0,
            ];

            $rows = $accountList->map(function ($acc) use ($opening, $mutation, $totals) {
                $op = $opening[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];
                $mu = $mutation[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];

                $openingNet = $op['debit'] - $op['credit'];
                $closingNet = $openingNet + $mu['debit'] - $mu['credit'];

                $row = (object) [
                    'account'         => $acc,
                    'opening_debit'   => max($openingNet, 0.0),
                    'opening_credit'  => max(-$openingNet, 0.0),
                    'mutation_debit'  => $mu['debit'],
                    'mutation_credit' => $mu['credit'],
                    'closing_debit'   => max($closingNet, 0.0),
                    'closing_credit'  => max(-$closingNet, 0.0),
                ];

                $totals->opening_debit   += $row->opening_debit;
                $totals->opening_credit  += $row->opening_credit;
                $totals->mutation_debit  += $row->mutation_debit;
                $totals->mutation_credit += $row->mutation_credit;
                $totals->closing_debit   += $row->closing_debit;
                $totals->closing_credit  += $row->closing_credit;

                return $row;
            });

            if (!$showZero) {
                $rows = $rows->filter(fn($r) => $r->opening_debit || $r->opening_credit
                    || $r->mutation_debit || $r->mutation_credit || $r->closing_debit || $r->closing_credit);
            }

            $rows = $rows->values();
        }

        return view('reports.trial-balance', compact(
            'organizations', 'orgId', 'rows', 'totals', 'dateFrom', 'dateTo', 'showZero'
        ));
    }

    // Laporan Laba Rugi — pendapatan dikurangi beban pada satu periode (hanya jurnal posted)
    public function incomeStatement(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        [$dateFrom, $dateTo] = $this->trialBalanceDateRange($request);

        $sections = null;

        if ($orgId) {
            $accounts = Account::where('organization_id', $orgId)
                ->where('is_header', false)
                ->whereIn('account_type', ['pendapatan', 'beban'])
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'account_type', 'normal_balance']);

            $mutation = $this->sumsPerAccount($accounts->pluck('id'), from: $dateFrom, to: $dateTo);

            $rowsFor = function (string $type) use ($accounts, $mutation) {
                return $accounts->where('account_type', $type)->map(function ($acc) use ($mutation) {
                    $m = $mutation[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];
                    $sign = $acc->normal_balance === 'kredit' ? -1 : 1;

                    return (object) [
                        'account' => $acc,
                        'amount'  => $sign * ($m['debit'] - $m['credit']),
                    ];
                })->filter(fn($r) => abs($r->amount) > 0.005)->values();
            };

            $pendapatan = $rowsFor('pendapatan');
            $beban = $rowsFor('beban');
            $totalPendapatan = $pendapatan->sum('amount');
            $totalBeban = $beban->sum('amount');

            $sections = (object) [
                'pendapatan'      => $pendapatan,
                'beban'           => $beban,
                'totalPendapatan' => $totalPendapatan,
                'totalBeban'      => $totalBeban,
                'labaBersih'      => $totalPendapatan - $totalBeban,
            ];
        }

        return view('reports.income-statement', compact(
            'organizations', 'orgId', 'dateFrom', 'dateTo', 'sections'
        ));
    }

    // Laporan Neraca (Laporan Posisi Keuangan) — saldo aset, kewajiban & ekuitas per tanggal (hanya jurnal posted)
    public function balanceSheet(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        $asOf = $request->input('as_of') ?: now()->toDateString();

        $sections = null;
        $isBalanced = null;

        if ($orgId) {
            $accounts = Account::where('organization_id', $orgId)
                ->where('is_header', false)
                ->orderBy('code')
                ->get(['id', 'code', 'name', 'account_type', 'normal_balance']);

            $balances = $this->sumsPerAccount($accounts->pluck('id'), to: $asOf);

            $rowsFor = function (string $type) use ($accounts, $balances) {
                return $accounts->where('account_type', $type)->map(function ($acc) use ($balances) {
                    $b = $balances[$acc->id] ?? ['debit' => 0.0, 'credit' => 0.0];
                    $sign = $acc->normal_balance === 'kredit' ? -1 : 1;

                    return (object) [
                        'account' => $acc,
                        'amount'  => $sign * ($b['debit'] - $b['credit']),
                    ];
                })->filter(fn($r) => abs($r->amount) > 0.005)->values();
            };

            $aset = $rowsFor('aset');
            $kewajiban = $rowsFor('kewajiban');
            $ekuitas = $rowsFor('ekuitas');

            // Belum ada proses tutup buku otomatis, jadi laba/rugi berjalan dihitung dari
            // akumulasi pendapatan-beban sejak awal s.d. tanggal ini, supaya Aset = Kewajiban + Ekuitas.
            $labaBerjalan = $rowsFor('pendapatan')->sum('amount') - $rowsFor('beban')->sum('amount');

            $totalAset = $aset->sum('amount');
            $totalKewajiban = $kewajiban->sum('amount');
            $totalEkuitas = $ekuitas->sum('amount') + $labaBerjalan;

            $sections = (object) [
                'aset'           => $aset,
                'kewajiban'      => $kewajiban,
                'ekuitas'        => $ekuitas,
                'labaBerjalan'   => $labaBerjalan,
                'totalAset'      => $totalAset,
                'totalKewajiban' => $totalKewajiban,
                'totalEkuitas'   => $totalEkuitas,
            ];

            $isBalanced = abs($totalAset - ($totalKewajiban + $totalEkuitas)) < 0.5;
        }

        return view('reports.balance-sheet', compact(
            'organizations', 'orgId', 'asOf', 'sections', 'isBalanced'
        ));
    }

    // Laporan Arus Kas — mutasi kas/bank pada satu periode, diklasifikasi ke aktivitas operasi/investasi/pendanaan
    // berdasarkan akun lawan pada tiap jurnal (metode langsung). Transfer antar rekening kas sendiri diabaikan.
    public function cashFlow(Request $request)
    {
        $orgIds = auth()->user()->organizationIds();
        $organizations = $this->organizationOptions($orgIds);

        $orgId = $request->input('organization_id');
        if (!$organizations->contains('id', $orgId)) {
            $orgId = $organizations->first()?->id;
        }

        [$dateFrom, $dateTo] = $this->trialBalanceDateRange($request);

        $sections = null;
        $noCashAccounts = false;

        if ($orgId) {
            $kasAccountIds = Account::where('organization_id', $orgId)
                ->where('is_header', false)
                ->where('code', 'like', '1.1.01.%')
                ->pluck('id');

            if ($kasAccountIds->isEmpty()) {
                $noCashAccounts = true;
            } else {
                $openingCash = 0.0;
                if ($dateFrom) {
                    $pre = $this->sumsPerAccount($kasAccountIds, lt: $dateFrom);
                    foreach ($pre as $s) {
                        $openingCash += $s['debit'] - $s['credit'];
                    }
                }

                // Semua entri jurnal posted pada periode ini yang menyentuh akun kas/bank
                $entryIds = JournalEntryLine::whereIn('account_id', $kasAccountIds)
                    ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')
                        ->when($dateFrom, fn($qq) => $qq->whereDate('entry_date', '>=', $dateFrom))
                        ->when($dateTo, fn($qq) => $qq->whereDate('entry_date', '<=', $dateTo)))
                    ->pluck('journal_entry_id')->unique();

                $buckets = [
                    'operasi'   => collect(),
                    'investasi' => collect(),
                    'pendanaan' => collect(),
                ];

                if ($entryIds->isNotEmpty()) {
                    $lines = JournalEntryLine::with('account:id,code,name,account_type')
                        ->whereIn('journal_entry_id', $entryIds)
                        ->get()
                        ->groupBy('journal_entry_id');

                    foreach ($lines as $entryLines) {
                        $otherLines = $entryLines->filter(fn($l) => !$kasAccountIds->contains($l->account_id));

                        foreach ($otherLines as $line) {
                            // Bagian arus kas yang berasal dari akun lawan ini (positif = kas masuk)
                            $amount = (float) $line->credit - (float) $line->debit;
                            if (abs($amount) < 0.005) {
                                continue;
                            }

                            $activity = $this->classifyCashFlowActivity($line->account);
                            $buckets[$activity]->push((object) [
                                'account' => $line->account,
                                'amount'  => $amount,
                            ]);
                        }
                    }
                }

                $groupRows = function ($rows) {
                    return $rows->groupBy('account.id')->map(function ($group) {
                        return (object) [
                            'account' => $group->first()->account,
                            'amount'  => $group->sum('amount'),
                        ];
                    })->filter(fn($r) => abs($r->amount) > 0.005)->sortBy('account.code')->values();
                };

                $operasi = $groupRows($buckets['operasi']);
                $investasi = $groupRows($buckets['investasi']);
                $pendanaan = $groupRows($buckets['pendanaan']);

                $totalOperasi = $operasi->sum('amount');
                $totalInvestasi = $investasi->sum('amount');
                $totalPendanaan = $pendanaan->sum('amount');
                $kenaikanBersih = $totalOperasi + $totalInvestasi + $totalPendanaan;
                $closingCash = $openingCash + $kenaikanBersih;

                // Verifikasi silang terhadap saldo kas aktual (kumulatif) per tanggal akhir periode
                $actualClosing = 0.0;
                if ($dateTo) {
                    $act = $this->sumsPerAccount($kasAccountIds, to: $dateTo);
                    foreach ($act as $s) {
                        $actualClosing += $s['debit'] - $s['credit'];
                    }
                } else {
                    $actualClosing = $closingCash;
                }

                $sections = (object) [
                    'operasi'         => $operasi,
                    'investasi'       => $investasi,
                    'pendanaan'       => $pendanaan,
                    'totalOperasi'    => $totalOperasi,
                    'totalInvestasi'  => $totalInvestasi,
                    'totalPendanaan'  => $totalPendanaan,
                    'openingCash'     => $openingCash,
                    'kenaikanBersih'  => $kenaikanBersih,
                    'closingCash'     => $closingCash,
                    'actualClosing'   => $actualClosing,
                    'isReconciled'    => abs($closingCash - $actualClosing) < 0.5,
                ];
            }
        }

        return view('reports.cash-flow', compact(
            'organizations', 'orgId', 'dateFrom', 'dateTo', 'sections', 'noCashAccounts'
        ));
    }

    // Klasifikasi aktivitas arus kas dari akun lawan: pendanaan (ekuitas/kewajiban jk panjang),
    // investasi (aset tidak lancar/investasi), sisanya operasi (pendapatan, beban, aset & kewajiban lancar lainnya)
    private function classifyCashFlowActivity(Account $account): string
    {
        if ($account->account_type === 'ekuitas' || str_starts_with($account->code, '2.2')) {
            return 'pendanaan';
        }

        if (str_starts_with($account->code, '1.2')
            || str_starts_with($account->code, '1.1.05')
            || str_starts_with($account->code, '1.1.06')) {
            return 'investasi';
        }

        return 'operasi';
    }

    // Jumlah debit/kredit per akun dari jurnal posted, dibatasi salah satu: lt (sebelum tanggal), atau from/to (rentang)
    private function sumsPerAccount($accountIds, ?string $lt = null, ?string $from = null, ?string $to = null): array
    {
        if ($accountIds->isEmpty()) {
            return [];
        }

        $sums = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function ($q) use ($lt, $from, $to) {
                $q->where('status', 'posted')
                    ->when($lt, fn($qq) => $qq->whereDate('entry_date', '<', $lt))
                    ->when($from, fn($qq) => $qq->whereDate('entry_date', '>=', $from))
                    ->when($to, fn($qq) => $qq->whereDate('entry_date', '<=', $to));
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
            ->groupBy('account_id')
            ->get();

        return $sums->mapWithKeys(fn($r) => [
            $r->account_id => ['debit' => (float) $r->d, 'credit' => (float) $r->c],
        ])->all();
    }

    // Default: awal tahun berjalan s.d. hari ini. Jika form sudah pernah disubmit, hormati input user (kosong = tanpa batas).
    private function trialBalanceDateRange(Request $request): array
    {
        if ($request->has('date_from') || $request->has('date_to')) {
            return [$request->input('date_from'), $request->input('date_to')];
        }

        return [now()->startOfYear()->toDateString(), now()->toDateString()];
    }

    // Default: bulan berjalan. Jika form sudah pernah disubmit, hormati input user (kosong = tanpa batas).
    private function dateRange(Request $request): array
    {
        if ($request->has('date_from') || $request->has('date_to')) {
            return [$request->input('date_from'), $request->input('date_to')];
        }

        return [now()->startOfMonth()->toDateString(), now()->toDateString()];
    }

    private function recapPerOrganization($base)
    {
        $rows = (clone $base)
            ->selectRaw('organization_id, COUNT(*) as jumlah, COALESCE(SUM(amount), 0) as total')
            ->groupBy('organization_id')
            ->orderByDesc('total')
            ->get();

        $names = Organization::whereIn('id', $rows->pluck('organization_id'))->pluck('name', 'id');

        return $rows->map(fn($r) => (object) [
            'name'   => $names[$r->organization_id] ?? '-',
            'jumlah' => $r->jumlah,
            'total'  => $r->total,
        ]);
    }

    private function organizationOptions($orgIds)
    {
        return Organization::when($orgIds !== null, fn($q) => $q->whereIn('id', $orgIds))
            ->orderBy('name')->get(['id', 'name']);
    }

    // Resolusi periode anggaran terpilih: dari query string, atau periode aktif, atau yang terbaru
    private function resolvePeriod(Request $request, ?array $orgIds): array
    {
        $periods = BudgetPeriod::with('organization:id,name')
            ->when($orgIds !== null, fn($q) => $q->whereIn('organization_id', $orgIds))
            ->orderByDesc('period_start')
            ->get();

        $period = $request->filled('budget_period_id')
            ? $periods->firstWhere('id', $request->budget_period_id)
            : null;
        $period ??= $periods->firstWhere('is_active', true) ?? $periods->first();

        return [$periods, $period];
    }

    // Total pengembalian dana terkonfirmasi per fund_request_id, untuk sekumpulan ID pengajuan
    private function confirmedRefundsFor($fundRequestIds)
    {
        return FundRefund::where('status', 'confirmed')
            ->whereIn('fund_request_id', $fundRequestIds)
            ->selectRaw('fund_request_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('fund_request_id')
            ->pluck('total', 'fund_request_id');
    }
}
