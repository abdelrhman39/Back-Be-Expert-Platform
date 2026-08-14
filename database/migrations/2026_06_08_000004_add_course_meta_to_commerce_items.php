<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('course_image')->nullable()->after('course_title');
            $table->string('course_slug')->nullable()->after('course_image');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->string('course_image')->nullable()->after('course_title');
            $table->string('course_slug')->nullable()->after('course_image');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['course_image', 'course_slug']);
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropColumn(['course_image', 'course_slug']);
        });
    }
};
