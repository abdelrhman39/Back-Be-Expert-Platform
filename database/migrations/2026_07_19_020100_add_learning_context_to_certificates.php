<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->string('related_program_type', 32)->nullable()->after('external_verification_url');
            $table->unsignedBigInteger('related_program_id')->nullable()->after('related_program_type');
            $table->string('related_program_name')->nullable()->after('related_program_id');

            $table->index(['related_program_type', 'related_program_id']);
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropIndex(['related_program_type', 'related_program_id']);
            $table->dropColumn([
                'related_program_type',
                'related_program_id',
                'related_program_name',
            ]);
        });
    }
};
