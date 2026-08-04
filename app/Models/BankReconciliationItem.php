<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BankReconciliationItem extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'bank_reconciliation_id', 'side', 'description', 'amount',
        'counter_account_id', 'journal_entry_id', 'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function bankReconciliation()
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function counterAccount()
    {
        return $this->belongsTo(Account::class, 'counter_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPosted(): bool
    {
        return $this->journal_entry_id !== null;
    }
}
