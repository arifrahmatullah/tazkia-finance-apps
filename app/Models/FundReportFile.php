<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FundReportFile extends Model
{
    use HasUuids, Auditable;

    protected $fillable = [
        'fund_report_id', 'uploaded_by',
        'file_path', 'file_name', 'mime_type', 'file_size',
    ];

    public function fundReport()
    {
        return $this->belongsTo(FundReport::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeLabelAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
