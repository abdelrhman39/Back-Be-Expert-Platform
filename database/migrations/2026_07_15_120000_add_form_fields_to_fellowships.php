<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fellowships', function (Blueprint $table) {
            if (! Schema::hasColumn('fellowships', 'form_fields')) {
                $table->json('form_fields')->nullable()->after('sort_order');
            }

            if (! Schema::hasColumn('fellowships', 'file_upload_settings')) {
                $table->json('file_upload_settings')->nullable()->after('form_fields');
            }
        });

        $defaultFields = [
            ['key' => 'name', 'label' => 'الاسم الكامل', 'type' => 'text', 'required' => true, 'contact' => 'name', 'preset' => 'full_name'],
            ['key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email', 'preset' => 'email'],
            ['key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone', 'preset' => 'mobile'],
            ['key' => 'national_id', 'label' => 'رقم الهوية', 'type' => 'text', 'required' => true],
            ['key' => 'education_level', 'label' => 'المؤهل العلمي', 'type' => 'select', 'required' => true, 'options' => 'education_levels'],
            ['key' => 'specialization', 'label' => 'التخصص', 'type' => 'text', 'required' => true],
            ['key' => 'motivation', 'label' => 'دوافع التقديم', 'type' => 'textarea', 'required' => true, 'rows' => 5],
            ['key' => 'cv', 'label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true, 'preset' => 'cv'],
        ];

        $defaultFileSettings = [
            'allowed_types' => 'PDF, DOC, DOCX, JPG, PNG',
            'max_size_mb' => 10,
            'max_files_per_field' => 1,
        ];

        if (Schema::hasTable('fellowships')) {
            DB::table('fellowships')->whereNull('form_fields')->update([
                'form_fields' => json_encode($defaultFields, JSON_UNESCAPED_UNICODE),
                'file_upload_settings' => json_encode($defaultFileSettings, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('fellowships', function (Blueprint $table) {
            $table->dropColumn(['form_fields', 'file_upload_settings']);
        });
    }
};
