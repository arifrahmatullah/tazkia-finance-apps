<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProgram extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'budget_allocation_id', 'account_id', 'name', 'notes', 'frequency', 'is_active',
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

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->details()->sum('total_amount');
    }

    public function getNominalPerTerminAttribute(): float
    {
        $freq = max(1, (int) $this->frequency);
        return $freq > 0 ? round($this->total_amount / $freq, 2) : 0;
    }

    public function regenerateSchedules(): void
    {
        $freq = max(1, (int) $this->frequency);
        $existing = $this->schedules()->orderBy('termin')->get()->keyBy('termin');

        // Hapus termin yang melebihi frekuensi baru
        $this->schedules()->where('termin', '>', $freq)->delete();

        // Tambah termin yang belum ada
        for ($i = 1; $i <= $freq; $i++) {
            if (!$existing->has($i)) {
                $this->schedules()->create(['termin' => $i, 'estimated_date' => null]);
            }
        }
    }
}
