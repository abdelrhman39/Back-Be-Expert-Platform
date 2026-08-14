<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('national_id', 10)->nullable()->unique()->after('phone_verified_at');
            $table->string('locale', 5)->default('ar')->after('password');
            $table->string('status', 20)->default('active')->after('locale');
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('status');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('last_login_at')->nullable()->after('locked_until');
            $table->string('last_login_method', 20)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'name_ar',
                'phone',
                'phone_verified_at',
                'national_id',
                'locale',
                'status',
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'last_login_method',
            ]);
        });
    }
};
