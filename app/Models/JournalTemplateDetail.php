<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JournalTemplateDetail extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'journal_template_id', 'account_id', 'balance_type', 'description', 'sequence',
    ];

    public function journalTemplate()
    {
        return $this->belongsTo(JournalTemplate::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function isDebit(): bool  { return $this->balance_type === 'debit'; }
    public function isCredit(): bool { return $this->balance_type === 'credit'; }
}
