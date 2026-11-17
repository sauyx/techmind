<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_session_id')->constrained('test_sessions')->cascadeOnDelete();
            $table->foreignId('criterion_id')->nullable()->constrained('criteria')->nullOnDelete();
            $table->string('criterion_code', 64);
            $table->string('criterion_name', 128);
            $table->unsignedTinyInteger('rating');
            $table->decimal('weight_snapshot', 5, 2)->default(1.00);
            $table->timestamp('created_at')->nullable();

            $table->unique(['test_session_id', 'criterion_code']);
            $table->index('test_session_id');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE test_answers ADD CONSTRAINT test_answers_rating_range CHECK (rating BETWEEN 1 AND 5)'
            );
        }
        if ($driver === 'sqlite') {
            DB::statement(
                "CREATE TRIGGER test_answers_rating_check_insert BEFORE INSERT ON test_answers
                BEGIN
                    SELECT CASE WHEN NEW.rating < 1 OR NEW.rating > 5 THEN RAISE(ABORT, 'rating out of range') END;
                END;"
            );
            DB::statement(
                "CREATE TRIGGER test_answers_rating_check_update BEFORE UPDATE ON test_answers
                BEGIN
                    SELECT CASE WHEN NEW.rating < 1 OR NEW.rating > 5 THEN RAISE(ABORT, 'rating out of range') END;
                END;"
            );
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS test_answers_rating_check_insert');
            DB::statement('DROP TRIGGER IF EXISTS test_answers_rating_check_update');
        }
        Schema::dropIfExists('test_answers');
    }
};
