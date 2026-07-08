<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BudgetProgramSchedule extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'budget_program_id', 'termin', 'estimated_date', 'notes',
    ];

    protected $casts = [
        'estimated_date' => 'date',
    ];

    public function budgetProgram()
    {
        return $this->belongsTo(BudgetProgram::class);
    }
}
