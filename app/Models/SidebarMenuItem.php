<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarMenuItem extends Model
{
    protected $fillable = [
        'section',
        'section_label_key',
        'section_icon',
        'section_sort_order',
        'label_key',
        'route',
        'active_patterns',
        'icon',
        'sort_order',
        'is_admin_only',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'active_patterns' => 'array',
            'section_sort_order' => 'integer',
            'sort_order' => 'integer',
            'is_admin_only' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }
}
