<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_staff', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('permission_preset', 64)->default('instructor.lead')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('academic_staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('permission_preset');
        });
    }
};
