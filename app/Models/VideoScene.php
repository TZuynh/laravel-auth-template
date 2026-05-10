<?php

namespace App\Models;

use App\Enums\VideoSceneStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoScene extends Model
{
    protected $fillable = [
        'video_project_id',
        'transition_id',
        'sort_order',
        'title',
        'cinematic_description',
        'voice_over_text',
        'subtitle_text',
        'duration_seconds',
        'camera_movement',
        'animation_style',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'duration_seconds' => 'decimal:3',
            'status' => VideoSceneStatus::class,
            'metadata' => 'array',
        ];
    }

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function transition(): BelongsTo
    {
        return $this->belongsTo(Transition::class);
    }

    public function aiPrompts(): HasMany
    {
        return $this->hasMany(AiPrompt::class);
    }

    public function sceneAssets(): HasMany
    {
        return $this->hasMany(SceneAsset::class);
    }

    public function subtitles(): HasMany
    {
        return $this->hasMany(Subtitle::class);
    }

    public function renderJobs(): HasMany
    {
        return $this->hasMany(RenderJob::class);
    }
}
