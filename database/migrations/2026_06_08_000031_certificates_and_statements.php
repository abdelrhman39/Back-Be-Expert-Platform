<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('verify_token', 64)->nullable()->unique()->after('code');
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('issued_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
        });

        Schema::create('statements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_student_id')->nullable()->constrained('academic_students')->nullOnDelete();
            $table->string('type', 32);
            $table->string('title');
            $table->string('status', 32)->default('pending');
            $table->text('student_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statements');

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issued_by');
            $table->dropColumn(['verify_token', 'notes']);
        });
    }
};
