<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 60)->default('bulk_video_style')->index();
            $table->string('platform', 80)->default('shorts')->index();
            $table->string('style', 80)->index();
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['style', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
