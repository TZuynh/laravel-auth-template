<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_scenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transition_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('title');
            $table->text('cinematic_description')->nullable();
            $table->text('voice_over_text')->nullable();
            $table->text('subtitle_text')->nullable();
            $table->decimal('duration_seconds', 8, 3)->default(2.000);
            $table->string('camera_movement', 80)->default('dolly_in')->index();
            $table->string('animation_style', 80)->default('cinematic_parallax')->index();
            $table->string('status', 40)->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['video_project_id', 'sort_order']);
            $table->index(['video_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_scenes');
    }
};
