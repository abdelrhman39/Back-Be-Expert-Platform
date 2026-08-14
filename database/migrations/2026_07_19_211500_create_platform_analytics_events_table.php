<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 32);
            $table->string('visit_id', 64)->nullable();
            $table->string('visitor_hash', 64)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path', 500)->nullable();
            $table->string('route_name', 190)->nullable();
            $table->string('referrer_host', 190)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('country_name', 120)->nullable();
            $table->string('region', 160)->nullable();
            $table->string('city', 160)->nullable();
            $table->string('device_type', 24)->nullable();
            $table->string('browser', 64)->nullable();
            $table->string('operating_system', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->date('occurred_on');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event_type', 'occurred_on']);
            $table->index(['occurred_on', 'visit_id']);
            $table->index(['occurred_on', 'visitor_hash']);
            $table->index(['country_code', 'occurred_on']);
            $table->index(['region', 'occurred_on']);
            $table->index(['city', 'occurred_on']);
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_analytics_events');
    }
};
