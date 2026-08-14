<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('integrity_review_status', 20)->default('unreviewed')->after('integrity_flags');
            $table->text('integrity_review_notes')->nullable()->after('integrity_review_status');
            $table->foreignId('integrity_reviewed_by')
                ->nullable()
                ->after('integrity_review_notes')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('integrity_reviewed_at')->nullable()->after('integrity_reviewed_by');

            $table->index(['exam_id', 'integrity_review_status']);
            $table->index(['exam_id', 'integrity_flags']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex(['exam_id', 'integrity_review_status']);
            $table->dropIndex(['exam_id', 'integrity_flags']);
            $table->dropConstrainedForeignId('integrity_reviewed_by');
            $table->dropColumn([
                'integrity_review_status',
                'integrity_review_notes',
                'integrity_reviewed_at',
            ]);
        });
    }
};
