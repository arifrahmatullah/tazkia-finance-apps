<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

// Override nominal satu rincian kegiatan KHUSUS untuk satu termin -- dipakai saat Keuangan
// perlu menggeser plafon antar termin (mis. termin 1 dikurangi, termin 2 ditambah) untuk
// rincian yang sama, tanpa mengubah unit_price default rincian itu di termin-termin lain.
class BudgetProgramScheduleDetail extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'budget_program_schedule_id', 'budget_program_detail_id', 'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function schedule()
    {
        return $this->belongsTo(BudgetProgramSchedule::class, 'budget_program_schedule_id');
    }

    public function detail()
    {
        return $this->belongsTo(BudgetProgramDetail::class, 'budget_program_detail_id');
    }
}
