<?php

namespace App\Support;

class FormFieldLibrary
{
    /** @return array<string, array{label: string, fields: list<array<string, mixed>>}> */
    public static function categories(): array
    {
        return [
            'personal' => [
                'label' => 'بيانات شخصية',
                'fields' => [
                    self::preset('full_name'),
                    self::preset('email'),
                    self::preset('mobile'),
                    self::preset('date_of_birth'),
                    self::preset('nationality'),
                    self::preset('gender'),
                ],
            ],
            'professional' => [
                'label' => 'بيانات مهنية',
                'fields' => [
                    self::preset('job_title'),
                    self::preset('organisation'),
                    self::preset('linkedin_url'),
                    self::preset('portfolio_url'),
                    self::preset('years_of_experience'),
                ],
            ],
            'custom' => [
                'label' => 'حقول مخصصة',
                'fields' => [
                    self::preset('text_short'),
                    self::preset('text_long'),
                    self::preset('dropdown'),
                    self::preset('checkbox'),
                    self::preset('radio'),
                    self::preset('number'),
                ],
            ],
            'uploads' => [
                'label' => 'مرفقات',
                'fields' => [
                    self::preset('cv'),
                    self::preset('portfolio_file'),
                    self::preset('supporting_doc'),
                    self::preset('image'),
                    self::preset('video'),
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function preset(string $id): array
    {
        return match ($id) {
            'full_name' => ['preset' => 'full_name', 'key' => 'name', 'label' => 'الاسم الكامل', 'type' => 'text', 'required' => true, 'contact' => 'name'],
            'email' => ['preset' => 'email', 'key' => 'email', 'label' => 'البريد الإلكتروني', 'type' => 'email', 'required' => true, 'contact' => 'email'],
            'mobile' => ['preset' => 'mobile', 'key' => 'phone', 'label' => 'رقم الجوال', 'type' => 'tel', 'required' => true, 'contact' => 'phone'],
            'date_of_birth' => ['preset' => 'date_of_birth', 'key' => 'date_of_birth', 'label' => 'تاريخ الميلاد', 'type' => 'date', 'required' => false],
            'nationality' => ['preset' => 'nationality', 'key' => 'nationality', 'label' => 'الجنسية / الهوية', 'type' => 'text', 'required' => false],
            'gender' => ['preset' => 'gender', 'key' => 'gender', 'label' => 'الجنس', 'type' => 'radio', 'required' => false, 'options' => 'genders'],
            'job_title' => ['preset' => 'job_title', 'key' => 'job_title', 'label' => 'المسمى الوظيفي', 'type' => 'text', 'required' => false],
            'organisation' => ['preset' => 'organisation', 'key' => 'organisation', 'label' => 'الجهة / المؤسسة', 'type' => 'text', 'required' => false],
            'linkedin_url' => ['preset' => 'linkedin_url', 'key' => 'linkedin_url', 'label' => 'رابط LinkedIn', 'type' => 'text', 'required' => false, 'placeholder' => 'https://linkedin.com/in/...'],
            'portfolio_url' => ['preset' => 'portfolio_url', 'key' => 'portfolio_url', 'label' => 'رابط Portfolio', 'type' => 'text', 'required' => false],
            'years_of_experience' => ['preset' => 'years_of_experience', 'key' => 'years_of_experience', 'label' => 'سنوات الخبرة', 'type' => 'number', 'required' => false, 'min' => 0, 'max' => 60],
            'text_short' => ['preset' => 'text_short', 'key' => 'custom_text', 'label' => 'نص قصير', 'type' => 'text', 'required' => false],
            'text_long' => ['preset' => 'text_long', 'key' => 'custom_textarea', 'label' => 'نص طويل', 'type' => 'textarea', 'required' => false, 'rows' => 4],
            'dropdown' => ['preset' => 'dropdown', 'key' => 'custom_select', 'label' => 'قائمة منسدلة', 'type' => 'select', 'required' => false, 'options' => ['option_1' => 'خيار 1', 'option_2' => 'خيار 2']],
            'checkbox' => ['preset' => 'checkbox', 'key' => 'custom_checkbox', 'label' => 'خيار (مربع اختيار)', 'type' => 'checkbox', 'required' => false],
            'radio' => ['preset' => 'radio', 'key' => 'custom_radio', 'label' => 'خيارات (اختيار واحد)', 'type' => 'radio', 'required' => false, 'options' => ['yes' => 'نعم', 'no' => 'لا']],
            'number' => ['preset' => 'number', 'key' => 'custom_number', 'label' => 'رقم', 'type' => 'number', 'required' => false],
            'cv' => ['preset' => 'cv', 'key' => 'cv', 'label' => 'السيرة الذاتية', 'type' => 'file', 'required' => true],
            'portfolio_file' => ['preset' => 'portfolio_file', 'key' => 'portfolio', 'label' => 'ملف Portfolio', 'type' => 'file', 'required' => false],
            'supporting_doc' => ['preset' => 'supporting_doc', 'key' => 'supporting_doc', 'label' => 'مستند داعم', 'type' => 'file', 'required' => false],
            'image' => ['preset' => 'image', 'key' => 'image', 'label' => 'صورة', 'type' => 'file', 'required' => false, 'accept' => 'image'],
            'video' => ['preset' => 'video', 'key' => 'video', 'label' => 'فيديو', 'type' => 'file', 'required' => false, 'accept' => 'video'],
            default => ['preset' => $id, 'key' => $id, 'label' => 'حقل جديد', 'type' => 'text', 'required' => false],
        };
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'text' => 'Text',
            'email' => 'Email',
            'tel' => 'Phone',
            'textarea' => 'Text (long)',
            'number' => 'Number',
            'date' => 'Date',
            'select' => 'Dropdown',
            'radio' => 'Radio',
            'checkbox' => 'Checkbox',
            'file' => 'File upload',
            default => ucfirst($type),
        };
    }
}
