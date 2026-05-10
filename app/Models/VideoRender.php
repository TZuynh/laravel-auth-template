<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoRender extends Model
{
    protected $fillable = [
        'uuid',
        'video_project_id',
        'render_job_id',
        'renderer',
        'status',
        'aspect_ratio',
        'width',
        'height',
        'fps',
        'duration_seconds',
        'output_path',
        'timeline',
        'metadata',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'fps' => 'integer',
            'duration_seconds' => 'decimal:3',
            'timeline' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
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

