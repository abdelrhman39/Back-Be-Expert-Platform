<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_students', function (Blueprint $table) {
            $table->string('nationality', 32)->nullable()->after('city');
            $table->string('employment_status', 32)->nullable()->after('nationality');
            $table->string('study_period', 32)->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('academic_students', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'employment_status', 'study_period']);
        });
    }
};
