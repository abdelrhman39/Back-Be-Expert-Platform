<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64);
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('trigger_kind', 32)->default('before_event');
            $table->unsignedInteger('offset_minutes')->nullable();
            $table->json('channels')->nullable();
            $table->string('audience', 32)->default('enrolled_students');
            $table->boolean('quiet_hours_respect')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_enabled']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('channel', 32);
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->unique(
                ['notification_rule_id', 'notifiable_id', 'subject_type', 'subject_id', 'channel'],
                'notification_delivery_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('notifications');
    }
};
