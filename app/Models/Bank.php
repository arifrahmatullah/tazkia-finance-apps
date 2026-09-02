<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'name', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
