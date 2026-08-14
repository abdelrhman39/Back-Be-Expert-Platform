<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->json('checkout_items')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('installment_contracts', function (Blueprint $table) {
            $table->dropColumn('checkout_items');
        });
    }
};
