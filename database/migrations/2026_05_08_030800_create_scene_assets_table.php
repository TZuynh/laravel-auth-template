<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scene_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_scene_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_prompt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50)->index();
            $table->string('provider', 80)->nullable()->index();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('duration_seconds', 8, 3)->nullable();
            $table->string('status', 40)->default('processing')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['video_scene_id', 'type']);
            $table->index(['video_scene_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scene_assets');
    }
};
