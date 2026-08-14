<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('gateway', 32)->nullable()->after('payment_ref');
            $table->string('gateway_payment_id')->nullable()->after('gateway');
            $table->timestamp('paid_at')->nullable()->after('gateway_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_payment_id', 'paid_at']);
        });
    }
};
