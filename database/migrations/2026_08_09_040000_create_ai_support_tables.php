<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_support_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('locale', 5)->default('ar');
            $table->string('audience', 20)->default('visitor'); // visitor|student|instructor
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('user_agent', 255)->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('status', 20)->default('open'); // open|closed|escalated
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_support_conversations')->cascadeOnDelete();
            $table->string('role', 20); // user|assistant|system
            $table->longText('content');
            $table->json('knowledge_refs')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->boolean('needs_human')->default(false);
            $table->tinyInteger('feedback')->nullable(); // 1 good, -1 bad
            $table->text('feedback_note')->nullable();
            $table->boolean('training_approved')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['training_approved', 'feedback']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_support_messages');
        Schema::dropIfExists('ai_support_conversations');
    }
};
