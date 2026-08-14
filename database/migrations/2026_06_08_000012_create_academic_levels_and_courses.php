<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->string('name_ar');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });

        Schema::create('academic_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('academic_levels')->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('symbol_ar', 64)->nullable();
            $table->string('symbol_en', 64)->nullable();
            $table->string('code', 32)->unique();
            $table->unsignedSmallInteger('credit_hours')->default(0);
            $table->string('status', 32)->default('active');
            $table->string('target_group')->nullable();
            $table->text('summary')->nullable();
            $table->string('added_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_courses');
        Schema::dropIfExists('academic_levels');
    }
};
