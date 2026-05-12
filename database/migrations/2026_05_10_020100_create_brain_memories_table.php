<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brain_memories', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 80)->default('voice_style');
            $table->string('topic', 160)->nullable();
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brain_memories');
    }
};
