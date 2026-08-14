<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->string('attempt_policy', 24)->default('single')->after('max_attempts');
            $table->string('grade_selection', 24)->default('highest')->after('attempt_policy');

            $table->index(['attempt_policy', 'grade_selection']);
        });

        DB::table('exams')
            ->where('max_attempts', '>', 1)
            ->update(['attempt_policy' => 'limited']);
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->dropIndex(['attempt_policy', 'grade_selection']);
            $table->dropColumn(['attempt_policy', 'grade_selection']);
        });
    }
};
