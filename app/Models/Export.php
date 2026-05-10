<?php

namespace App\Models;

use App\Enums\AspectRatio;
use App\Enums\ExportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    protected $fillable = [
        'uuid',
        'video_project_id',
        'render_job_id',
        'aspect_ratio',
        'format',
        'resolution_width',
        'resolution_height',
        'duration_seconds',
        'file_path',
        'file_size',
        'checksum',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'aspect_ratio' => AspectRatio::class,
            'resolution_width' => 'integer',
            'resolution_height' => 'integer',
            'duration_seconds' => 'decimal:3',
            'file_size' => 'integer',
            'status' => ExportStatus::class,
            'metadata' => 'array',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function renderJob(): BelongsTo
    {
        return $this->belongsTo(RenderJob::class);
    }
}
