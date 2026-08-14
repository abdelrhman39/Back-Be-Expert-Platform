<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('teams_meeting_id')->nullable();
            $table->string('graph_recording_id')->nullable();
            $table->string('recording_url', 1000)->nullable();
            $table->string('download_url', 1000)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->string('source', 32)->default('teams_graph');
            $table->string('status', 32)->default('processing');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->json('raw_graph_payload')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::table('session_materials', function (Blueprint $table) {
            $table->foreignId('session_recording_id')->nullable()->after('external_url')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('session_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('session_recording_id');
        });

        Schema::dropIfExists('session_recordings');
    }
};
