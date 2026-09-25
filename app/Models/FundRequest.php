<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundRequest extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    // Status yang berarti pengajuan ini tidak lagi "aktif menahan" apa pun --
    // baik pagu program maupun termin di Estimasi Jadwal -- karena ditolak
    // approver ATAU dibatalkan sendiri oleh pengaju/Keuangan. Dipakai di semua
    // tempat yang menghitung "sisa pagu" atau "termin masih tersedia".
    public const VOID_STATUSES = ['rejected', 'cancelled'];

    // Batas waktu pengaju mengirim laporan penggunaan dana sejak dana cair. Lewat dari ini
    // dan masih ada yang belum dilaporkan, pengaju tidak bisa membuat pengajuan baru.
    public const REPORT_DEADLINE_DAYS = 14;

    protected $fillable = [
        'organization_id', 'department_id', 'budget_period_id', 'budget_program_id',
        'budget_program_schedule_id',
        'requester_id', 'requester_position_id', 'reference',
        'title', 'purpose', 'amount', 'original_amount',
        'bank_name', 'bank_account_number', 'bank_account_name',
        'status', 'current_step', 'total_steps', 'notes',
        'submitted_at', 'approved_at', 'rejected_at',
        'cancelled_at', 'cancelled_by',
        'disbursed_at', 'report_due_at', 'disbursement_notes', 'disbursed_by', 'disburse_account_id',
        'receipt_status', 'receipt_confirmed_at', 'receipt_notes', 'auto_confirmed',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'original_amount'      => 'decimal:2',
        'submitted_at'         => 'datetime',
        'approved_at'          => 'datetime',
        'rejected_at'          => 'datetime',
        'cancelled_at'         => 'datetime',
        'disbursed_at'         => 'datetime',
        'report_due_at'        => 'datetime',
        'receipt_confirmed_at' => 'datetime',
        'auto_confirmed'       => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function budgetPeriod()
    {
        return $this->belongsTo(BudgetPeriod::class);
    }

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
    }

    public function schedule()
    {
        return $this->belongsTo(BudgetProgramSchedule::class, 'budget_program_schedule_id');
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function requesterPosition()
    {
        return $this->belongsTo(Position::class, 'requester_position_id');
    }

    public function approvals()
    {
        return $this->hasMany(FundRequestApproval::class)->orderBy('step');
    }

    public function currentApproval()
    {
        return $this->hasOne(FundRequestApproval::class)
            ->where('step', $this->current_step)
            ->where('status', 'waiting');
    }

    public function disburseAccount()
    {
        return $this->belongsTo(Account::class, 'disburse_account_id');
    }

    public function details()
    {
        return $this->hasMany(FundRequestDetail::class);
    }

    public function files()
    {
        return $this->hasMany(FundRequestFile::class)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(FundRequestFile::class)->where('type', 'attachment')->latest();
    }

    public function disbursementProofs()
    {
        return $this->hasMany(FundRequestFile::class)->where('type', 'disbursement_proof')->latest();
    }

    public function fundReports()
    {
        return $this->hasMany(FundReport::class)->latest();
    }

    public function fundRefunds()
    {
        return $this->hasMany(FundRefund::class)->latest();
    }

    public function cashTopupRequests()
    {
        return $this->belongsToMany(CashTopupRequest::class, 'cash_topup_request_fund_requests')
            ->withTimestamps();
    }

    // Jenis "pembayaran" langsung ditransfer ke tujuan (vendor/tagihan),
    // bukan ke rekening pengaju, sehingga tidak perlu laporan penggunaan dana.
    public function needsReport(): bool
    {
        return $this->budgetProgram?->type !== 'pembayaran';
    }

    // Batas lapor: eksplisit (report_due_at, diisi saat jenis program diubah Keuangan) kalau ada,
    // kalau tidak ya tanggal cair + REPORT_DEADLINE_DAYS. Null kalau belum cair.
    public function reportDueAt(): ?\Carbon\Carbon
    {
        if ($this->report_due_at) {
            return $this->report_due_at;
        }

        return $this->disbursed_at?->copy()->addDays(self::REPORT_DEADLINE_DAYS);
    }

    // Sudah cair, wajib laporan, belum ada laporan yang masuk (menunggu/disetujui) -- laporan
    // yang ditolak dianggap belum. Sama dengan definisi kartu "Belum Laporan" di dashboard.
    public function scopeUnreported($query)
    {
        return $query->whereNotNull('disbursed_at')
            ->whereNotIn('status', self::VOID_STATUSES)
            ->whereDoesntHave('budgetProgram', fn($p) => $p->where('type', 'pembayaran'))
            ->whereDoesntHave('fundReports', fn($r) => $r->whereIn('status', ['waiting', 'approved']));
    }

    // Pengajuan belum-dilaporkan milik pengaju yang batas lapornya sudah lewat.
    public static function overdueUnreportedFor(string $employeeId)
    {
        return self::unreported()
            ->where('requester_id', $employeeId)
            ->get()
            ->filter(fn($fr) => $fr->reportDueAt()?->isPast())
            ->values();
    }

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isDisbursed(): bool  { return !is_null($this->disbursed_at); }
    public function isAmountCorrected(): bool { return !is_null($this->original_amount); }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isVoid(): bool { return in_array($this->status, self::VOID_STATUSES, true); }

    // Bisa dibatalkan pengaju selama belum dicairkan -- baik masih menunggu approval
    // maupun sudah disetujui tapi Keuangan belum menekan "Cairkan". Begitu dana sudah
    // cair, pembatalan harus lewat proses pengembalian dana, bukan tombol batal ini.
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved'], true) && !$this->isDisbursed();
    }

    public static function generateReference(string $orgId, string $date): string
    {
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "PD-{$year}{$month}-";

        $last = self::withTrashed()
            ->where('organization_id', $orgId)
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('reference')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
