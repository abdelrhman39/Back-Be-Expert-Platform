<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('instructions_en')->nullable()->after('instructions');
            $table->string('language_policy', 32)->default('ar_only')->after('type')->index();
        });

        Schema::table('exam_questions', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('prompt_en')->nullable()->after('prompt');
            $table->text('explanation_en')->nullable()->after('explanation');
            $table->json('answer_key_en')->nullable()->after('answer_key');
        });

        Schema::table('exam_question_options', function (Blueprint $table): void {
            $table->text('content_en')->nullable()->after('content');
            $table->text('feedback_en')->nullable()->after('feedback');
            $table->json('match_data_en')->nullable()->after('match_data');
        });

        Schema::table('exam_attempts', function (Blueprint $table): void {
            $table->string('language', 2)->default('ar')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', fn (Blueprint $table) => $table->dropColumn('language'));
        Schema::table('exam_question_options', fn (Blueprint $table) => $table->dropColumn(['content_en', 'feedback_en', 'match_data_en']));
        Schema::table('exam_questions', fn (Blueprint $table) => $table->dropColumn(['title_en', 'prompt_en', 'explanation_en', 'answer_key_en']));
        Schema::table('exams', fn (Blueprint $table) => $table->dropColumn(['title_en', 'instructions_en', 'language_policy']));
    }
};
