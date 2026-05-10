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

        DB::table('sidebar_menu_items')
            ->where('route', 'erp.bom')
            ->update([
                'is_enabled' => false,
                'updated_at' => now(),
            ]);

        DB::table('sidebar_menu_items')->updateOrInsert(
            ['route' => 'roles.index'],
            [
                'section' => 'system',
                'section_label_key' => 'messages.erp.sidebar.system_group',
                'section_icon' => 'settings',
                'section_sort_order' => 30,
                'label_key' => 'messages.erp.sidebar.roles',
                'active_patterns' => json_encode(['roles.*'], JSON_THROW_ON_ERROR),
                'icon' => 'shield',
                'sort_order' => 15,
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
            ->where('route', 'erp.bom')
            ->update([
                'is_enabled' => true,
                'updated_at' => now(),
            ]);

        DB::table('sidebar_menu_items')
            ->where('route', 'roles.index')
            ->delete();
    }
};
