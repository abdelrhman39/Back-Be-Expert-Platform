<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_pages', function (Blueprint $table) {
            $table->string('layout', 32)->default('default')->after('type');
            $table->boolean('show_title')->default(true)->after('show_in_footer');
            $table->boolean('noindex')->default(false)->after('show_title');
            $table->text('internal_notes')->nullable()->after('legacy_slug');
        });

        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->string('excerpt', 500)->nullable()->after('slug');
            $table->string('og_image', 512)->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('cms_page_translations', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'og_image']);
        });

        Schema::table('cms_pages', function (Blueprint $table) {
            $table->dropColumn(['layout', 'show_title', 'noindex', 'internal_notes']);
        });
    }
};
