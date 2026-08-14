<?php

namespace App\Support;

use App\Models\AcademicStaff;
use App\Models\User;

class InstructorPermissions
{
    /** @var array<string, array<int, string>> */
    protected static array $presets = [
        'instructor.viewer' => [
            'instructor.profile.view',
            'instructor.sections.view',
            'instructor.schedules.view',
            'instructor.teams.join_link.view',
            'instructor.zoom.join_link.view',
            'instructor.recordings.view',
            'instructor.sessions.view',
            'instructor.attendance.view',
            'instructor.grades.view',
            'instructor.content.view',
            'instructor.exams.view',
        ],
        'instructor.trainer' => [
            'instructor.profile.view',
            'instructor.profile.update',
            'instructor.sections.view',
            'instructor.sections.view_all_students',
            'instructor.schedules.view',
            'instructor.teams.join_link.view',
            'instructor.zoom.join_link.view',
            'instructor.zoom.meeting.start',
            'instructor.zoom.recording.control',
            'instructor.recordings.view',
            'instructor.sessions.view',
            'instructor.materials.upload',
            'instructor.materials.publish',
            'instructor.assignments.create',
            'instructor.assignments.publish',
            'instructor.assignments.grade',
            'instructor.attendance.view',
            'instructor.attendance.record_manual',
            'instructor.grades.view',
            'instructor.grades.enter',
            'instructor.content.view',
            'instructor.content.create',
            'instructor.content.update',
            'instructor.announcements.post',
            'instructor.messages.students',
            'instructor.exams.view',
            'instructor.exams.create',
            'instructor.exams.update',
            'instructor.exams.publish',
            'instructor.questions.manage',
            'instructor.exam_attempts.view',
            'instructor.exam_attempts.grade',
        ],
        'instructor.lead' => [
            'instructor.profile.view',
            'instructor.profile.update',
            'instructor.sections.view',
            'instructor.sections.view_all_students',
            'instructor.sections.export_roster',
            'instructor.schedules.view',
            'instructor.teams.join_link.view',
            'instructor.teams.meeting.create',
            'instructor.zoom.join_link.view',
            'instructor.zoom.meeting.create',
            'instructor.zoom.meeting.start',
            'instructor.zoom.meeting.update',
            'instructor.zoom.attendance.sync',
            'instructor.zoom.recording.control',
            'instructor.zoom.recording.sync',
            'instructor.recordings.view',
            'instructor.recordings.publish',
            'instructor.sessions.view',
            'instructor.materials.upload',
            'instructor.materials.publish',
            'instructor.materials.delete',
            'instructor.assignments.create',
            'instructor.assignments.publish',
            'instructor.assignments.grade',
            'instructor.assignments.delete',
            'instructor.attendance.view',
            'instructor.attendance.record_manual',
            'instructor.attendance.override',
            'instructor.attendance.export',
            'instructor.grades.view',
            'instructor.grades.enter',
            'instructor.content.view',
            'instructor.content.create',
            'instructor.content.update',
            'instructor.content.publish',
            'instructor.content.delete',
            'instructor.announcements.post',
            'instructor.messages.students',
            'instructor.messages.staff',
            'instructor.exams.view',
            'instructor.exams.create',
            'instructor.exams.update',
            'instructor.exams.publish',
            'instructor.exams.delete',
            'instructor.questions.manage',
            'instructor.exam_attempts.view',
            'instructor.exam_attempts.grade',
            'instructor.exam_reports.view',
            'instructor.exam_accommodations.manage',
        ],
        'instructor.coordinator' => [
            'instructor.profile.view',
            'instructor.profile.update',
            'instructor.sections.view',
            'instructor.sections.view_all_students',
            'instructor.schedules.view',
            'instructor.schedules.propose',
            'instructor.teams.join_link.view',
            'instructor.zoom.join_link.view',
            'instructor.zoom.meeting.start',
            'instructor.zoom.attendance.sync',
            'instructor.zoom.recording.control',
            'instructor.zoom.recording.sync',
            'instructor.recordings.view',
            'instructor.recordings.publish',
            'instructor.sessions.view',
            'instructor.materials.upload',
            'instructor.materials.publish',
            'instructor.assignments.create',
            'instructor.assignments.publish',
            'instructor.assignments.grade',
            'instructor.attendance.view',
            'instructor.attendance.record_manual',
            'instructor.grades.view',
            'instructor.grades.enter',
            'instructor.content.view',
            'instructor.content.create',
            'instructor.content.update',
            'instructor.announcements.post',
            'instructor.messages.students',
            'instructor.exams.view',
            'instructor.exams.create',
            'instructor.exams.update',
            'instructor.exams.publish',
            'instructor.questions.manage',
            'instructor.exam_attempts.view',
            'instructor.exam_attempts.grade',
            'instructor.exam_reports.view',
        ],
        'instructor.full' => [
            'instructor.profile.view',
            'instructor.profile.update',
            'instructor.sections.view',
            'instructor.sections.view_all_students',
            'instructor.sections.export_roster',
            'instructor.schedules.view',
            'instructor.schedules.propose',
            'instructor.teams.join_link.view',
            'instructor.teams.meeting.create',
            'instructor.teams.meeting.cancel',
            'instructor.zoom.join_link.view',
            'instructor.zoom.meeting.create',
            'instructor.zoom.meeting.start',
            'instructor.zoom.meeting.update',
            'instructor.zoom.meeting.cancel',
            'instructor.zoom.attendance.sync',
            'instructor.zoom.recording.control',
            'instructor.zoom.recording.sync',
            'instructor.recordings.view',
            'instructor.recordings.publish',
            'instructor.sessions.view',
            'instructor.materials.upload',
            'instructor.materials.publish',
            'instructor.materials.delete',
            'instructor.assignments.create',
            'instructor.assignments.publish',
            'instructor.assignments.grade',
            'instructor.assignments.delete',
            'instructor.attendance.view',
            'instructor.attendance.record_manual',
            'instructor.attendance.override',
            'instructor.attendance.export',
            'instructor.grades.view',
            'instructor.grades.enter',
            'instructor.grades.finalize',
            'instructor.content.view',
            'instructor.content.create',
            'instructor.content.update',
            'instructor.content.publish',
            'instructor.content.delete',
            'instructor.announcements.post',
            'instructor.messages.students',
            'instructor.messages.staff',
            'instructor.exams.view',
            'instructor.exams.create',
            'instructor.exams.update',
            'instructor.exams.publish',
            'instructor.exams.delete',
            'instructor.questions.manage',
            'instructor.exam_attempts.view',
            'instructor.exam_attempts.grade',
            'instructor.exam_reports.view',
            'instructor.exam_accommodations.manage',
        ],
    ];

