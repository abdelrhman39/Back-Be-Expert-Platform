<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_course_lessons', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->after('title_en');
            $table->text('summary_ar')->nullable()->after('code');
            $table->text('summary_en')->nullable()->after('summary_ar');
            $table->string('status', 32)->default('published')->after('type');
            $table->boolean('is_preview')->default(false)->after('status');
            $table->boolean('completion_required')->default(true)->after('is_preview');
            $table->string('video_provider', 32)->nullable()->after('external_url');
            $table->string('resource_url')->nullable()->after('video_provider');
            $table->text('notes_internal')->nullable()->after('file_name');
            $table->string('meta_title_ar')->nullable()->after('notes_internal');
            $table->string('meta_title_en')->nullable()->after('meta_title_ar');
            $table->text('meta_description_ar')->nullable()->after('meta_title_en');
            $table->text('meta_description_en')->nullable()->after('meta_description_ar');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_course_lessons', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'summary_ar',
                'summary_en',
                'status',
                'is_preview',
                'completion_required',
                'video_provider',
                'resource_url',
                'notes_internal',
                'meta_title_ar',
                'meta_title_en',
                'meta_description_ar',
                'meta_description_en',
            ]);
        });
    }
};
