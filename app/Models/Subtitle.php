<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtitle extends Model
{
    protected $fillable = [
        'video_project_id',
        'video_scene_id',
        'language',
        'format',
        'content',
        'timing',
        'style',
        'file_path',
    ];

    protected function casts(): array
    {
        return [
            'timing' => 'array',
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
