<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubtitleGeneration extends Model
{
    protected $fillable = [
        'video_project_id',
        'video_scene_id',
        'provider',
        'status',
        'language',
        'format',
        'content',
        'subtitle_path',
        'word_timings',
        'style',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'word_timings' => 'array',
            'style' => 'array',
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
}

