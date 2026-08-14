<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->string('source_type', 24)->default('platform')->after('certificate_template_id');
            $table->string('external_issuer')->nullable()->after('program_name');
            $table->string('external_credential_id')->nullable()->after('external_issuer');
            $table->text('external_verification_url')->nullable()->after('external_credential_id');
            $table->string('external_file_name')->nullable()->after('pdf_generated_at');
            $table->string('external_file_mime', 128)->nullable()->after('external_file_name');
            $table->string('external_file_hash', 64)->nullable()->after('external_file_mime');

            $table->index(['source_type', 'status']);
            $table->index('external_credential_id');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropIndex(['source_type', 'status']);
            $table->dropIndex(['external_credential_id']);
            $table->dropColumn([
                'source_type',
                'external_issuer',
                'external_credential_id',
                'external_verification_url',
                'external_file_name',
                'external_file_mime',
                'external_file_hash',
            ]);
        });
    }
};
