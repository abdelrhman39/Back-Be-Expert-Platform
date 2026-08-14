<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->string('payment_receipt_path')->nullable()->after('lost_reason');
            $table->string('payment_receipt_name')->nullable()->after('payment_receipt_path');
            $table->timestamp('payment_receipt_uploaded_at')->nullable()->after('payment_receipt_name');
            $table->timestamp('paid_at')->nullable()->after('payment_receipt_uploaded_at');
        });

        $now = now();

        DB::table('crm_statuses')->where('key', 'won')->update(['sort_order' => 75]);
        DB::table('crm_statuses')->where('key', 'lost')->update(['sort_order' => 85]);
        DB::table('crm_statuses')->where('key', 'unreachable')->update(['sort_order' => 95]);

        $existing = DB::table('crm_statuses')->whereIn('key', ['awaiting_payment', 'paid'])->pluck('key')->all();

        $rows = [];
        if (! in_array('awaiting_payment', $existing, true)) {
            $rows[] = [
                'key' => 'awaiting_payment',
                'name_ar' => 'يريد السداد',
                'color' => '#0d9488',
                'sort_order' => 65,
                'is_active' => true,
                'is_default' => false,
                'is_initial' => false,
                'is_won' => false,
                'is_lost' => false,
                'is_closed' => false,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (! in_array('paid', $existing, true)) {
            $rows[] = [
                'key' => 'paid',
                'name_ar' => 'تم السداد',
                'color' => '#059669',
                'sort_order' => 70,
                'is_active' => true,
                'is_default' => false,
                'is_initial' => false,
                'is_won' => true,
                'is_lost' => false,
                'is_closed' => true,
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('crm_statuses')->insert($rows);
        }
    }

    public function down(): void
    {
        DB::table('crm_statuses')->whereIn('key', ['awaiting_payment', 'paid'])->delete();
        DB::table('crm_statuses')->where('key', 'won')->update(['sort_order' => 70]);
        DB::table('crm_statuses')->where('key', 'lost')->update(['sort_order' => 80]);
        DB::table('crm_statuses')->where('key', 'unreachable')->update(['sort_order' => 90]);

        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropColumn([
                'payment_receipt_path',
                'payment_receipt_name',
                'payment_receipt_uploaded_at',
                'paid_at',
            ]);
        });
    }
};
