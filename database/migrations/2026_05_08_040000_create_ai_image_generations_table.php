<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_image_generations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 80)->default('local')->index();
            $table->string('model', 120)->nullable()->index();
            $table->string('style', 80)->default('cinematic')->index();
            $table->string('aspect_ratio', 10)->default('9:16')->index();
            $table->string('status', 40)->default('completed')->index();
            $table->longText('prompt');
            $table->longText('optimized_prompt')->nullable();
            $table->longText('negative_prompt')->nullable();
            $table->string('image_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'style']);
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_image_generations');
    }
};
