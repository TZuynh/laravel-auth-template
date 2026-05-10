<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('render_job_id')->nullable()->index();
            $table->string('aspect_ratio', 10)->default('9:16')->index();
            $table->string('format', 20)->default('mp4')->index();
            $table->unsignedInteger('resolution_width')->default(1080);
            $table->unsignedInteger('resolution_height')->default(1920);
            $table->decimal('duration_seconds', 8, 3)->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum', 128)->nullable()->index();
            $table->string('status', 40)->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['video_project_id', 'status']);
            $table->index(['aspect_ratio', 'format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
