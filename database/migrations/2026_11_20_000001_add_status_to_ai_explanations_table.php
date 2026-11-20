<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_explanations', function (Blueprint $table) {
            $table->string('status', 16)->default('success')->after('driver_used');
            $table->unsignedTinyInteger('attempts')->default(1)->after('status');
            $table->text('error_message')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('ai_explanations', function (Blueprint $table) {
            $table->dropColumn(['status', 'attempts', 'error_message']);
        });
    }
};
