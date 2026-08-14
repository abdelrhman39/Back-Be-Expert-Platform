<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_batches', function (Blueprint $table) {
            $table->string('semester_key', 32)->nullable()->after('semester');
            $table->date('start_date')->nullable()->after('semester_key');
            $table->date('end_date')->nullable()->after('start_date');
            $table->unsignedInteger('capacity')->nullable()->after('students_count');
            $table->string('study_mode', 32)->nullable()->after('capacity');
            $table->string('coordinator')->nullable()->after('study_mode');
            $table->boolean('enrollment_open')->default(true)->after('coordinator');
            $table->text('notes')->nullable()->after('enrollment_open');
        });

        Schema::create('academic_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('academic_courses')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('academic_levels')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->string('subtitle')->nullable();
            $table->unsignedInteger('max_capacity')->default(30);
            $table->unsignedInteger('students_count')->default(0);
            $table->string('supervisor')->nullable();
            $table->string('period', 32)->nullable();
            $table->string('semester')->nullable();
            $table->string('semester_key', 32)->nullable();
            $table->string('status', 32)->default('active');
            $table->string('added_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_sections');

        Schema::table('academic_batches', function (Blueprint $table) {
            $table->dropColumn([
                'semester_key',
                'start_date',
                'end_date',
                'capacity',
                'study_mode',
                'coordinator',
                'enrollment_open',
                'notes',
            ]);
        });
    }
};
