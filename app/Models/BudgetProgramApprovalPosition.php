<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

// Konfigurasi jabatan approver step-2 (setelah Keuangan) per organisasi, untuk
// approval edit Program Kerja yang diajukan di luar periode perencanaan.
class BudgetProgramApprovalPosition extends Model
{
    use HasUuids, Auditable;

    protected $fillable = ['organization_id', 'step2_position_id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function step2Position()
    {
        return $this->belongsTo(Position::class, 'step2_position_id');
    }
}
