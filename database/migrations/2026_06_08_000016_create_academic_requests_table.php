<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 64)->unique();
            $table->string('type', 32);
            $table->foreignId('student_id')->nullable()->constrained('academic_students')->nullOnDelete();
            $table->string('student_name');
            $table->string('student_national_id', 20)->nullable();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->string('program_name')->nullable();
            $table->string('semester_key', 32)->nullable();
            $table->string('semester_label')->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('review_status', 32)->default('pending');
            $table->text('reason')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['type', 'review_status']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_requests');
    }
};
