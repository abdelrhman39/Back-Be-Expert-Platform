<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zoxagent_meetings', function (Blueprint $table) {
            if (! Schema::hasColumn('zoxagent_meetings', 'last_ended_at')) {
                $table->timestamp('last_ended_at')->nullable()->after('last_started_at');
            }
            if (! Schema::hasColumn('zoxagent_meetings', 'recordings_synced_at')) {
                $table->timestamp('recordings_synced_at')->nullable()->after('attendance_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('zoxagent_meetings', function (Blueprint $table) {
            if (Schema::hasColumn('zoxagent_meetings', 'last_ended_at')) {
                $table->dropColumn('last_ended_at');
            }
            if (Schema::hasColumn('zoxagent_meetings', 'recordings_synced_at')) {
                $table->dropColumn('recordings_synced_at');
            }
        });
    }
};
