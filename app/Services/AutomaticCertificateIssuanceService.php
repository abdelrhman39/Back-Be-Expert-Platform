<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class AutomaticCertificateIssuanceService
{
    public function issueForStudentId(int $studentId, string $trigger = 'automatic'): ?Certificate
    {
        if (! CertificateAccessSettings::autoIssueEnabled()) {
            return null;
        }

        $result = Cache::lock('certificate-auto-issue:student:'.$studentId, 60)
            ->get(function () use ($studentId, $trigger): ?Certificate {
                $student = AcademicStudent::query()
                    ->with(['user', 'batch.program'])
                    ->find($studentId);

                if (! $student?->user_id
                    || ! CertificateAccessPolicy::studentMeetsVisibilityCondition($student)
                    || Certificate::query()
                        ->where('academic_student_id', $student->id)
                        ->where('source_type', 'platform')
                        ->exists()) {
                    return null;
                }

                $template = CertificateTemplate::query()
                    ->where('status', 'active')
                    ->orderByDesc('is_default')
                    ->orderByDesc('id')
                    ->first();

                if (! $template) {
                    return null;
                }

                $certificate = app(CertificateService::class)->issueForStudent(
                    student: $student,
                    issuer: null,
                    template: $template,
                    overrides: [
                        'metadata' => [
                            'issuance_mode' => 'automatic',
                            'issuance_trigger' => $trigger,
                            'visibility_policy' => CertificateAccessSettings::defaultVisibilityMode(),
                        ],
                    ],
                );

                if (CertificateAccessSettings::autoIssueNotificationsEnabled()) {
                    app(NotificationService::class)->notifyCertificateIssued($certificate);
                }

                return $certificate;
            });

        return $result instanceof Certificate ? $result : null;
    }

    public function processEligible(int $chunkSize = 100): int
    {
        if (! CertificateAccessSettings::autoIssueEnabled()) {
            return 0;
        }

        $count = 0;

        $this->eligibleStudentsQuery()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($students) use (&$count): void {
                foreach ($students as $student) {
                    if ($this->issueForStudentId($student->id, 'scheduled_scan')) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    private function eligibleStudentsQuery(): Builder
    {
        $query = AcademicStudent::query()
            ->whereNotNull('user_id')
            ->whereDoesntHave('certificates', fn (Builder $certificates) => $certificates
                ->where('source_type', 'platform'));

        return match (CertificateAccessSettings::defaultVisibilityMode()) {
            'after_graduation' => $query->where('academic_status', 'graduated'),
            'after_graduation_and_exam' => $query
                ->where('academic_status', 'graduated')
                ->whereHas('examAttempts', fn (Builder $attempts) => $attempts
                    ->where('status', 'graded')
                    ->where('passed', true)),
            'after_exam_pass' => $query->whereHas('examAttempts', fn (Builder $attempts) => $attempts
                ->where('status', 'graded')
                ->where('passed', true)),
            default => $query,
        };
    }
}
