<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('academic_sections')->cascadeOnDelete();
            $table->foreignId('attendance_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scope', 16)->default('section');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->timestamp('due_at')->nullable();
            $table->boolean('allow_late_submission')->default(true);
            $table->unsignedTinyInteger('late_penalty_percent')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->unsignedTinyInteger('max_files')->default(5);
            $table->boolean('allow_text_submission')->default(true);
            $table->string('status', 16)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['section_id', 'status']);
            $table->index(['attendance_session_id', 'status']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->text('body_text')->nullable();
            $table->string('submission_url', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 16)->default('draft');
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'assignment_submissions_unique');
            $table->index(['assignment_id', 'status']);
        });

        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_submission_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
