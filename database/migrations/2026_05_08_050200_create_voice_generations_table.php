<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_generations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_scene_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('voice_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 80)->default('xtts')->index();
            $table->string('status', 40)->default('queued')->index();
            $table->string('language', 10)->default('vi')->index();
            $table->string('voice', 120)->nullable();
            $table->text('text');
            $table->string('audio_path')->nullable();
            $table->decimal('duration_seconds', 10, 3)->nullable();
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_generations');
    }
};

