<?php

namespace App\Models;

use App\Enums\RenderJobStatus;
use App\Enums\RenderJobType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RenderJob extends Model
{
    protected $fillable = [
        'uuid',
        'video_project_id',
        'video_scene_id',
        'export_id',
        'type',
        'status',
        'queue',
        'provider',
        'progress',
        'attempts',
        'max_attempts',
        'current_step',
        'input_payload',
        'output_payload',
        'error_message',
        'available_at',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => RenderJobType::class,
            'status' => RenderJobStatus::class,
            'progress' => 'integer',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'input_payload' => 'array',
            'output_payload' => 'array',
            'available_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function videoScene(): BelongsTo
    {
        return $this->belongsTo(VideoScene::class);
    }

    public function export(): BelongsTo
    {
        return $this->belongsTo(Export::class);
    }
}
