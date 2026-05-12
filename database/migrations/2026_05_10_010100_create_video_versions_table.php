<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_versions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_generation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('render_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('style_slug', 80)->index();
            $table->string('style_name', 120);
            $table->string('platform', 80)->default('shorts')->index();
            $table->string('aspect_ratio', 10)->default('9:16')->index();
            $table->decimal('duration_seconds', 8, 3)->default(15.000);
            $table->string('voice', 80)->nullable();
            $table->string('music', 120)->nullable();
            $table->string('subtitle_style', 120)->nullable();
            $table->string('pacing', 80)->nullable();
            $table->text('visual_direction')->nullable();
            $table->json('style_payload')->nullable();
            $table->json('timeline_json')->nullable();
            $table->string('output_url')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['video_generation_id', 'style_slug']);
            $table->index(['video_generation_id', 'status']);
            $table->index(['style_slug', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_versions');
    }
};
