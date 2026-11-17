<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->unique()->constrained('test_sessions')->cascadeOnDelete();
            $table->string('method_code', 32)->default('SAW');
            $table->json('ranking');
            $table->string('top_career_code', 64);
            $table->string('top_career_name', 128);
            $table->timestamps();

            $table->index('top_career_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
