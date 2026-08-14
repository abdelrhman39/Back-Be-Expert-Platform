<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename');
            $table->string('status', 24)->default('processing');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('created_rows')->default(0);
            $table->unsignedInteger('updated_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('options')->nullable();
            $table->json('errors')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('import_id')->nullable()->constrained('crm_imports')->nullOnDelete();
            $table->string('source_type', 60)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source', 40)->default('manual');
            $table->string('status', 32)->default('new');
            $table->string('priority', 16)->default('medium');
            $table->unsignedTinyInteger('lead_score')->default(0);
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('country', 120)->nullable();
            $table->string('region', 160)->nullable();
            $table->string('city', 160)->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('do_not_contact')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('first_contacted_at')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'owner_id']);
            $table->index(['program_id', 'status']);
            $table->index(['next_follow_up_at', 'status']);
            $table->index(['source', 'created_at']);
            $table->index('email');
            $table->index('phone');
            $table->unique(['source_type', 'source_id']);
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32);
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('outcome', 40)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['contact_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['scheduled_at', 'completed_at']);
        });

        Schema::create('crm_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('sales_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->unsignedInteger('assigned_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_assigned_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['program_id', 'sales_user_id']);
            $table->index(['program_id', 'is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_assignment_rules');
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_imports');
    }
};
