<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoxagent_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->unique()->constrained('attendance_sessions')->cascadeOnDelete();
            $table->string('room_id')->nullable();
            $table->string('room_code', 16);
            $table->string('join_url', 500)->nullable();
            $table->boolean('auto_record')->default(true);
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('attendance_synced_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index('room_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoxagent_meetings');
    }
};
