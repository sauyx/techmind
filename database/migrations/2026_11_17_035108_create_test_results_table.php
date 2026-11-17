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
            $table->foreignId('test_session_id')->constrained('test_sessions')->cascadeOnDelete();
            $table->foreignId('career_id')->constrained('careers')->restrictOnDelete();
            $table->string('career_code', 64);
            $table->string('career_name', 128);
            $table->unsignedTinyInteger('rank');
            $table->decimal('saw_score', 8, 6);
            $table->decimal('match_percentage', 5, 1);
            $table->json('contribution_breakdown');
            $table->json('gap_summary')->nullable();
            $table->timestamps();

            $table->index(['test_session_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
