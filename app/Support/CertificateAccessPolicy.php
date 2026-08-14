<?php

namespace App\Support;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\Exam;

class CertificateAccessPolicy
{
    public static function isVisible(Certificate $certificate): bool
    {
        if (CertificateAccessSettings::hideRevoked() && $certificate->status === 'revoked') {
            return false;
        }

        if ($certificate->isExternal()) {
            return $certificate->student_visible
                && (! $certificate->visible_from || $certificate->visible_from->isPast());
        }

        return $certificate->academicStudent
            ? static::studentMeetsVisibilityCondition($certificate->academicStudent)
            : CertificateAccessSettings::defaultVisibilityMode() === 'immediate';
    }

    public static function studentMeetsVisibilityCondition(AcademicStudent $student): bool
    {
        return match (CertificateAccessSettings::defaultVisibilityMode()) {
            'after_graduation' => static::hasGraduated($student),
            'after_exam_pass' => static::hasPassedRequiredExam($student),
            'after_graduation_and_exam' => static::hasGraduated($student)
                && static::hasPassedRequiredExam($student),
            default => true,
        };
    }

    public static function canDownload(Certificate $certificate): bool
    {
        if (! static::isVisible($certificate)
            || ! CertificateAccessSettings::downloadsEnabled()) {
            return false;
        }

        if (CertificateAccessSettings::requireActiveForDownload() && $certificate->status !== 'active') {
            return false;
        }

        return ! CertificateAccessSettings::requireIntegrityForDownload() || $certificate->hasValidIntegrity();
    }

    public static function canPrint(Certificate $certificate): bool
    {
        return static::isVisible($certificate)
            && CertificateAccessSettings::printingEnabled();
    }

    public static function showDetails(Certificate $certificate): bool
    {
        return CertificateAccessSettings::detailsEnabled();
    }

    public static function pendingReason(Certificate $certificate): ?string
    {
        if ($certificate->isExternal()) {
            if (! $certificate->student_visible || $certificate->visibility_mode === 'hidden') {
                return 'مخفية عن الطالب بقرار الإدارة.';
            }

            if ($certificate->visible_from?->isFuture()) {
                return 'مجدولة للظهور في '.$certificate->visible_from->format('Y-m-d H:i');
            }

            return null;
        }

        return match (CertificateAccessSettings::defaultVisibilityMode()) {
            'after_graduation' => static::hasGraduated($certificate)
                ? null
                : 'ستظهر بعد اعتماد حالة التخرج.',
            'after_exam_pass' => static::hasPassedRequiredExam($certificate)
                ? null
                : 'ستظهر بعد اجتياز الاختبار المطلوب.',
            'after_graduation_and_exam' => static::combinedPendingReason($certificate),
            default => null,
        };
    }

    private static function hasGraduated(AcademicStudent|Certificate $subject): bool
    {
        $student = $subject instanceof Certificate ? $subject->academicStudent : $subject;

        return $student?->academic_status === 'graduated';
    }

    private static function hasPassedRequiredExam(AcademicStudent|Certificate $subject): bool
    {
        $student = $subject instanceof Certificate ? $subject->academicStudent : $subject;
        if (! $student) {
            return false;
        }

        $examType = CertificateAccessSettings::requiredExamType();

        return Exam::query()
            ->when(
                $examType !== 'any',
                fn ($query) => $query->where('type', $examType),
                fn ($query) => $query->whereNot('type', 'practice'),
            )
            ->when($student->section_id, fn ($query) => $query->where('section_id', $student->section_id))
            ->whereHas('attempts', fn ($query) => $query
                ->where('student_id', $student->id)
                ->where('status', 'graded'))
            ->with(['attempts' => fn ($query) => $query
                ->where('student_id', $student->id)
                ->where('status', 'graded')])
            ->get()
            ->contains(fn (Exam $exam) => (bool) $exam->selectAttemptFrom($exam->attempts)?->passed);
    }

    private static function combinedPendingReason(Certificate $certificate): ?string
    {
        $graduated = static::hasGraduated($certificate);
        $passed = static::hasPassedRequiredExam($certificate);

        if (! $graduated && ! $passed) {
            return 'ستظهر بعد اعتماد التخرج واجتياز الاختبار المطلوب.';
        }

        if (! $graduated) {
            return 'تم اجتياز الاختبار؛ بانتظار اعتماد التخرج.';
        }

        return $passed ? null : 'تم اعتماد التخرج؛ بانتظار اجتياز الاختبار المطلوب.';
    }
}
