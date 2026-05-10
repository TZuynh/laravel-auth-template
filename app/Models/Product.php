<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'image',
        'price',
        'stock',
        'category',
        'brand',
        'tags',
        'featured',
        'synced_to_meta',
        'status',
        'product_form',
        'published_at',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'image' => 'string',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'tags' => 'array',
            'featured' => 'boolean',
            'synced_to_meta' => 'boolean',
            'published_at' => 'date',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ProductAsset::class);
    }

    public function videoProjects(): HasMany
    {
        return $this->hasMany(VideoProject::class);
    }
}
