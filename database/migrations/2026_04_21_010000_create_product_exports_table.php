<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('search_query')->nullable();
            $table->json('filters')->nullable();
            $table->string('export_format')->default('default');
            $table->string('export_locale')->default('vi');
            $table->json('options')->nullable();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('download_name');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_exports');
    }
};
