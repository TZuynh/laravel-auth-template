<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->index();
            $table->string('status', 40)->default('ready')->index();
            $table->string('provider', 80)->nullable()->index();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->decimal('duration_seconds', 8, 3)->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'type']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_assets');
    }
};
