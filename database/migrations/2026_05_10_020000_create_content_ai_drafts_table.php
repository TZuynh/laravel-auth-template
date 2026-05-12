<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_ai_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 40)->default('facebook');
            $table->string('title')->nullable();
            $table->string('status', 40)->default('completed');
            $table->string('tone', 80)->default('expert');
            $table->string('audience', 160)->nullable();
            $table->boolean('include_emoji')->default(true);
            $table->boolean('include_hashtags')->default(true);
            $table->text('prompt')->nullable();
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_ai_drafts');
    }
};
