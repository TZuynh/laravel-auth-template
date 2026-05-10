<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transition extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'duration_seconds',
        'ffmpeg_filter',
        'remotion_component',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'decimal:3',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function videoScenes(): HasMany
    {
        return $this->hasMany(VideoScene::class);
    }
}
