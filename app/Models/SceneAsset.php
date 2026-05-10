<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneAsset extends Model
{
    protected $fillable = [
        'video_scene_id',
        'product_asset_id',
        'ai_prompt_id',
        'type',
        'provider',
        'path',
        'thumbnail_path',
        'mime_type',
        'width',
        'height',
        'duration_seconds',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssetType::class,
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'decimal:3',
            'status' => AssetStatus::class,
            'metadata' => 'array',
        ];
    }

    public function videoScene(): BelongsTo
    {
        return $this->belongsTo(VideoScene::class);
    }

    public function productAsset(): BelongsTo
    {
        return $this->belongsTo(ProductAsset::class);
    }

    public function aiPrompt(): BelongsTo
    {
        return $this->belongsTo(AiPrompt::class);
    }
}
