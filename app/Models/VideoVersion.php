<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoVersion extends Model
{
    protected $fillable = [
        'uuid',
        'video_generation_id',
        'video_project_id',
        'render_job_id',
        'title',
        'style_slug',
        'style_name',
        'platform',
        'aspect_ratio',
        'duration_seconds',
        'voice',
        'music',
        'subtitle_style',
        'pacing',
        'visual_direction',
        'style_payload',
        'timeline_json',
        'output_url',
        'status',
        'progress',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'decimal:3',
            'style_payload' => 'array',
            'timeline_json' => 'array',
            'progress' => 'integer',
        ];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(VideoGeneration::class, 'video_generation_id');
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function renderJob(): BelongsTo
    {
        return $this->belongsTo(RenderJob::class);
    }

    public function voiceTracks(): HasMany
    {
        return $this->hasMany(VoiceTrack::class);
    }

    public function subtitleTracks(): HasMany
    {
        return $this->hasMany(SubtitleTrack::class);
    }
}
