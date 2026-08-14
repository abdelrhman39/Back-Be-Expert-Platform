<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->unique()->constrained('academic_sections')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('academic_levels')->nullOnDelete();
            $table->string('semester_key', 32)->nullable();
            $table->string('period', 32)->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('academic_staff')->nullOnDelete();
            $table->string('trainer_name')->nullable();
            $table->string('day_of_week', 16)->nullable();
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_schedules');
    }
};
