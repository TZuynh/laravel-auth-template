<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'video_project_id',
        'render_job_id',
        'provider',
        'model',
        'operation',
        'units',
        'tokens',
        'render_seconds',
        'gpu_seconds',
        'cost',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
            'tokens' => 'integer',
            'render_seconds' => 'decimal:3',
            'gpu_seconds' => 'decimal:3',
            'cost' => 'decimal:6',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

