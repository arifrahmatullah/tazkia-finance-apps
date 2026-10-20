<?php

namespace App\Models;

use App\Models\Account;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProgramDetail extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'budget_program_id', 'account_id', 'description', 'quantity', 'unit',
        'unit_price', 'total_amount', 'notes',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // Termin spesifik yang dipakai rincian ini -- kosong (belum pernah di-custom) berarti
    // default: berlaku di SEMUA termin program, termasuk otomatis ikut termin baru kalau
    // frekuensi program ditambah nanti. Begitu ada baris di sini, rincian dianggap "custom",
    // cuma berlaku di termin yang dicentang -- dipakai buat rincian yang frekuensinya beda
    // dari program (mis. biaya sekali bayar di program yang jalan beberapa termin).
    public function termins()
    {
        return $this->belongsToMany(BudgetProgramSchedule::class, 'budget_program_detail_schedules');
    }

    public function appliesToSchedule(BudgetProgramSchedule $schedule): bool
    {
        $termins = $this->relationLoaded('termins') ? $this->termins : $this->termins()->get();
        return $termins->isEmpty() || $termins->contains('id', $schedule->id);
    }

    // Jumlah termin efektif rincian ini -- dasar quantity (lihat syncQuantityFromTermins()).
    // Belum di-custom = ikut frekuensi program apa adanya (perilaku lama).
    public function effectiveTerminCount(): int
    {
        $count = $this->termins()->count();
        return $count > 0 ? $count : max(1, (int) $this->budgetProgram->frequency);
    }

    // Samakan quantity (otomatis total_amount ikut, lewat saving() hook di bawah) dengan jumlah
    // termin efektif saat ini -- dipanggil tiap rincian disimpan atau tiap frekuensi/jadwal
    // program berubah, supaya quantity selalu konsisten dengan pilihan termin (atau defaultnya).
    public function syncQuantityFromTermins(): void
    {
        $this->update(['quantity' => $this->effectiveTerminCount()]);
    }

    protected static function booted(): void
    {
        static::saving(function (self $detail) {
            $detail->total_amount = round($detail->quantity * $detail->unit_price, 2);
        });
    }
}