    /** @return array<string, string> */
    public static function presetLabels(): array
    {
        return [
            'instructor.viewer' => 'مدرب — عرض فقط',
            'instructor.trainer' => 'مدرب — أساسي',
            'instructor.lead' => 'مدرب رئيسي',
            'instructor.coordinator' => 'منسق مقرر',
            'instructor.full' => 'صلاحيات موسعة',
        ];
    }

    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        $decision = AccessControl::decision($user, $permission);

        if ($decision !== null) {
            return $decision;
        }

        if (! $user->isInstructor()) {
            return false;
        }

        return static::legacyCan($user, $permission);
    }

    public static function legacyCan(User $user, string $permission): bool
    {
        $staff = $user->academicStaff;

        if (! $staff || $staff->status !== 'active') {
            return false;
        }

        $preset = self::presetFor($staff);
        $permissions = self::$presets[$preset];

        return in_array($permission, $permissions, true);
    }

    /** @return array<int, string> */
    public static function presetPermissions(string $preset): array
    {
        return self::$presets[$preset] ?? self::$presets['instructor.viewer'];
    }

    /** @return array<int, string> */
    public static function allPermissions(): array
    {
        return collect(self::$presets)->flatten()->unique()->sort()->values()->all();
    }

    /** @return array<string, array{label: string, group: string, scope: string}> */
    public static function definitions(): array
    {
        $groups = [
            'profile' => 'الملف الشخصي',
            'sections' => 'الشعب والطلاب',
            'schedules' => 'الجداول',
            'teams' => 'اجتماعات Teams',
            'zoom' => 'اجتماعات Zoom',
            'recordings' => 'التسجيلات',
            'sessions' => 'الحصص',
            'materials' => 'المواد والملفات',
            'assignments' => 'الواجبات',
            'attendance' => 'الحضور',
            'grades' => 'الدرجات',
            'content' => 'محتوى المقرر',
            'announcements' => 'الإعلانات',
            'messages' => 'التواصل',
            'exams' => 'الاختبارات',
            'questions' => 'بنك الأسئلة',
            'exam_attempts' => 'محاولات الاختبار',
            'exam_reports' => 'تقارير الاختبار',
            'exam_accommodations' => 'تسهيلات الاختبار',
        ];
        $actions = [
            'view' => 'عرض',
            'update' => 'تحديث',
            'create' => 'إنشاء',
            'publish' => 'نشر',
            'delete' => 'حذف',
            'upload' => 'رفع',
            'grade' => 'تصحيح',
            'enter' => 'إدخال',
            'finalize' => 'اعتماد نهائي',
            'override' => 'تجاوز',
            'export' => 'تصدير',
            'manage' => 'إدارة',
            'propose' => 'اقتراح',
            'cancel' => 'إلغاء',
            'start' => 'بدء',
            'sync' => 'مزامنة',
            'control' => 'تحكم',
            'students' => 'مراسلة الطلاب',
            'staff' => 'مراسلة الكادر',
            'record_manual' => 'تسجيل يدوي',
            'view_all_students' => 'عرض جميع الطلاب',
            'export_roster' => 'تصدير قائمة الطلاب',
        ];

        return collect(static::allPermissions())->mapWithKeys(function (string $permission) use ($groups, $actions): array {
            $parts = explode('.', $permission);
            $groupKey = $parts[1] ?? 'general';
            $actionKey = implode('_', array_slice($parts, 2));
            $groupLabel = $groups[$groupKey] ?? str_replace('_', ' ', $groupKey);

            return [$permission => [
                'label' => $groupLabel.' — '.($actions[$actionKey] ?? str_replace('_', ' ', $actionKey)),
                'group' => 'instructor.'.$groupKey,
                'scope' => 'instructor',
            ]];
        })->all();
    }

    public static function presetFor(AcademicStaff $staff): string
    {
        $preset = $staff->permission_preset;

        return is_string($preset) && array_key_exists($preset, self::$presets)
            ? $preset
            : 'instructor.viewer';
    }
}
