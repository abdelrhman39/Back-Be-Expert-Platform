<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_batches', function (Blueprint $table) {
            $table->decimal('tuition_amount', 10, 2)->nullable()->after('capacity');
            $table->boolean('installment_allowed')->default(true)->after('tuition_amount');
        });
    }

    public function down(): void
    {
        Schema::table('academic_batches', function (Blueprint $table) {
            $table->dropColumn(['tuition_amount', 'installment_allowed']);
        });
    }
};
