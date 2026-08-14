<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_course_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('course_id');
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'sort_order']);
        });

        Schema::create('catalog_course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('catalog_course_modules')->cascadeOnDelete();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('type', 32)->default('html');
            $table->text('body_ar')->nullable();
            $table->text('body_en')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['module_id', 'sort_order']);
        });

        Schema::create('catalog_content_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('catalog_enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('catalog_course_lessons')->cascadeOnDelete();
            $table->string('status', 32)->default('not_started');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_content_progress');
        Schema::dropIfExists('catalog_course_lessons');
        Schema::dropIfExists('catalog_course_modules');
    }
};
