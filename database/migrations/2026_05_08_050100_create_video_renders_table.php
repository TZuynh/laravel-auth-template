<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_renders', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('render_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('renderer', 80)->default('ffmpeg')->index();
            $table->string('status', 40)->default('queued')->index();
            $table->string('aspect_ratio', 20)->default('9:16');
            $table->unsignedInteger('width')->default(1080);
            $table->unsignedInteger('height')->default(1920);
            $table->unsignedTinyInteger('fps')->default(30);
            $table->decimal('duration_seconds', 10, 3)->nullable();
            $table->string('output_path')->nullable();
            $table->json('timeline')->nullable();
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_renders');
    }
};

