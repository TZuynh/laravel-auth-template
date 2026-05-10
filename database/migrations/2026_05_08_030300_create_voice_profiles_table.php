<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 80)->default('elevenlabs')->index();
            $table->string('provider_voice_id')->nullable()->index();
            $table->string('name');
            $table->string('gender', 30)->default('neutral')->index();
            $table->string('language', 10)->default('vi')->index();
            $table->string('tone', 50)->default('premium')->index();
            $table->string('sample_path')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['language', 'gender']);
            $table->index(['provider', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_profiles');
    }
};
