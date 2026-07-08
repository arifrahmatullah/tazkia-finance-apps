<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'organization_id', 'entry_date', 'reference', 'description',
        'status', 'created_by', 'posted_at', 'posted_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at'  => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function getTotalDebitAttribute(): float
    {
        return $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return $this->lines->sum('credit');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public static function generateReference(string $orgId, string $date): string
    {
        $year  = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $prefix = "JU-{$year}{$month}-";

        $last = self::where('organization_id', $orgId)
            ->where('reference', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $seq = $last ? (intval(substr($last->reference, strlen($prefix))) + 1) : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
