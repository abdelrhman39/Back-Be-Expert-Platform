<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoom_hosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_staff_id')->nullable()->unique()->constrained('academic_staff')->nullOnDelete();
            $table->string('zoom_user_id')->unique();
            $table->string('email')->index();
            $table->string('license_type', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->string('pool', 64)->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('zoom_host_id')->nullable()->constrained()->nullOnDelete();
            $table->string('meeting_id')->unique();
            $table->string('meeting_uuid')->nullable()->index();
            $table->text('join_url')->nullable();
            $table->text('start_url')->nullable();
            $table->text('passcode')->nullable();
            $table->string('registration_mode', 32)->default('none');
            $table->string('recording_mode', 32)->default('none');
            $table->string('status', 32)->default('scheduled')->index();
            $table->json('alternative_hosts')->nullable();
            $table->json('settings')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('attendance_synced_at')->nullable();
            $table->timestamp('recordings_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('zoom_registrants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zoom_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('academic_students')->cascadeOnDelete();
            $table->string('registrant_id')->nullable()->index();
            $table->string('email');
            $table->text('join_url')->nullable();
            $table->string('status', 32)->default('pending');
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->unique(['zoom_meeting_id', 'student_id']);
        });

        Schema::create('zoom_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type')->index();
            $table->string('meeting_id')->nullable()->index();
            $table->json('payload');
            $table->string('status', 32)->default('received')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('provider', 32)->nullable()->after('source')->index();
            $table->string('external_participant_id')->nullable()->after('provider');
            $table->unsignedInteger('attendance_seconds')->nullable()->after('external_participant_id');
            $table->timestamp('joined_at')->nullable()->after('attendance_seconds');
            $table->timestamp('left_at')->nullable()->after('joined_at');
            $table->json('provider_payload')->nullable()->after('left_at');
        });

        Schema::table('session_recordings', function (Blueprint $table) {
            $table->string('provider', 32)->nullable()->after('source')->index();
            $table->string('external_recording_id')->nullable()->after('provider')->index();
            $table->string('share_url', 1000)->nullable()->after('external_recording_id');
            $table->string('play_url', 1000)->nullable()->after('share_url');
            $table->text('recording_passcode')->nullable()->after('download_url');
            $table->string('storage_destination', 32)->nullable()->after('recording_passcode');
            $table->string('storage_disk', 64)->nullable()->after('storage_destination');
            $table->string('storage_path', 1000)->nullable()->after('storage_disk');
            $table->json('provider_payload')->nullable()->after('raw_graph_payload');
        });
    }

    public function down(): void
    {
        Schema::table('session_recordings', function (Blueprint $table) {
            $table->dropColumn([
                'provider', 'external_recording_id', 'share_url', 'play_url', 'recording_passcode',
                'storage_destination', 'storage_disk', 'storage_path', 'provider_payload',
            ]);
        });
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn([
                'provider', 'external_participant_id', 'attendance_seconds', 'joined_at',
                'left_at', 'provider_payload',
            ]);
        });
        Schema::dropIfExists('zoom_webhook_events');
        Schema::dropIfExists('zoom_registrants');
        Schema::dropIfExists('zoom_meetings');
        Schema::dropIfExists('zoom_hosts');
    }
};
