<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->timestamp('student_signed_at')->nullable()->after('signed_at');
            $table->string('student_signature_path')->nullable()->after('student_signed_at');
            $table->string('student_signature_name')->nullable()->after('student_signature_path');
            $table->string('student_signature_ip', 64)->nullable()->after('student_signature_name');
            $table->boolean('requires_student_signature')->default(true)->after('student_signature_ip');
            $table->timestamp('suspended_at')->nullable()->after('completed_at');
            $table->string('suspension_reason')->nullable()->after('suspended_at');
        });

        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('admin_notes');
            $table->json('reminder_offsets_sent')->nullable()->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'reminder_offsets_sent']);
        });

        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->dropColumn([
                'student_signed_at',
                'student_signature_path',
                'student_signature_name',
                'student_signature_ip',
                'requires_student_signature',
                'suspended_at',
                'suspension_reason',
            ]);
        });
    }
};
