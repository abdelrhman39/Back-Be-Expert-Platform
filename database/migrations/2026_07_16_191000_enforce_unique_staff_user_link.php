<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateLinks = DB::table('academic_staff')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateLinks) {
            throw new RuntimeException('Duplicate instructor account links must be resolved before migration.');
        }

        Schema::table('academic_staff', function (Blueprint $table) {
            $table->unique('user_id', 'academic_staff_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('academic_staff', function (Blueprint $table) {
            $table->dropUnique('academic_staff_user_id_unique');
        });
    }
};
