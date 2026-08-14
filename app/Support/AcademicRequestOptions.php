<?php

namespace App\Support;

class AcademicRequestOptions
{
    /** @return array<string, array{label: string, description: string, route: string, tone: string}> */
    public static function types(): array
    {
        return [
            'deferral' => [
                'label' => 'طلبات التأجيل',
                'description' => 'تأجيل الدراسة لفصل أو أكثر',
                'route' => 'admin.requests.deferral',
                'tone' => 'deferral',
            ],
            'withdrawal' => [
                'label' => 'طلبات الانسحاب',
                'description' => 'انسحاب من البرنامج أو الدفعة',
                'route' => 'admin.requests.withdrawal',
                'tone' => 'withdrawal',
            ],
            'program_change' => [
                'label' => 'تغيير البرنامج',
                'description' => 'نقل الطالب إلى برنامج آخر',
                'route' => 'admin.requests.program-change',
                'tone' => 'program',
            ],
            'semester_excuse' => [
                'label' => 'اعتذار عن الفصل',
                'description' => 'اعتذار رسمي عن فصل دراسي',
                'route' => 'admin.requests.semester-excuse',
                'tone' => 'excuse',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'pending' => 'قيد المراجعة',
            'processing' => 'جاري العمل عليه',
            'approved' => 'موافقة',
            'rejected' => 'مرفوض',
        ];
    }

    /** @return array<string, string> */
    public static function reviewStatuses(): array
    {
        return [
            'pending' => 'لم يراجع',
            'reviewed' => 'تم المراجعة',
        ];
    }

    /** @return array<int, array{key: string, label: string}> */
    public static function semesters(): array
    {
        return [
            ['key' => '2027-f1', 'label' => 'فصل القبول الأول للعام الأكاديمي 2027-2028'],
            ['key' => '2026-f1', 'label' => 'الفصل الأول للعام الدراسي 2026/2027'],
            ['key' => '2025-f1', 'label' => 'الفصل الأول للعام الدراسي 2025/2026'],
            ['key' => '2024-f1', 'label' => 'الفصل الأول للعام الدراسي 2024/2025'],
        ];
    }

    public static function typeFromRoute(?string $routeName): string
    {
        return match ($routeName) {
            'admin.requests.withdrawal' => 'withdrawal',
            'admin.requests.program-change' => 'program_change',
            'admin.requests.semester-excuse' => 'semester_excuse',
            default => 'deferral',
        };
    }

    public static function typeLabel(?string $type): string
    {
        return $type ? (static::types()[$type]['label'] ?? $type) : '—';
    }

    public static function statusLabel(?string $status): string
    {
        return $status ? (static::statuses()[$status] ?? $status) : '—';
    }

    public static function reviewStatusLabel(?string $status): string
    {
        return $status ? (static::reviewStatuses()[$status] ?? $status) : '—';
    }

    public static function listRoute(string $type): string
    {
        $meta = static::types()[$type] ?? null;

        return $meta ? route($meta['route']) : route('admin.requests.deferral');
    }

    public static function tableTitle(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'عرض كافة الطلبات الانسحاب',
            'program_change' => 'عرض كافة طلبات تغيير البرنامج',
            'semester_excuse' => 'طلبات اعتذار الفصل الدراسي',
            default => 'طلبات التأجيل',
        };
    }

    public static function emptyMessage(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'لا توجد طلبات انسحاب',
            'program_change' => 'لا توجد طلبات تغيير برنامج',
            'semester_excuse' => 'لا توجد طلبات اعتذار',
            default => 'لا توجد طلبات تأجيل',
        };
    }

    public static function unitLabel(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'طلب انسحاب',
            'program_change' => 'طلب تغيير برنامج',
            'semester_excuse' => 'طالب اعتذار',
            default => 'طلب تأجيل',
        };
    }

    public static function viewTitle(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'عرض بيانات طلب الانسحاب',
            'program_change' => 'عرض بيانات طلب تغيير البرنامج',
            'semester_excuse' => 'عرض بيانات طلب اعتذار الفصل',
            default => 'عرض بيانات طلب التأجيل',
        };
    }

    public static function generateRequestNo(int $seed = 0): string
    {
        return (string) (1779589387102000 + $seed + random_int(100, 99999));
    }

    public static function studentSingularLabel(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'طلب انسحاب',
            'program_change' => 'طلب تغيير برنامج',
            'semester_excuse' => 'طلب اعتذار عن الفصل',
            default => 'طلب تأجيل',
        };
    }

    public static function studentIcon(string $type): string
    {
        return match ($type) {
            'withdrawal' => 'fa-person-walking-arrow-right',
            'program_change' => 'fa-right-left',
            'semester_excuse' => 'fa-file-circle-xmark',
            default => 'fa-calendar-plus',
        };
    }

    public static function studentSubmitDescription(string $type): string
    {
        return static::types()[$type]['description'] ?? '';
    }

    /** @return array<string, string> */
    public static function paymentMethods(): array
    {
        return [
            'دفع إلكتروني' => 'دفع إلكتروني',
            'تحويل بنكي' => 'تحويل بنكي',
            'شيك' => 'شيك',
        ];
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'approved' => 'portal-badge--success',
            'rejected' => 'portal-badge--danger',
            'processing' => 'portal-badge--info',
            default => 'portal-badge--warn',
        };
    }

    public static function reviewActionLabel(string $action): string
    {
        return match ($action) {
            'approved' => 'الموافقة على الطلب الأكاديمي',
            'rejected' => 'رفض الطلب الأكاديمي',
            'processing' => 'بدء معالجة الطلب الأكاديمي',
            default => 'تحديث الطلب الأكاديمي',
        };
    }

    /** @return array<int, array{key: string, type: string, label: string, description: string, icon: string, route: string}> */
    public static function hubActions(string $locale): array
    {
        $actions = [];

        foreach (static::types() as $type => $meta) {
            $actions[] = [
                'key' => $type,
                'type' => 'academic',
                'label' => static::studentSingularLabel($type),
                'description' => $meta['description'],
                'icon' => static::studentIcon($type),
                'route' => route('user-requests.create', ['locale' => $locale, 'type' => $type]),
            ];
        }

        $actions[] = [
            'key' => 'refund',
            'type' => 'financial',
            'label' => 'طلب استرداد',
            'description' => 'استرداد مبلغ طلب مدفوع',
            'icon' => 'fa-rotate-left',
            'route' => route('refunds', ['locale' => $locale]),
        ];

        return $actions;
    }
}
