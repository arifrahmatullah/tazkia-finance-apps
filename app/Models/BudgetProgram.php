<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProgram extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    public const TYPES = [
        'pengadaan'  => 'Pengadaan',
        'kegiatan'   => 'Kegiatan',
        'pembayaran' => 'Pembayaran',
    ];

    protected $fillable = [
        'budget_allocation_id', 'account_id', 'name', 'type', 'notes', 'frequency', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'frequency'  => 'integer',
    ];

    public function budgetAllocation()
    {
        return $this->belongsTo(BudgetAllocation::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function details()
    {
        return $this->hasMany(BudgetProgramDetail::class);
    }

    public function schedules()
    {
        return $this->hasMany(BudgetProgramSchedule::class)->orderBy('termin');
    }

    public function changeRequests()
    {
        return $this->hasMany(BudgetProgramChangeRequest::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? '-';
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->details()->sum('total_amount');
    }

    public function getNominalPerTerminAttribute(): float
    {
        $freq = max(1, (int) $this->frequency);
        return $freq > 0 ? round($this->total_amount / $freq, 2) : 0;
    }

    // Tambah total program (via rincian, karena total_amount itu SUM rincian, bukan kolom
    // sendiri) sebesar $excess -- dipakai saat satu termin butuh lebih dari sisa yang ada,
    // dan sisa alokasi anggaran departemen masih cukup menampungnya (lihat
    // BudgetProgramScheduleController::update()). Kenaikan dibagi proporsional ke semua
    // rincian sesuai porsinya saat ini; baris terakhir menyerap sisa pembulatan.
    public function growTotalAmountBy(float $excess): void
    {
        if ($excess <= 0) {
            return;
        }

        $details = $this->details;
        if ($details->isEmpty()) {
            return;
        }

        $currentTotal = (float) $details->sum('total_amount');
        $lastIndex    = $details->count() - 1;
        $allocated    = 0.0;

        foreach ($details as $i => $detail) {
            if ($i === $lastIndex) {
                $lineIncrease = round($excess - $allocated, 2);
            } else {
                $share = $currentTotal > 0 ? ((float) $detail->total_amount / $currentTotal) : (1 / $details->count());
                $lineIncrease = round($excess * $share, 2);
                $allocated += $lineIncrease;
            }

            $quantity = (float) $detail->quantity;
            $newTotal = (float) $detail->total_amount + $lineIncrease;
            $detail->update([
                'unit_price' => $quantity > 0 ? round($newTotal / $quantity, 2) : $detail->unit_price,
            ]);
        }
    }

    // "Kunci" termin yang amount-nya masih NULL (masih ikut nominal_per_termin secara implisit)
    // ke nilai efektifnya SAAT INI, kecuali termin yang sedang diedit. Dipanggil SEBELUM
    // growTotalAmountBy() supaya termin lain tidak ikut bergeser diam-diam gara-gara
    // nominal_per_termin (rata-rata) berubah akibat total program bertambah -- cuma termin
    // yang sedang diedit yang sengaja naik, yang lain tetap seperti semula.
    public function lockImplicitScheduleAmounts(?string $exceptScheduleId, float $lockValue): void
    {
        $this->schedules()
            ->whereNull('amount')
            ->when($exceptScheduleId, fn($q) => $q->where('id', '!=', $exceptScheduleId))
            ->update(['amount' => $lockValue]);
    }

    public function regenerateSchedules(): void
    {
        $freq = max(1, (int) $this->frequency);
        $existing = $this->schedules()->orderBy('termin')->get()->keyBy('termin');

        // Hapus termin yang melebihi frekuensi baru
        $this->schedules()->where('termin', '>', $freq)->delete();

        // Tambah termin yang belum ada, nominal default dibagi rata
        for ($i = 1; $i <= $freq; $i++) {
            if (!$existing->has($i)) {
                $this->schedules()->create(['termin' => $i, 'estimated_date' => null, 'amount' => $this->nominal_per_termin]);
            }
        }
    }

    // Kalau nominal rincian berubah (nominal_per_termin ikut berubah), sinkronkan nominal
    // termin di Estimasi Jadwal yang MASIH default (sama dengan nominal_per_termin LAMA,
    // belum pernah di-custom manual satu-satu) ke nilai yang baru. Termin yang nilainya
    // sudah beda dari nominal lama dianggap sudah sengaja di-custom staf Keuangan --
    // dibiarkan, tidak ditimpa. Termin yang sudah "diambil" pengajuan dana yang masih aktif
    // (belum ditolak/dibatalkan) juga dibiarkan, supaya tidak diam-diam mengubah nominal
    // yang sudah diajukan/disetujui.
    public function syncScheduleAmountsAfterTotalChange(float $oldNominalPerTermin): void
    {
        $newNominalPerTermin = $this->nominal_per_termin;
        if (abs($newNominalPerTermin - $oldNominalPerTermin) < 0.01) {
            return;
        }

        $this->schedules()->with(['fundRequests', 'detailOverrides'])->get()->each(function ($sch) use ($oldNominalPerTermin, $newNominalPerTermin) {
            $isStillDefault = $sch->amount === null || abs((float) $sch->amount - $oldNominalPerTermin) < 0.01;
            $isTaken = $sch->fundRequests->contains(fn($fr) => !$fr->isVoid());
            $hasDetailOverride = $sch->detailOverrides->isNotEmpty();

            if ($isStillDefault && !$isTaken && !$hasDetailOverride) {
                $sch->update(['amount' => $newNominalPerTermin]);
            }
        });
    }

    // Termin yang tanggal estimasinya jatuh di bulan yang sama dengan $date (default hari
    // ini) -- pengajuan dana ditautkan ke termin sesuai kalender berjalan, bukan sekadar
    // nomor urut. Null kalau tidak ada termin yang tanggalnya cocok dengan bulan itu.
    public function scheduleForMonth(?\Carbon\Carbon $date = null): ?BudgetProgramSchedule
    {
        $date = $date ?? now();

        return $this->schedules()
            ->whereNotNull('estimated_date')
            ->whereYear('estimated_date', $date->year)
            ->whereMonth('estimated_date', $date->month)
            ->first();
    }

    // Sisa plafon termin itu (nominal termin dikurangi total SEMUA pengajuan aktif yang
    // sudah menempel di termin ini, lintas rincian) -- dipakai supaya beberapa pengajuan
    // per-rincian yang terpisah tetap tidak melebihi anggaran periode itu secara gabungan.
    public function scheduleRemainingCapacity(BudgetProgramSchedule $schedule): float
    {
        $ceiling = $schedule->amount !== null ? (float) $schedule->amount : $this->nominal_per_termin;

        $used = (float) $schedule->fundRequests()
            ->whereNotIn('status', FundRequest::VOID_STATUSES)
            ->sum('amount');

        return $ceiling - $used;
    }

    // Sisa plafon satu rincian kegiatan tertentu DI TERMIN ini saja (bukan gabungan semua
    // rincian) -- supaya satu rincian tidak bisa diajukan berkali-kali melebihi nominal
    // per-termin-nya sendiri, walau termin secara keseluruhan masih ada sisa dari rincian lain.
    public function detailRemainingCapacity(BudgetProgramDetail $detail, BudgetProgramSchedule $schedule): float
    {
        $used = (float) FundRequestDetail::where('budget_program_detail_id', $detail->id)
            ->whereHas('fundRequest', function ($q) use ($schedule) {
                $q->where('budget_program_schedule_id', $schedule->id)
                    ->whereNotIn('status', FundRequest::VOID_STATUSES);
            })
            ->sum('total_amount');

        return $schedule->effectiveUnitPriceFor($detail) - $used;
    }

    // Pengajuan Dana hanya boleh dibuat kalau semua termin di Estimasi Jadwal
    // sudah punya tanggal (jadwal jadi acuan pencairan, bukan sekadar catatan).
    public function hasCompleteSchedule(): bool
    {
        $freq = max(1, (int) $this->frequency);
        $filled = $this->schedules()->whereNotNull('estimated_date')->count();
        return $this->schedules()->count() >= $freq && $filled >= $freq;
    }

    // Edit Program Kerja (info, rincian, nominal per termin) hanya bebas dilakukan selama
    // periode perencanaan (planning_start s.d. planning_end) milik periode anggarannya.
    // Di luar itu, perubahan harus lewat alur approval (lihat BudgetProgramChangeRequest).
    public function isWithinPlanningWindow(): bool
    {
        $period = $this->budgetAllocation?->budgetPeriod;
        if (!$period || !$period->planning_start || !$period->planning_end) {
            return true;
        }

        $today = now()->toDateString();
        return $today >= $period->planning_start->toDateString() && $today <= $period->planning_end->toDateString();
    }
}
