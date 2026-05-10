<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sidebar_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index();
            $table->string('section_label_key');
            $table->string('section_icon')->default('circle');
            $table->unsignedSmallInteger('section_sort_order')->default(0)->index();
            $table->string('label_key');
            $table->string('route');
            $table->json('active_patterns')->nullable();
            $table->string('icon')->default('circle');
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_admin_only')->default(false)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        $items = collect(config('sidebar.items', []))
            ->map(fn (array $item): array => [
                'section' => $item['section'],
                'section_label_key' => $item['section_label_key'],
                'section_icon' => $item['section_icon'] ?? 'circle',
                'section_sort_order' => $item['section_sort_order'] ?? 0,
                'label_key' => $item['label_key'],
                'route' => $item['route'],
                'active_patterns' => json_encode($item['active_patterns'] ?? [], JSON_THROW_ON_ERROR),
                'icon' => $item['icon'] ?? 'circle',
                'sort_order' => $item['sort_order'] ?? 0,
                'is_admin_only' => (bool) ($item['is_admin_only'] ?? false),
                'is_enabled' => (bool) ($item['is_enabled'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($items !== []) {
            DB::table('sidebar_menu_items')->insert($items);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_menu_items');
    }
};
