<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_scene_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50)->index();
            $table->string('provider', 80)->nullable()->index();
            $table->string('model', 120)->nullable()->index();
            $table->longText('prompt');
            $table->longText('negative_prompt')->nullable();
            $table->json('response')->nullable();
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->string('status', 40)->default('draft')->index();
            $table->timestamps();

            $table->index(['video_project_id', 'type']);
            $table->index(['video_scene_id', 'type']);
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
    }
};
