<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('sidebar_menu_items')) {
            return;
        }

        $now = now();
        $marketingDefaults = [
            'section' => 'marketing',
            'section_label_key' => 'messages.erp.sidebar.marketing_group',
            'section_icon' => 'megaphone',
            'section_sort_order' => 25,
            'is_admin_only' => true,
            'is_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('sidebar_menu_items')
            ->where('section', 'marketing')
            ->where(function ($query): void {
                $query->where('label_key', 'messages.erp.sidebar.ai_video')
                    ->orWhere('route', 'marketing.index');
            })
            ->update([
                'label_key' => 'messages.erp.sidebar.content_ai',
                'route' => 'marketing.content.index',
                'active_patterns' => json_encode(['marketing.index', 'marketing.content.*'], JSON_THROW_ON_ERROR),
                'icon' => 'pen',
                'sort_order' => 10,
                'is_enabled' => true,
                'updated_at' => $now,
            ]);

        DB::table('sidebar_menu_items')->updateOrInsert(
            ['route' => 'marketing.content.index'],
            array_replace($marketingDefaults, [
                'label_key' => 'messages.erp.sidebar.content_ai',
                'active_patterns' => json_encode(['marketing.index', 'marketing.content.*'], JSON_THROW_ON_ERROR),
                'icon' => 'pen',
                'sort_order' => 10,
                'updated_at' => $now,
            ])
        );

        DB::table('sidebar_menu_items')->updateOrInsert(
            ['route' => 'marketing.images'],
            array_replace($marketingDefaults, [
                'label_key' => 'messages.erp.sidebar.ai_images',
                'active_patterns' => json_encode(['marketing.images*'], JSON_THROW_ON_ERROR),
                'icon' => 'image',
                'sort_order' => 20,
                'updated_at' => $now,
            ])
        );

        DB::table('sidebar_menu_items')->updateOrInsert(
            ['route' => 'marketing.brain.index'],
            array_replace($marketingDefaults, [
                'label_key' => 'messages.erp.sidebar.brain_ai',
                'active_patterns' => json_encode(['marketing.brain.*'], JSON_THROW_ON_ERROR),
                'icon' => 'database',
                'sort_order' => 30,
                'updated_at' => $now,
            ])
        );

        DB::table('sidebar_menu_items')
            ->where('section', 'marketing')
            ->whereIn('route', [
                'marketing.scenes',
                'marketing.render-history',
                'marketing.exports',
                'marketing.templates',
            ])
            ->update([
                'is_enabled' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('sidebar_menu_items')) {
            return;
        }

        DB::table('sidebar_menu_items')
            ->where('route', 'marketing.content.index')
            ->update([
                'label_key' => 'messages.erp.sidebar.ai_video',
                'route' => 'marketing.index',
                'active_patterns' => json_encode(['marketing.index'], JSON_THROW_ON_ERROR),
                'icon' => 'video',
                'updated_at' => now(),
            ]);
    }
};
