<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->string('visibility_mode', 32)->default('immediate')->after('status');
            $table->boolean('student_visible')->default(true)->after('visibility_mode');
            $table->timestamp('visible_from')->nullable()->after('student_visible');
            $table->boolean('allow_download')->default(true)->after('visible_from');
            $table->boolean('allow_print')->default(true)->after('allow_download');
            $table->boolean('show_details')->default(true)->after('allow_print');
            $table->string('student_note', 500)->nullable()->after('show_details');

            $table->index(['user_id', 'student_visible', 'visible_from'], 'certificates_student_visibility_index');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropIndex('certificates_student_visibility_index');
            $table->dropColumn([
                'visibility_mode',
                'student_visible',
                'visible_from',
                'allow_download',
                'allow_print',
                'show_details',
                'student_note',
            ]);
        });
    }
};
