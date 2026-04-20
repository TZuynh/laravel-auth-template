<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('sku')->unique();
            $table->decimal('price', 15, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('category')->nullable()->index();
            $table->string('brand')->nullable()->index();
            $table->json('tags')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('synced_to_meta')->default(false);
            $table->string('status')->default('active')->index();
            $table->string('product_form')->nullable();
            $table->date('published_at')->nullable()->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
