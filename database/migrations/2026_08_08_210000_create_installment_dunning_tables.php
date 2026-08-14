<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_dunning_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('process_time', 5)->default('09:00');
            $table->timestamps();
        });

        Schema::create('installment_dunning_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('installment_dunning_policies')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('name');
            $table->text('admin_notes')->nullable();
            $table->boolean('enabled')->default(true);
            $table->integer('trigger_offset_days')->default(0)->comment('أيام بعد الاستحقاق؛ سالب = قبل الاستحقاق');
            $table->unsignedTinyInteger('trigger_hour')->nullable()->comment('ساعة محلية اختيارية 0-23');
            $table->json('actions')->nullable();
            $table->boolean('email_enabled')->default(true);
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->json('channels')->nullable();
            $table->timestamps();

            $table->index(['policy_id', 'sort_order']);
        });

        Schema::create('installment_dunning_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('installment_dunning_policies')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('installment_dunning_steps')->cascadeOnDelete();
            $table->foreignId('schedule_id')->constrained('installment_schedules')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('installment_contracts')->cascadeOnDelete();
            $table->string('status', 32)->default('executed');
            $table->timestamp('executed_at')->nullable();
            $table->json('actions_applied')->nullable();
            $table->boolean('message_sent')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'step_id']);
            $table->index(['contract_id', 'executed_at']);
        });

        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->foreignId('dunning_policy_id')
                ->nullable()
                ->after('template_id')
                ->constrained('installment_dunning_policies')
                ->nullOnDelete();
            $table->json('dunning_restrictions')->nullable()->after('suspension_reason');
        });
    }

    public function down(): void
    {
        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dunning_policy_id');
            $table->dropColumn('dunning_restrictions');
        });

        Schema::dropIfExists('installment_dunning_executions');
        Schema::dropIfExists('installment_dunning_steps');
        Schema::dropIfExists('installment_dunning_policies');
    }
};
