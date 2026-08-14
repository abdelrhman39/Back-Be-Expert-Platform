<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_fields', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('slug');
            $table->boolean('home_visible')->default(true)->after('sidebar_visible');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_fields', function (Blueprint $table) {
            $table->dropColumn(['icon', 'home_visible']);
        });
    }
};
