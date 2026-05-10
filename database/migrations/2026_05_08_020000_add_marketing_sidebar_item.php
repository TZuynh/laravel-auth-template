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

        DB::table('sidebar_menu_items')->updateOrInsert(
            ['route' => 'marketing.index'],
            [
                'section' => 'marketing',
                'section_label_key' => 'messages.erp.sidebar.marketing_group',
                'section_icon' => 'megaphone',
                'section_sort_order' => 25,
                'label_key' => 'messages.erp.sidebar.ai_video',
                'active_patterns' => json_encode(['marketing.*'], JSON_THROW_ON_ERROR),
                'icon' => 'video',
                'sort_order' => 10,
                'is_admin_only' => true,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('sidebar_menu_items')) {
            return;
        }

        DB::table('sidebar_menu_items')
            ->where('route', 'marketing.index')
            ->delete();
    }
};
