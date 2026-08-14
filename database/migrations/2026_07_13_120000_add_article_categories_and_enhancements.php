<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('color', 16)->default('#1b8354');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('article_category_id')->nullable()->after('status')->constrained('article_categories')->nullOnDelete();
            $table->string('video_url', 500)->nullable()->after('featured_image');
            $table->text('internal_notes')->nullable()->after('legacy_slug');
        });

        Schema::table('article_translations', function (Blueprint $table) {
            $table->string('og_image', 500)->nullable()->after('meta_description');
        });

        $newsId = DB::table('article_categories')->insertGetId([
            'slug' => 'news',
            'name_ar' => 'أخبار',
            'name_en' => 'News',
            'color' => '#1b8354',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $eventId = DB::table('article_categories')->insertGetId([
            'slug' => 'events',
            'name_ar' => 'فعاليات',
            'name_en' => 'Events',
            'color' => '#0d9488',
            'sort_order' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('article_categories')->insert([
            [
                'slug' => 'announcements',
                'name_ar' => 'إعلانات',
                'name_en' => 'Announcements',
                'color' => '#2563eb',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'partnerships',
                'name_ar' => 'شراكات',
                'name_en' => 'Partnerships',
                'color' => '#7c3aed',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('articles')->where('category', 'news')->update(['article_category_id' => $newsId]);
        DB::table('articles')->where('category', 'event')->update(['article_category_id' => $eventId]);
    }

    public function down(): void
    {
        Schema::table('article_translations', function (Blueprint $table) {
            $table->dropColumn('og_image');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('article_category_id');
            $table->dropColumn(['video_url', 'internal_notes']);
        });

        Schema::dropIfExists('article_categories');
    }
};
