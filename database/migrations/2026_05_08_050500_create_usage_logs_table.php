<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('video_project_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('render_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 80)->index();
            $table->string('model', 120)->nullable()->index();
            $table->string('operation', 80)->index();
            $table->unsignedInteger('units')->default(1);
            $table->unsignedInteger('tokens')->default(0);
            $table->decimal('render_seconds', 12, 3)->default(0);
            $table->decimal('gpu_seconds', 12, 3)->default(0);
            $table->decimal('cost', 12, 6)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'operation']);
            $table->index(['provider', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};

