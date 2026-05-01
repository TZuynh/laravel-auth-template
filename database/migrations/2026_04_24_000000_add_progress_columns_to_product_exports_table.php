<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_exports', function (Blueprint $table) {
            $table->unsignedInteger('total_rows')->nullable()->after('error_message');
            $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            $table->timestamp('cancelled_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('product_exports', function (Blueprint $table) {
            $table->dropColumn(['total_rows', 'processed_rows', 'cancelled_at']);
        });
    }
};
