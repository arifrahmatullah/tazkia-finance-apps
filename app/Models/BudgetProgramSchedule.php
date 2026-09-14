<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetProgramSchedule extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'budget_program_id', 'termin', 'estimated_date', 'notes', 'amount',
    ];

    protected $casts = [
        'estimated_date' => 'date',
        'amount'         => 'decimal:2',
    ];

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
    }

    public function fundRequests()
    {
        return $this->hasMany(FundRequest::class, 'budget_program_schedule_id');
    }

    public function detailOverrides()
    {
        return $this->hasMany(BudgetProgramScheduleDetail::class, 'budget_program_schedule_id');
    }

    // Plafon efektif satu rincian KHUSUS untuk termin ini -- pakai override (di-custom manual
    // lewat modal Edit Termin) kalau ada, else jatuh balik ke unit_price default rincian itu
    // (berlaku sama di semua termin yang belum di-custom).
    public function effectiveUnitPriceFor(BudgetProgramDetail $detail): float
    {
        $override = $this->relationLoaded('detailOverrides')
            ? $this->detailOverrides->firstWhere('budget_program_detail_id', $detail->id)
            : $this->detailOverrides()->where('budget_program_detail_id', $detail->id)->first();

        return (float) ($override->unit_price ?? $detail->unit_price);
    }

    // Termin dianggap "terpakai" kalau ada pengajuan dana yang menempel padanya dan masih
    // aktif -- begitu ditolak atau dibatalkan, termin otomatis kebuka lagi untuk pengajuan
    // berikutnya.
    public function activeFundRequest()
    {
        return $this->fundRequests()->whereNotIn('status', FundRequest::VOID_STATUSES)->latest()->first();
    }

    // Label bulan+tahun berbahasa Indonesia dari tanggal estimasi termin ini (mis. "September
    // 2026") -- dipakai di narasi UI/pesan error supaya jelas termin yang mana yang dimaksud.
    public function monthLabel(): ?string
    {
        return $this->estimated_date?->locale('id')->translatedFormat('F Y');
    }
}
