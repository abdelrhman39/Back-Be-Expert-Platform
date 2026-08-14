<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_students', function (Blueprint $table) {
            $table->string('academic_status', 32)->default('studying')->after('study_status');
            $table->timestamp('graduated_at')->nullable()->after('academic_status');
        });

        Schema::create('academic_staff', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('role', 64)->default('instructor');
            $table->string('specialty')->nullable();
            $table->string('gender', 16)->nullable();
            $table->unsignedSmallInteger('courses_count')->default(0);
            $table->unsignedSmallInteger('hours_per_week')->default(0);
            $table->decimal('compensation_total', 12, 2)->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_staff');

        Schema::table('academic_students', function (Blueprint $table) {
            $table->dropColumn(['academic_status', 'graduated_at']);
        });
    }
};
