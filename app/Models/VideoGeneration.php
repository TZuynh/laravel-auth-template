<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoGeneration extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'prompt',
        'language',
        'aspect_ratio',
        'duration_seconds',
        'provider',
        'render_provider',
        'status',
        'requested_versions',
        'completed_versions',
        'failed_versions',
        'settings',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'requested_versions' => 'integer',
            'completed_versions' => 'integer',
            'failed_versions' => 'integer',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(VideoVersion::class)->orderBy('id');
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
