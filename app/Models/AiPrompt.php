<?php

namespace App\Models;

use App\Enums\AiPromptStatus;
use App\Enums\AiPromptType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiPrompt extends Model
{
    protected $fillable = [
        'video_project_id',
        'video_scene_id',
        'type',
        'provider',
        'model',
        'prompt',
        'negative_prompt',
        'response',
        'tokens_used',
        'cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => AiPromptType::class,
            'response' => 'array',
            'tokens_used' => 'integer',
            'cost' => 'decimal:4',
            'status' => AiPromptStatus::class,
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

    public function sceneAssets(): HasMany
    {
        return $this->hasMany(SceneAsset::class);
    }
}
