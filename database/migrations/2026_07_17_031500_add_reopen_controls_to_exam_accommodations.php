<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_accommodations', function (Blueprint $table) {
            $table->boolean('unlimited_attempts')->default(false)->after('extra_attempts');
            $table->boolean('override_exam_availability')->default(false)->after('unlimited_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('exam_accommodations', function (Blueprint $table) {
            $table->dropColumn(['unlimited_attempts', 'override_exam_availability']);
        });
    }
};
