<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_course_details', function (Blueprint $table) {
            $table->unsignedInteger('course_id')->primary();
            $table->text('meta_description_ar')->nullable();
            $table->text('meta_description_en')->nullable();
            $table->text('brief_ar')->nullable();
            $table->text('brief_en')->nullable();
            $table->text('goals_ar')->nullable();
            $table->text('goals_en')->nullable();
            $table->text('audience_ar')->nullable();
            $table->text('audience_en')->nullable();
            $table->text('features_ar')->nullable();
            $table->text('features_en')->nullable();
            $table->text('topics_ar')->nullable();
            $table->text('topics_en')->nullable();
            $table->text('outcomes_ar')->nullable();
            $table->text('outcomes_en')->nullable();
            $table->text('conditions_ar')->nullable();
            $table->text('conditions_en')->nullable();
            $table->text('faq_ar')->nullable();
            $table->text('faq_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_course_details');
    }
};
