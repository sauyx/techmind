<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_explanations', function (Blueprint $table) {
            $table->string('format', 8)->default('text')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ai_explanations', function (Blueprint $table) {
            $table->dropColumn('format');
        });
    }
};
