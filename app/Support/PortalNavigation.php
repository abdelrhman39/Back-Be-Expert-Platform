<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class PortalNavigation
{
    /** @return array<int, array{route: string, label: string, key: string}> */
    public static function items(): array
    {
        $locale = app()->getLocale();

        $items = [
            ['key' => 'profile', 'route' => route('profile', ['locale' => $locale]), 'label' => 'لوحة التحكم', 'icon' => 'fa-gauge-high'],
            ['key' => 'learning-list', 'route' => route('learning-list', ['locale' => $locale]), 'label' => 'قائمة التعلم', 'icon' => 'fa-book-open'],
            ['key' => 'academic-curriculum', 'route' => route('academic-curriculum', ['locale' => $locale]), 'label' => 'منهج البرنامج', 'icon' => 'fa-graduation-cap'],
            ['key' => 'sessions', 'route' => route('sessions', ['locale' => $locale]), 'label' => 'حصصي', 'icon' => 'fa-chalkboard-user'],
            ['key' => 'assignments', 'route' => route('assignments', ['locale' => $locale]), 'label' => 'واجباتي', 'icon' => 'fa-file-pen'],
            ['key' => 'exams', 'route' => route('exams', ['locale' => $locale]), 'label' => 'اختباراتي', 'icon' => 'fa-file-circle-check'],
            ['key' => 'notifications', 'route' => route('notifications', ['locale' => $locale]), 'label' => 'الإشعارات', 'icon' => 'fa-bell'],
            ['key' => 'certificates', 'route' => route('certificates', ['locale' => $locale]), 'label' => 'شهاداتي', 'icon' => 'fa-award'],
            ['key' => 'statements', 'route' => route('statements', ['locale' => $locale]), 'label' => 'إفاداتي', 'icon' => 'fa-file-lines'],
            ['key' => 'my-orders', 'route' => route('my-orders', ['locale' => $locale]), 'label' => 'طلبات الشراء', 'icon' => 'fa-file-invoice'],
            ['key' => 'academic-registration', 'route' => route('academic-registration', ['locale' => $locale]), 'label' => 'التسجيل الأكاديمي', 'icon' => 'fa-user-graduate'],
            ['key' => 'installments', 'route' => route('installments', ['locale' => $locale]), 'label' => 'أقساطي', 'icon' => 'fa-credit-card'],
            ['key' => 'user-requests', 'route' => route('user-requests', ['locale' => $locale]), 'label' => 'طلباتي الأكاديمية', 'icon' => 'fa-clipboard-list'],
            ['key' => 'settings', 'route' => route('settings', ['locale' => $locale]), 'label' => 'الإعدادات', 'icon' => 'fa-gear'],
            ['key' => 'certificate-verify', 'route' => route('certificate-verify', ['locale' => $locale]), 'label' => 'التحقق من الشهادة', 'icon' => 'fa-shield-check'],
        ];

        if (! InstallmentSettings::academicRegistrationEnabled() || ! static::showAcademicRegistrationLink()) {
            $items = array_values(array_filter($items, fn (array $item) => $item['key'] !== 'academic-registration'));
        }

        if (! static::showAcademicCurriculumLink()) {
            $items = array_values(array_filter($items, fn (array $item) => $item['key'] !== 'academic-curriculum'));
        }

        if (! CertificateAccessSettings::portalEnabled()) {
            $items = array_values(array_filter($items, fn (array $item) => $item['key'] !== 'certificates'));
        }

        return $items;
    }

    protected static function showAcademicCurriculumLink(): bool
    {
        $student = auth()->user()?->academicStudent;

        return $student?->batch_id !== null && $student->batch?->program_id !== null;
    }

    protected static function showAcademicRegistrationLink(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return true;
        }

        $student = $user->academicStudent;

        if (! $student) {
            return true;
        }

        return $student->academic_status === 'pending';
    }

    public static function isActive(string $key, string $active): bool
    {
        return $key === $active;
    }
}
