<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasUuids;

    public $timestamps  = false;
    public $updatable   = false;

    protected $fillable = [
        'user_id', 'user_name', 'action',
        'auditable_type', 'auditable_id',
        'old_values', 'new_values',
        'ip_address', 'url',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    // Untuk aktivitas yang bukan perubahan data model (login/logout) — event Eloquent tidak berlaku di sini
    public static function record(string $action, ?User $user, array $extra = []): void
    {
        static::create([
            'user_id'        => $user?->id,
            'user_name'      => $user?->name ?? ($extra['attempted_email'] ?? 'Tidak diketahui'),
            'action'         => $action,
            'auditable_type' => User::class,
            'auditable_id'   => (string) ($user?->id ?? ''),
            'old_values'     => null,
            'new_values'     => $extra ?: null,
            'ip_address'     => request()?->ip(),
            'url'            => request()?->fullUrl(),
            'created_at'     => now(),
        ]);
    }

    public static function modelLabel(string $type): string
    {
        return match (class_basename($type)) {
            'BudgetProgram'        => 'Program Kerja',
            'BudgetProgramDetail'  => 'Rincian Program Kerja',
            'BudgetAllocation'     => 'Pagu Anggaran',
            'BudgetPeriod'         => 'Periode Anggaran',
            'JournalEntry'         => 'Jurnal',
            'JournalEntryLine'     => 'Baris Jurnal',
            'JournalTemplate'      => 'Template Jurnal',
            'JournalTemplateDetail'=> 'Baris Template Jurnal',
            'FundRequest'          => 'Pengajuan Dana',
            'FundRequestApproval'  => 'Persetujuan Dana',
            'FundRequestFile'      => 'Lampiran Pengajuan Dana',
            'FundReport'           => 'Laporan Dana',
            'FundReportFile'       => 'Lampiran Laporan Dana',
            'FundRefund'           => 'Pengembalian Dana',
            'IncomeEstimate'       => 'Estimasi Pendapatan',
            'IncomeEstimateDetail' => 'Rincian Estimasi',
            'ApprovalSetting'      => 'Setting Approval',
            'Account'              => 'Akun COA',
            'Department'           => 'Departemen',
            'Organization'         => 'Organisasi',
            'Employee'             => 'Pegawai',
            'EmployeePosition'     => 'Riwayat Jabatan',
            'User'                 => 'Pengguna',
            'Position'             => 'Jabatan',
            'Role'                 => 'Role',
            'UserOrganizationRole' => 'Akses Role Pengguna',
            default                => class_basename($type),
        };
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'created'      => 'Dibuat',
            'updated'      => 'Diubah',
            'deleted'      => 'Dihapus',
            'restored'     => 'Dipulihkan',
            'login'        => 'Login',
            'login_failed' => 'Login Gagal',
            'logout'       => 'Logout',
            default        => $action,
        };
    }

    public static function actionColor(string $action): string
    {
        return match ($action) {
            'created'      => 'green',
            'updated'      => 'blue',
            'deleted'      => 'red',
            'restored'     => 'orange',
            'login'        => 'green',
            'login_failed' => 'red',
            'logout'       => 'slate',
            default        => 'slate',
        };
    }
}
