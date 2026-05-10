<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exports', function (Blueprint $table): void {
            $table->foreign('render_job_id')
                ->references('id')
                ->on('render_jobs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exports', function (Blueprint $table): void {
            $table->dropForeign(['render_job_id']);
        });
    }
};
