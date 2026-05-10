<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('disk', 64)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('type', 50)->index();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('duration_seconds', 10, 3)->nullable();
            $table->string('checksum', 128)->nullable()->index();
            $table->string('provider', 80)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['video_project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};

