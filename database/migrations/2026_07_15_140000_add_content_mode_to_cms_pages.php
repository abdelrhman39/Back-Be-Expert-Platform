<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cms_pages', 'content_mode')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->string('content_mode', 16)->default('html')->after('layout');
            });
        }

        DB::table('cms_pages')
            ->whereIn('type', ['home', 'about', 'contact'])
            ->update(['content_mode' => 'blocks']);

        DB::table('cms_pages')
            ->whereNotIn('type', ['home', 'about', 'contact'])
            ->update(['content_mode' => 'html']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('cms_pages', 'content_mode')) {
            Schema::table('cms_pages', function (Blueprint $table) {
                $table->dropColumn('content_mode');
            });
        }
    }
};
