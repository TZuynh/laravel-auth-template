<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('mood', 60)->default('cinematic')->index();
            $table->unsignedSmallInteger('bpm')->nullable()->index();
            $table->decimal('duration_seconds', 8, 3)->nullable();
            $table->string('file_path');
            $table->string('license_type', 60)->default('internal')->index();
            $table->decimal('default_volume', 4, 2)->default(0.35);
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['mood', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};
