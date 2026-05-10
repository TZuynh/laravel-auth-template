<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceGeneration extends Model
{
    protected $fillable = [
        'video_project_id',
        'video_scene_id',
        'voice_profile_id',
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

    public function videoProject(): BelongsTo
    {
        return $this->belongsTo(VideoProject::class);
    }

    public function videoScene(): BelongsTo
    {
        return $this->belongsTo(VideoScene::class);
    }

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }
}

