<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_explanations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('driver_used', 32);
            $table->string('model_name')->nullable();
            $table->text('content');
            $table->boolean('is_fallback')->default(false);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_explanations');
    }
};
