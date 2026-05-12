<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentAiDraft extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'product_id',
        'platform',
        'title',
        'status',
        'tone',
        'audience',
        'include_emoji',
        'include_hashtags',
        'prompt',
        'content',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'include_emoji' => 'boolean',
            'include_hashtags' => 'boolean',
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
}
