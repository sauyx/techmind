<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained('careers')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['career_id', 'criterion_id']);
            $table->index('criterion_id');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE career_profiles ADD CONSTRAINT career_profiles_score_range CHECK (score BETWEEN 1 AND 5)'
            );
        }

        if ($driver === 'sqlite') {
            DB::statement(
                "CREATE TRIGGER career_profiles_score_check_insert BEFORE INSERT ON career_profiles
                BEGIN
                    SELECT CASE WHEN NEW.score < 1 OR NEW.score > 5 THEN RAISE(ABORT, 'score out of range') END;
                END;"
            );
            DB::statement(
                "CREATE TRIGGER career_profiles_score_check_update BEFORE UPDATE ON career_profiles
                BEGIN
                    SELECT CASE WHEN NEW.score < 1 OR NEW.score > 5 THEN RAISE(ABORT, 'score out of range') END;
                END;"
            );
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS career_profiles_score_check_insert');
            DB::statement('DROP TRIGGER IF EXISTS career_profiles_score_check_update');
        }

        Schema::dropIfExists('career_profiles');
    }
};
