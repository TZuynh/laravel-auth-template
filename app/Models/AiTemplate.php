<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'language',
        'tone',
        'style',
        'platform',
        'system_prompt',
        'script_prompt_template',
        'image_prompt_template',
        'video_prompt_template',
        'voice_prompt_template',
        'default_scene_structure',
        'default_settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_scene_structure' => 'array',
            'default_settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }
}
