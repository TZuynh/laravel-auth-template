<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_projects', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('voice_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('music_track_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('language', 10)->default('vi')->index();
            $table->string('tone', 50)->default('premium')->index();
            $table->string('style', 60)->default('cinematic')->index();
            $table->string('aspect_ratio', 10)->default('9:16')->index();
            $table->decimal('duration_seconds', 8, 3)->default(8.000);
            $table->string('ai_model', 120)->nullable()->index();
            $table->longText('prompt')->nullable();
            $table->longText('optimized_prompt')->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['product_id', 'created_at']);
            $table->index(['style', 'aspect_ratio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_projects');
    }
};
