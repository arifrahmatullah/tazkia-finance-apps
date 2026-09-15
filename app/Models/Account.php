<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'parent_id', 'code', 'name',
        'account_type', 'normal_balance', 'description', 'is_header', 'is_active',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'aset'       => ['label' => 'Aset',       'normal' => 'debit',  'color' => '#2563eb', 'prefix' => '1'],
        'kewajiban'  => ['label' => 'Kewajiban',   'normal' => 'kredit', 'color' => '#dc2626', 'prefix' => '2'],
        'ekuitas'    => ['label' => 'Ekuitas',     'normal' => 'kredit', 'color' => '#7c3aed', 'prefix' => '3'],
        'pendapatan' => ['label' => 'Pendapatan',  'normal' => 'kredit', 'color' => '#16a34a', 'prefix' => '4'],
        'beban'      => ['label' => 'Beban',       'normal' => 'debit',  'color' => '#ea580c', 'prefix' => '5'],
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id')->orderBy('code');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->account_type]['label'] ?? $this->account_type;
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->account_type]['color'] ?? '#64748b';
    }

    // Saldo berjalan akun ini dari SEMUA jurnal yang sudah posted (termasuk saldo awal, yang
    // disimpan sebagai jurnal biasa dengan source_type 'beginning_balance') -- sama seperti
    // logic Buku Besar di FinanceReportController, cuma dijadikan satu method yang bisa dipakai
    // ulang (mis. buat cek saldo rekening sebelum pencairan).
    public function currentBalance(): float
    {
        $sign = $this->normal_balance === 'kredit' ? -1 : 1;

        $totals = JournalEntryLine::where('account_id', $this->id)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->selectRaw('COALESCE(SUM(debit), 0) as d, COALESCE(SUM(credit), 0) as c')
            ->first();

        return $sign * ((float) $totals->d - (float) $totals->c);
    }
}
