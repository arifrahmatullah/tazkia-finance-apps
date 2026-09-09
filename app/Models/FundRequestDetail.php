<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FundRequestDetail extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'fund_request_id', 'budget_program_detail_id', 'account_id',
        'description', 'quantity', 'unit', 'ceiling_unit_price', 'unit_price', 'total_amount',
    ];

    protected $casts = [
        'quantity'           => 'decimal:2',
        'ceiling_unit_price' => 'decimal:2',
        'unit_price'         => 'decimal:2',
        'total_amount'       => 'decimal:2',
    ];

    public function fundRequest()
    {
        return $this->belongsTo(FundRequest::class);
    }

    public function budgetProgramDetail()
    {
        return $this->belongsTo(BudgetProgramDetail::class);
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
