<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plan_template_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('installment_plan_templates')->cascadeOnDelete();
            $table->foreignId('academic_program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'academic_program_id'], 'inst_plan_program_unique');
        });

        Schema::create('installment_plan_template_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('installment_plan_templates')->cascadeOnDelete();
            $table->foreignId('catalog_course_id')->constrained('catalog_courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'catalog_course_id'], 'inst_plan_course_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plan_template_courses');
        Schema::dropIfExists('installment_plan_template_programs');
    }
};
