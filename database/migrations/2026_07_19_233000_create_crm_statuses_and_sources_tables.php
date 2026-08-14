<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name_ar');
            $table->string('color', 16)->default('#55706f');
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('crm_sources', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name_ar');
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table('crm_statuses')->insert([
            ['key' => 'new', 'name_ar' => 'جديد', 'color' => '#3b82f6', 'sort_order' => 10, 'is_active' => true, 'is_default' => true, 'is_initial' => true, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'contacted', 'name_ar' => 'تم التواصل', 'color' => '#0ea5e9', 'sort_order' => 20, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'qualified', 'name_ar' => 'مؤهل', 'color' => '#6366f1', 'sort_order' => 30, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'interested', 'name_ar' => 'مهتم', 'color' => '#d8a633', 'sort_order' => 40, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'follow_up', 'name_ar' => 'متابعة لاحقة', 'color' => '#f59e0b', 'sort_order' => 50, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'negotiation', 'name_ar' => 'تفاوض', 'color' => '#8b5cf6', 'sort_order' => 60, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => false, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'won', 'name_ar' => 'تم التحويل', 'color' => '#10b981', 'sort_order' => 70, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => true, 'is_lost' => false, 'is_closed' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'lost', 'name_ar' => 'غير مهتم / مفقود', 'color' => '#ef4444', 'sort_order' => 80, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => true, 'is_closed' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'unreachable', 'name_ar' => 'تعذر التواصل', 'color' => '#a63c35', 'sort_order' => 90, 'is_active' => true, 'is_default' => false, 'is_initial' => false, 'is_won' => false, 'is_lost' => false, 'is_closed' => true, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('crm_sources')->insert([
            ['key' => 'manual', 'name_ar' => 'إدخال يدوي', 'sort_order' => 10, 'is_active' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'import', 'name_ar' => 'ملف مستورد', 'sort_order' => 20, 'is_active' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'registration', 'name_ar' => 'مسجل بالمنصة', 'sort_order' => 30, 'is_active' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'application', 'name_ar' => 'طلب تسجيل', 'sort_order' => 40, 'is_active' => true, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'website', 'name_ar' => 'الموقع', 'sort_order' => 50, 'is_active' => true, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'campaign', 'name_ar' => 'حملة إعلانية', 'sort_order' => 60, 'is_active' => true, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'referral', 'name_ar' => 'ترشيح', 'sort_order' => 70, 'is_active' => true, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'social', 'name_ar' => 'شبكات اجتماعية', 'sort_order' => 80, 'is_active' => true, 'is_system' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_sources');
        Schema::dropIfExists('crm_statuses');
    }
};
