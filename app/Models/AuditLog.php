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

    public static function modelLabel(string $type): string
    {
        return match (class_basename($type)) {
            'BudgetProgram'       => 'Program Kerja',
            'BudgetProgramDetail' => 'Rincian Program Kerja',
            'BudgetAllocation'    => 'Pagu Anggaran',
            'BudgetPeriod'        => 'Periode Anggaran',
            'JournalEntry'        => 'Jurnal',
            'JournalEntryLine'    => 'Baris Jurnal',
            'FundRequest'         => 'Pengajuan Dana',
            'FundRequestApproval' => 'Persetujuan Dana',
            'IncomeEstimate'      => 'Estimasi Pendapatan',
            'IncomeEstimateDetail'=> 'Rincian Estimasi',
            'Account'             => 'Akun COA',
            'Department'          => 'Departemen',
            'Organization'        => 'Organisasi',
            'Employee'            => 'Pegawai',
            'User'                => 'Pengguna',
            'Position'            => 'Jabatan',
            default               => class_basename($type),
        };
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            'created'  => 'Dibuat',
            'updated'  => 'Diubah',
            'deleted'  => 'Dihapus',
            'restored' => 'Dipulihkan',
            default    => $action,
        };
    }

    public static function actionColor(string $action): string
    {
        return match ($action) {
            'created'  => 'green',
            'updated'  => 'blue',
            'deleted'  => 'red',
            'restored' => 'orange',
            default    => 'slate',
        };
    }
}
