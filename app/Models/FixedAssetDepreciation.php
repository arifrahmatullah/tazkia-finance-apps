<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FixedAssetDepreciation extends Model
{
    use HasUuids;

    protected $fillable = [
        'fixed_asset_id', 'period', 'amount', 'journal_entry_id',
    ];

    protected $casts = [
        'period' => 'date',
        'amount' => 'float',
    ];

    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
