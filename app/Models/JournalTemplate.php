<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JournalTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id', 'code', 'name', 'category', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function details()
    {
        return $this->hasMany(JournalTemplateDetail::class)->orderBy('sequence');
    }
}
