<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plan_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('program_type', 32)->nullable();
            $table->unsignedSmallInteger('max_installments')->default(12);
            $table->decimal('min_down_payment_percent', 5, 2)->default(20);
            $table->boolean('is_active')->default(true);
            $table->text('description_ar')->nullable();
            $table->timestamps();
        });

        Schema::create('installment_plan_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('installment_plan_templates')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->decimal('percent', 5, 2);
            $table->string('due_rule', 32)->default('month_offset');
            $table->unsignedSmallInteger('month_offset')->nullable();
            $table->string('label_ar')->nullable();
            $table->string('label_en')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'sequence']);
        });

        Schema::create('installment_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_no', 32)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_student_id')->nullable()->constrained('academic_students')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('academic_programs')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('installment_plan_templates')->nullOnDelete();
            $table->string('title');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('remaining_balance', 12, 2);
            $table->string('currency', 8)->default('SAR');
            $table->string('status', 32)->default('active');
            $table->date('starts_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('installment_contracts')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('label')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('percent', 5, 2)->nullable();
            $table->date('due_date');
            $table->string('status', 32)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('late_fee_amount', 10, 2)->default(0);
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waived_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['contract_id', 'sequence']);
        });

        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('installment_schedules')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('gateway', 32)->default('moyasar');
            $table->string('gateway_ref')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 32)->default('pending');
            $table->json('raw_webhook')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
        Schema::dropIfExists('installment_schedules');
        Schema::dropIfExists('installment_contracts');
        Schema::dropIfExists('installment_plan_template_items');
        Schema::dropIfExists('installment_plan_templates');
    }
};
