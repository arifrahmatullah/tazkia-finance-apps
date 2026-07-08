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

    protected static function booted(): void
    {
        static::saving(function (self $detail) {
            $detail->total_amount = round($detail->quantity * $detail->unit_price, 2);
        });
    }
}
