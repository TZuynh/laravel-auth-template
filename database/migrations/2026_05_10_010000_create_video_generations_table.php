<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_generations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('prompt');
            $table->string('language', 10)->default('en')->index();
            $table->string('aspect_ratio', 10)->default('9:16')->index();
            $table->unsignedSmallInteger('duration_seconds')->default(30);
            $table->string('provider', 80)->default('auto')->index();
            $table->string('render_provider', 80)->default('ffmpeg')->index();
            $table->string('status', 40)->default('draft')->index();
            $table->unsignedTinyInteger('requested_versions')->default(5);
            $table->unsignedTinyInteger('completed_versions')->default(0);
            $table->unsignedTinyInteger('failed_versions')->default(0);
            $table->json('settings')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['render_provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_generations');
    }
};
