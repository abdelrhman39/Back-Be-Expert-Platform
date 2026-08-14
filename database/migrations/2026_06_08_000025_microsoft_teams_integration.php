<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('microsoft_teams_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('microsoft_id');
            $table->string('microsoft_email');
            $table->string('display_name')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('tenant_id')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('microsoft_email');
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->string('teams_meeting_id')->nullable()->after('meeting_url');
            $table->string('teams_join_web_url', 500)->nullable()->after('teams_meeting_id');
            $table->string('teams_organizer_id')->nullable()->after('teams_join_web_url');
            $table->timestamp('teams_attendance_synced_at')->nullable()->after('teams_organizer_id');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedInteger('teams_attendance_seconds')->nullable()->after('source');
            $table->timestamp('teams_joined_at')->nullable()->after('teams_attendance_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['teams_attendance_seconds', 'teams_joined_at']);
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['teams_meeting_id', 'teams_join_web_url', 'teams_organizer_id', 'teams_attendance_synced_at']);
        });

        Schema::dropIfExists('microsoft_teams_connections');
    }
};
