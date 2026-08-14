<?php

namespace App\Support;

class InstructorNavigation
{
    /** @return array<int, array{route: string, label: string, key: string, icon: string}> */
    public static function items(): array
    {
        $locale = app()->getLocale();

        $items = [
            [
                'key' => 'dashboard',
                'route' => route('instructor.dashboard', ['locale' => $locale]),
                'label' => 'لوحة المدرب',
                'icon' => 'fa-chalkboard-user',
                'permission' => 'instructor.profile.view',
            ],
            [
                'key' => 'sections',
                'route' => route('instructor.sections', ['locale' => $locale]),
                'label' => 'شعبي',
                'icon' => 'fa-users-rectangle',
                'permission' => 'instructor.sections.view',
            ],
            [
                'key' => 'assignments',
                'route' => route('instructor.assignments', ['locale' => $locale]),
                'label' => 'تصحيح الواجبات',
                'icon' => 'fa-clipboard-check',
                'permission' => 'instructor.assignments.grade',
            ],
            [
                'key' => 'attendance',
                'route' => route('instructor.attendance', ['locale' => $locale]),
                'label' => 'الحضور',
                'icon' => 'fa-user-check',
                'permission' => 'instructor.attendance.view',
            ],
            [
                'key' => 'exams',
                'route' => route('instructor.exams', ['locale' => $locale]),
                'label' => 'الاختبارات',
                'icon' => 'fa-file-circle-check',
                'permission' => 'instructor.exams.view',
            ],
            [
                'key' => 'notifications',
                'route' => route('instructor.notifications', ['locale' => $locale]),
                'label' => 'الإشعارات',
                'icon' => 'fa-bell',
                'permission' => 'instructor.profile.view',
            ],
            [
                'key' => 'settings',
                'route' => route('instructor.settings', ['locale' => $locale]),
                'label' => 'الإعدادات',
                'icon' => 'fa-gear',
                'permission' => 'instructor.profile.update',
            ],
        ];

        $user = auth()->user();

        return array_values(array_filter(
            $items,
            fn (array $item): bool => $user?->canInstructor($item['permission']) ?? false,
        ));
    }

    public static function isActive(string $key, string $active): bool
    {
        return $key === $active;
    }
}
