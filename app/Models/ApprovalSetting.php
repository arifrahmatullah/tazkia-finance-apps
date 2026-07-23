<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApprovalSetting extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'organization_id', 'requester_position_id', 'approver_position_id',
        'step', 'max_amount', 'is_active',
    ];

    protected $casts = [
        'max_amount' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function requesterPosition()
    {
        return $this->belongsTo(Position::class, 'requester_position_id');
    }

    public function approverPosition()
    {
        return $this->belongsTo(Position::class, 'approver_position_id');
    }

    public static function getChainFor(string $orgId, string $positionId, float $amount): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('organization_id', $orgId)
            ->where('requester_position_id', $positionId)
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount))
            ->orderBy('step')
            ->get();
    }
}
