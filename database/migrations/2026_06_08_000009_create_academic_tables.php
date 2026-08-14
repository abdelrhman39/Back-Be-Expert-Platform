<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('code', 32)->unique();
            $table->string('symbol', 64)->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->date('start_date')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('type', 32)->default('diploma');
            $table->string('coordinator')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('city')->nullable();
            $table->text('summary')->nullable();
            $table->json('skills')->nullable();
            $table->string('study_status')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->string('semester')->nullable();
            $table->unsignedInteger('students_count')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });

        Schema::create('academic_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('academic_id', 32)->nullable();
            $table->string('national_id', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('gender', 16)->nullable();
            $table->string('city')->nullable();
            $table->string('study_status')->nullable();
            $table->boolean('login_allowed')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_students');
        Schema::dropIfExists('academic_batches');
        Schema::dropIfExists('academic_programs');
    }
};
