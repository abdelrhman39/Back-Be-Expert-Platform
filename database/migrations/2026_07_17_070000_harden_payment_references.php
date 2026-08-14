<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 8)->default('SAR')->after('total');
            $table->unique(['gateway', 'gateway_payment_id'], 'orders_gateway_payment_unique');
            $table->index(
                ['installment_schedule_id', 'user_id', 'status'],
                'orders_installment_pending_lookup',
            );
        });

        Schema::table('installment_payments', function (Blueprint $table) {
            $table->unique('order_id', 'installment_payments_order_unique');
            $table->unique(['gateway', 'gateway_ref'], 'installment_payments_gateway_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropUnique('installment_payments_order_unique');
            $table->dropUnique('installment_payments_gateway_ref_unique');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_installment_pending_lookup');
            $table->dropUnique('orders_gateway_payment_unique');
            $table->dropColumn('currency');
        });
    }
};
