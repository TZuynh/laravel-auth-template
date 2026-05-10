<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtitles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_scene_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('language', 10)->default('vi')->index();
            $table->string('format', 20)->default('srt')->index();
            $table->longText('content');
            $table->json('timing')->nullable();
            $table->json('style')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['video_project_id', 'language']);
            $table->index(['video_scene_id', 'format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitles');
    }
};
