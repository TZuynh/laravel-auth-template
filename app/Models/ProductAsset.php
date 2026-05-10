<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAsset extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'status',
        'provider',
        'path',
        'thumbnail_path',
        'mime_type',
        'width',
        'height',
        'duration_seconds',
        'is_primary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssetType::class,
            'status' => AssetStatus::class,
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'decimal:3',
            'is_primary' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sceneAssets(): HasMany
    {
        return $this->hasMany(SceneAsset::class);
    }
}
