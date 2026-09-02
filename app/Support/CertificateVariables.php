<?php

namespace App\Support;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\PlatformSetting;
use Carbon\CarbonInterface;

class CertificateVariables
{
    /** @return array<string, array{label: string, group: string, sample: string}> */
    public static function definitions(): array
    {
        return [
            'student.name_ar' => ['label' => 'اسم الطالب بالعربية', 'group' => 'الطالب', 'sample' => 'محمد أحمد العتيبي'],
            'student.name_en' => ['label' => 'اسم الطالب بالإنجليزية', 'group' => 'الطالب', 'sample' => 'Mohammed Ahmed Alotaibi'],
            'student.national_id' => ['label' => 'رقم الهوية', 'group' => 'الطالب', 'sample' => '10XXXXXXXX'],
            'student.academic_id' => ['label' => 'الرقم الأكاديمي', 'group' => 'الطالب', 'sample' => 'ST-2026-1042'],
            'student.nationality' => ['label' => 'الجنسية', 'group' => 'الطالب', 'sample' => 'سعودي'],
            'student.email' => ['label' => 'البريد الإلكتروني', 'group' => 'الطالب', 'sample' => 'student@example.com'],
            'student.city' => ['label' => 'المدينة', 'group' => 'الطالب', 'sample' => 'الامير مقرن'],
            'program.name_ar' => ['label' => 'اسم البرنامج بالعربية', 'group' => 'البرنامج', 'sample' => 'الدبلوم المهني في إدارة المشاريع'],
            'program.name_en' => ['label' => 'اسم البرنامج بالإنجليزية', 'group' => 'البرنامج', 'sample' => 'Professional Diploma in Project Management'],
            'program.certificate_name' => ['label' => 'اسم البرنامج على الشهادة', 'group' => 'البرنامج', 'sample' => 'الدبلوم المهني في إدارة المشاريع'],
            'program.code' => ['label' => 'رمز البرنامج', 'group' => 'البرنامج', 'sample' => 'PM-PRO'],
            'program.duration' => ['label' => 'مدة البرنامج', 'group' => 'البرنامج', 'sample' => '12 شهراً'],
            'batch.name' => ['label' => 'اسم الدفعة', 'group' => 'الدفعة', 'sample' => 'الدفعة الأولى 2026'],
            'batch.code' => ['label' => 'رمز الدفعة', 'group' => 'الدفعة', 'sample' => 'B-2026-01'],
            'batch.start_date' => ['label' => 'تاريخ بدء البرنامج', 'group' => 'التواريخ', 'sample' => '01 يناير 2026'],
            'batch.end_date' => ['label' => 'تاريخ انتهاء البرنامج', 'group' => 'التواريخ', 'sample' => '31 ديسمبر 2026'],
            'certificate.code' => ['label' => 'رقم الشهادة', 'group' => 'الشهادة', 'sample' => 'BE-26-0001042'],
            'certificate.issue_date' => ['label' => 'تاريخ الإصدار', 'group' => 'التواريخ', 'sample' => '19 يوليو 2026'],
            'certificate.start_date' => ['label' => 'تاريخ البداية المعتمد', 'group' => 'التواريخ', 'sample' => '01 يناير 2026'],
            'certificate.end_date' => ['label' => 'تاريخ النهاية المعتمد', 'group' => 'التواريخ', 'sample' => '31 ديسمبر 2026'],
            'certificate.verify_url' => ['label' => 'رابط التحقق', 'group' => 'الشهادة', 'sample' => 'https://example.com/ar/certificate-verify/secure-token'],
            'platform.name_ar' => ['label' => 'اسم المنصة بالعربية', 'group' => 'المنصة', 'sample' => 'مركز التعلم المستمر'],
            'platform.name_en' => ['label' => 'اسم المنصة بالإنجليزية', 'group' => 'المنصة', 'sample' => 'Continuing Learning Center'],
            'issuer.name' => ['label' => 'اسم مصدر الشهادة', 'group' => 'الشهادة', 'sample' => 'مدير مركز التعلم المستمر'],
        ];
    }

    /** @return array<string, array<int, array{key: string, label: string, sample: string}>> */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::definitions() as $key => $definition) {
            $groups[$definition['group']][] = [
                'key' => $key,
                'label' => $definition['label'],
                'sample' => $definition['sample'],
            ];
        }

        return $groups;
    }

    /** @return array<string, string> */
    public static function samples(): array
    {
        return collect(self::definitions())
            ->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['sample']])
            ->all();
    }

    /** @return array<string, string> */
    public static function resolve(Certificate $certificate, ?AcademicStudent $student = null): array
    {
        $student ??= $certificate->academicStudent;
        $student?->loadMissing(['batch.program', 'section']);
        $batch = $student?->batch;
        $program = $batch?->program;

        return [
            'student.name_ar' => $certificate->holder_name ?: ($student?->name_ar ?? ''),
            'student.name_en' => $student?->name_en ?? '',
            'student.national_id' => $student?->national_id ?? '',
            'student.academic_id' => $student?->academic_id ?? '',
            'student.nationality' => $student?->nationality ?? '',
            'student.email' => $student?->email ?? $student?->user?->email ?? '',
            'student.city' => $student?->city ?? '',
            'program.name_ar' => $certificate->program_name ?: ($program?->name_ar ?? ''),
            'program.name_en' => $program?->name_en ?? '',
            'program.certificate_name' => $program?->name_on_certificate ?: ($certificate->program_name ?? ''),
            'program.code' => $program?->code ?? '',
            'program.duration' => $program?->displayDuration() ?? '',
            'batch.name' => $batch?->name ?? '',
            'batch.code' => $batch?->code ?? '',
            'batch.start_date' => self::date($batch?->start_date),
            'batch.end_date' => self::date($batch?->end_date),
            'certificate.code' => $certificate->code,
            'certificate.issue_date' => self::date($certificate->issued_at),
            'certificate.start_date' => self::date($certificate->program_started_at ?? $batch?->start_date),
            'certificate.end_date' => self::date($certificate->program_ended_at ?? $batch?->end_date),
            'certificate.verify_url' => $certificate->verifyUrl(),
            'platform.name_ar' => PlatformSetting::get('platform_name_ar', config('app.name')) ?? config('app.name'),
            'platform.name_en' => PlatformSetting::get('platform_name_en', config('app.name')) ?? config('app.name'),
            'issuer.name' => $certificate->issuer?->name ?? '',
        ];
    }

    public static function interpolate(string $content, array $values): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-z0-9_.-]+)\s*\}\}/i',
            fn (array $matches) => (string) ($values[$matches[1]] ?? ''),
            $content,
        ) ?? $content;
    }

    private static function date(mixed $date): string
    {
        return $date instanceof CarbonInterface ? $date->translatedFormat('d F Y') : '';
    }
}
