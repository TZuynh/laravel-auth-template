<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 80)->default('starter')->index();
            $table->string('status', 40)->default('active')->index();
            $table->unsignedInteger('monthly_render_minutes')->default(30);
            $table->unsignedInteger('monthly_gpu_minutes')->default(0);
            $table->unsignedBigInteger('monthly_storage_mb')->default(1024);
            $table->string('billing_provider', 80)->nullable();
            $table->string('billing_customer_id')->nullable()->index();
            $table->string('billing_subscription_id')->nullable()->index();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

