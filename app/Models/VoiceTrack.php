<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceTrack extends Model
{
    protected $fillable = [
        'video_generation_id',
        'video_version_id',
        'video_project_id',
        'video_scene_id',
        'provider',
        'status',
        'language',
        'voice',
        'text',
        'audio_path',
        'duration_seconds',
        'metadata',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'decimal:3',
            'metadata' => 'array',
        ];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(VideoGeneration::class, 'video_generation_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VideoVersion::class, 'video_version_id');
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function videoScene(): BelongsTo
    {
        return $this->belongsTo(VideoScene::class);
    }
}
