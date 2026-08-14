<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_question_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('exam_question_categories')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('academic_courses')->nullOnDelete();
            $table->string('name');
            $table->string('code', 80)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['course_id', 'is_active']);
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('exam_question_categories')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('academic_courses')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 32);
            $table->string('title')->nullable();
            $table->longText('prompt');
            $table->longText('explanation')->nullable();
            $table->decimal('default_points', 8, 2)->default(1);
            $table->string('difficulty', 16)->default('medium');
            $table->string('scope', 16)->default('course');
            $table->string('status', 16)->default('draft');
            $table->json('answer_key')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['course_id', 'status', 'type']);
            $table->index(['category_id', 'difficulty']);
            $table->index(['created_by', 'scope']);
        });

        Schema::create('exam_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->string('option_key', 80);
            $table->longText('content');
            $table->boolean('is_correct')->default(false);
            $table->decimal('weight', 8, 4)->default(0);
            $table->text('feedback')->nullable();
            $table->json('match_data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['question_id', 'option_key']);
            $table->index(['question_id', 'sort_order']);
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('academic_sections')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('academic_courses')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('instructions')->nullable();
            $table->string('type', 24)->default('exam');
            $table->string('status', 20)->default('draft');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->decimal('total_points', 10, 2)->default(0);
            $table->decimal('passing_percent', 5, 2)->default(60);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('one_question_per_page')->default(false);
            $table->boolean('allow_back_navigation')->default(true);
            $table->boolean('require_access_code')->default(false);
            $table->string('access_code_hash')->nullable();
            $table->string('result_release', 24)->default('after_grading');
            $table->string('review_policy', 24)->default('score_only');
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['section_id', 'status', 'opens_at']);
            $table->index(['course_id', 'status']);
        });

        Schema::create('exam_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('shuffle_questions')->default(false);
            $table->unsignedSmallInteger('questions_to_draw')->nullable();
            $table->json('pool_filters')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'sort_order']);
        });

        Schema::create('exam_part_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->restrictOnDelete();
            $table->decimal('points', 8, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['exam_part_id', 'question_id']);
            $table->index(['exam_part_id', 'sort_order']);
        });

        Schema::create('exam_accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->unsignedSmallInteger('extra_time_minutes')->default(0);
            $table->unsignedTinyInteger('extra_attempts')->default(0);
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('ignore_access_code')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('status', 20)->default('in_progress');
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->decimal('auto_score', 10, 2)->default(0);
            $table->decimal('manual_score', 10, 2)->default(0);
            $table->decimal('total_score', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->json('question_snapshot');
            $table->json('settings_snapshot')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedInteger('integrity_flags')->default(0);
            $table->string('submission_reason', 32)->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'attempt_number']);
            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index(['expires_at', 'status']);
        });

        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('exam_questions')->restrictOnDelete();
            $table->json('answer')->nullable();
            $table->longText('answer_text')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_original_name')->nullable();
            $table->string('status', 20)->default('unanswered');
            $table->boolean('is_correct')->nullable();
            $table->decimal('auto_score', 8, 2)->default(0);
            $table->decimal('manual_score', 8, 2)->nullable();
            $table->text('grader_feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('question_snapshot');
            $table->text('grading_key')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->index(['attempt_id', 'status']);
            $table->index(['question_id', 'graded_at']);
        });

        Schema::create('exam_attempt_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['attempt_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_events');
        Schema::dropIfExists('exam_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_accommodations');
        Schema::dropIfExists('exam_part_questions');
        Schema::dropIfExists('exam_parts');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_question_options');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_question_categories');
    }
};
