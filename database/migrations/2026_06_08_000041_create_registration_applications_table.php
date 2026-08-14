<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no', 32)->unique();
            $table->string('type', 32)->index();
            $table->string('status', 24)->default('pending')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email')->index();
            $table->string('applicant_phone', 24)->nullable();
            $table->string('approved_role', 24)->nullable();
            $table->string('course_name')->nullable();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->json('payload')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_applications');
    }
};
