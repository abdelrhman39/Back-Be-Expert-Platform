<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('academic_sections')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('academic_schedules')->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('session_date');
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->string('status', 32)->default('completed');
            $table->string('source', 32)->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'session_date']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->string('status', 32)->default('absent');
            $table->string('source', 32)->default('manual');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('attendance_sessions');
    }
};
