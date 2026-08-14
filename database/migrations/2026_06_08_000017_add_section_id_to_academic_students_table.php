<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_students', function (Blueprint $table) {
            $table->foreignId('section_id')
                ->nullable()
                ->after('batch_id')
                ->constrained('academic_sections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('academic_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
