<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('background_disk', 64)->default('public');
            $table->string('background_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->unsignedInteger('canvas_width')->default(1123);
            $table->unsignedInteger('canvas_height')->default(794);
            $table->string('orientation', 16)->default('landscape');
            $table->json('elements')->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 24)->default('draft');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_default']);
        });

        Schema::table('certificates', function (Blueprint $table): void {
            $table->foreignId('certificate_template_id')->nullable()->after('academic_student_id')
                ->constrained('certificate_templates')->nullOnDelete();
            $table->unsignedInteger('template_version')->nullable()->after('certificate_template_id');
            $table->date('program_started_at')->nullable()->after('program_name');
            $table->date('program_ended_at')->nullable()->after('program_started_at');
            $table->timestamp('expires_at')->nullable()->after('issued_at');
            $table->json('template_snapshot')->nullable()->after('notes');
            $table->json('data_snapshot')->nullable()->after('template_snapshot');
            $table->string('credential_hash', 64)->nullable()->unique()->after('verify_token');
            $table->string('pdf_disk', 64)->nullable()->after('data_snapshot');
            $table->string('pdf_path')->nullable()->after('pdf_disk');
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');
            $table->timestamp('revoked_at')->nullable()->after('status');
            $table->text('revocation_reason')->nullable()->after('revoked_at');
            $table->json('metadata')->nullable()->after('revocation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('certificate_template_id');
            $table->dropColumn([
                'template_version',
                'program_started_at',
                'program_ended_at',
                'expires_at',
                'template_snapshot',
                'data_snapshot',
                'credential_hash',
                'pdf_disk',
                'pdf_path',
                'pdf_generated_at',
                'revoked_at',
                'revocation_reason',
                'metadata',
            ]);
        });

        Schema::dropIfExists('certificate_templates');
    }
};
