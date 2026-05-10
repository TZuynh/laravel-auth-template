<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiImageGeneration extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'provider',
        'model',
        'style',
        'aspect_ratio',
        'status',
        'prompt',
        'optimized_prompt',
        'negative_prompt',
        'image_path',
        'thumbnail_path',
        'mime_type',
        'width',
        'height',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }
}
