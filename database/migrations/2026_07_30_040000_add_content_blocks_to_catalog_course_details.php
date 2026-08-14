<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_course_details', function (Blueprint $table) {
            if (! Schema::hasColumn('catalog_course_details', 'content_blocks')) {
                $table->json('content_blocks')->nullable()->after('meta_description_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('catalog_course_details', function (Blueprint $table) {
            if (Schema::hasColumn('catalog_course_details', 'content_blocks')) {
                $table->dropColumn('content_blocks');
            }
        });
    }
};
