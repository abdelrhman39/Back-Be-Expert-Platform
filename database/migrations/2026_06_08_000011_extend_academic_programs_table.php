<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->string('name_on_certificate')->nullable()->after('name_en');
            $table->string('duration_label')->nullable()->after('duration_months');
            $table->string('media_url')->nullable()->after('study_status');
            $table->json('attachments')->nullable()->after('media_url');
        });
    }

    public function down(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->dropColumn(['name_on_certificate', 'duration_label', 'media_url', 'attachments']);
        });
    }
};
