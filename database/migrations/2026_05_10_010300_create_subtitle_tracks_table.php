<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtitle_tracks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_generation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_version_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_scene_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('provider', 80)->default('python-worker')->index();
            $table->string('status', 40)->default('queued')->index();
            $table->string('language', 10)->default('en')->index();
            $table->string('format', 20)->default('srt');
            $table->longText('content');
            $table->string('subtitle_path')->nullable();
            $table->json('word_timings')->nullable();
            $table->json('style')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['video_version_id', 'status']);
            $table->index(['video_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitle_tracks');
    }
};
