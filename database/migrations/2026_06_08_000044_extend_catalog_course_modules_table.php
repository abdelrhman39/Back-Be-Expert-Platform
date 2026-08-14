<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_course_modules', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->after('title_en');
            $table->text('summary_ar')->nullable()->after('code');
            $table->text('summary_en')->nullable()->after('summary_ar');
            $table->text('description_ar')->nullable()->after('summary_en');
            $table->text('description_en')->nullable()->after('description_ar');
            $table->text('objectives_ar')->nullable()->after('description_en');
            $table->text('objectives_en')->nullable()->after('objectives_ar');
            $table->string('status', 32)->default('published')->after('objectives_en');
            $table->boolean('is_optional')->default(false)->after('status');
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable()->after('is_optional');
            $table->json('prerequisite_module_ids')->nullable()->after('estimated_duration_minutes');
            $table->unsignedSmallInteger('drip_days')->nullable()->after('prerequisite_module_ids');
            $table->timestamp('unlock_at')->nullable()->after('drip_days');
            $table->string('completion_rule', 32)->default('all_lessons')->after('unlock_at');
            $table->string('icon', 64)->nullable()->after('completion_rule');
            $table->string('image_path')->nullable()->after('icon');
            $table->string('image_name')->nullable()->after('image_path');
            $table->string('meta_title_ar')->nullable()->after('image_name');
            $table->string('meta_title_en')->nullable()->after('meta_title_ar');
            $table->text('meta_description_ar')->nullable()->after('meta_title_en');
            $table->text('meta_description_en')->nullable()->after('meta_description_ar');
            $table->text('notes_internal')->nullable()->after('meta_description_en');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_course_modules', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'summary_ar',
                'summary_en',
                'description_ar',
                'description_en',
                'objectives_ar',
                'objectives_en',
                'status',
                'is_optional',
                'estimated_duration_minutes',
                'prerequisite_module_ids',
                'drip_days',
                'unlock_at',
                'completion_rule',
                'icon',
                'image_path',
                'image_name',
                'meta_title_ar',
                'meta_title_en',
                'meta_description_ar',
                'meta_description_en',
                'notes_internal',
            ]);
        });
    }
};
