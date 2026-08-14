<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('academic_sections')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->string('academic_id', 64)->nullable();
            $table->string('student_name');
            $table->string('status', 20)->default('eligible');
            $table->timestamp('assigned_at');
            $table->timestamp('excluded_at')->nullable();
            $table->text('exclusion_reason')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'status']);
        });

        $publishedExams = DB::table('exams')->where('status', 'published')->get(['id', 'section_id']);

        foreach ($publishedExams as $exam) {
            $students = DB::table('academic_students')
                ->where('section_id', $exam->section_id)
                ->get(['id', 'user_id', 'section_id', 'batch_id', 'academic_id', 'name_ar']);

            foreach ($students as $student) {
                DB::table('exam_candidates')->insert([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'user_id' => $student->user_id,
                    'section_id' => $student->section_id,
                    'batch_id' => $student->batch_id,
                    'academic_id' => $student->academic_id,
                    'student_name' => $student->name_ar,
                    'status' => 'eligible',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_candidates');
    }
};
