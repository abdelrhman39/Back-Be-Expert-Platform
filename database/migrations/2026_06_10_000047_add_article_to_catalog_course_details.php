<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_course_details', function (Blueprint $table) {
            $table->longText('article_ar')->nullable()->after('faq_en');
            $table->longText('article_en')->nullable()->after('article_ar');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_course_details', function (Blueprint $table) {
            $table->dropColumn(['article_ar', 'article_en']);
        });
    }
};
