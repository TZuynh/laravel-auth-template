<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('render_jobs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_scene_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('export_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50)->index();
            $table->string('status', 40)->default('queued')->index();
            $table->string('queue', 80)->default('render')->index();
            $table->string('provider', 80)->nullable()->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(3);
            $table->string('current_step')->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['video_project_id', 'type', 'status']);
            $table->index(['queue', 'status', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('render_jobs');
    }
};
