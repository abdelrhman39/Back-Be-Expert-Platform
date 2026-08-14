<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('sidebar_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('catalog_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('sidebar_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('catalog_category_course', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('catalog_categories')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('catalog_courses')->cascadeOnDelete();
            $table->primary(['category_id', 'course_id']);
        });

        Schema::create('catalog_field_course', function (Blueprint $table) {
            $table->foreignId('field_id')->constrained('catalog_fields')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('catalog_courses')->cascadeOnDelete();
            $table->primary(['field_id', 'course_id']);
        });

        Schema::table('catalog_courses', function (Blueprint $table) {
            $table->boolean('is_self_learning')->default(false)->after('delivery_type');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_courses', function (Blueprint $table) {
            $table->dropColumn('is_self_learning');
        });

        Schema::dropIfExists('catalog_field_course');
        Schema::dropIfExists('catalog_category_course');
        Schema::dropIfExists('catalog_fields');
        Schema::dropIfExists('catalog_categories');
    }
};
