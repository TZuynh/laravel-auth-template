<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('language', 10)->default('vi')->index();
            $table->string('tone', 40)->default('cinematic')->index();
            $table->string('style', 60)->default('product_showcase')->index();
            $table->string('platform', 40)->default('tiktok')->index();
            $table->text('system_prompt');
            $table->text('script_prompt_template')->nullable();
            $table->text('image_prompt_template')->nullable();
            $table->text('video_prompt_template')->nullable();
            $table->text('voice_prompt_template')->nullable();
            $table->json('default_scene_structure')->nullable();
            $table->json('default_settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['style', 'platform']);
            $table->index(['language', 'tone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_templates');
    }
};
