<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'status',
        'monthly_render_minutes',
        'monthly_gpu_minutes',
        'monthly_storage_mb',
        'billing_provider',
        'billing_customer_id',
        'billing_subscription_id',
        'renews_at',
        'ends_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'monthly_render_minutes' => 'integer',
            'monthly_gpu_minutes' => 'integer',
            'monthly_storage_mb' => 'integer',
            'renews_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

