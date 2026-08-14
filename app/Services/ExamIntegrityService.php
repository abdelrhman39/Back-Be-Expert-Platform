<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamIntegrityService
{
    public const EVENT_WEIGHTS = [
        'page_hidden' => 1,
        'fullscreen_exit' => 2,
        'copy_attempt' => 2,
        'paste_attempt' => 2,
    ];

    public function record(
        ExamAttempt $attempt,
        string $eventType,
        ?array $metadata = null,
        ?string $ipAddress = null,
    ): bool {
        if (! array_key_exists($eventType, self::EVENT_WEIGHTS) || ! $attempt->isActive()) {
            return false;
        }

        return DB::transaction(function () use ($attempt, $eventType, $metadata, $ipAddress): bool {
            $attempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if (! $attempt->isActive()) {
                return false;
            }

            $duplicate = $attempt->events()
                ->where('event_type', $eventType)
                ->where('occurred_at', '>=', now()->subSeconds(2))
                ->exists();

            if ($duplicate) {
                return false;
            }

            $weight = self::EVENT_WEIGHTS[$eventType];
            $attempt->events()->create([
                'event_type' => $eventType,
                'metadata' => array_merge($metadata ?? [], ['risk_weight' => $weight]),
                'ip_address' => $ipAddress,
                'occurred_at' => now(),
            ]);
            $attempt->increment('integrity_flags', $weight);

            if ($attempt->integrity_review_status !== 'unreviewed') {
                $attempt->update([
                    'integrity_review_status' => 'unreviewed',
                    'integrity_reviewed_by' => null,
                    'integrity_reviewed_at' => null,
                ]);
            }

            return true;
        });
    }

    public function review(
        ExamAttempt $attempt,
        User $reviewer,
        string $status,
        ?string $notes = null,
    ): ExamAttempt {
        if (! in_array($status, ['cleared', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'reviewStatus' => 'حالة مراجعة النزاهة غير صالحة.',
            ]);
        }

        $attempt->update([
            'integrity_review_status' => $status,
            'integrity_review_notes' => filled($notes) ? trim($notes) : null,
            'integrity_reviewed_by' => $reviewer->id,
            'integrity_reviewed_at' => now(),
        ]);

        app(AuditLogService::class)->log(
            action: 'exam.integrity_reviewed',
            descriptionAr: 'مراجعة سجل نزاهة محاولة اختبار',
            group: 'exams',
            actor: $reviewer,
            subject: $attempt,
            subjectLabel: $attempt->effectiveExamTitle().' — محاولة '.$attempt->attempt_number,
            newValues: [
                'integrity_review_status' => $status,
                'integrity_flags' => $attempt->integrity_flags,
                'notes' => $notes,
            ],
        );

        return $attempt->fresh(['integrityReviewer']);
    }

    public function risk(int $score): array
    {
        return match (true) {
            $score >= 10 => ['key' => 'critical', 'label' => 'حرج', 'color' => 'danger'],
            $score >= 6 => ['key' => 'high', 'label' => 'مرتفع', 'color' => 'danger'],
            $score >= 3 => ['key' => 'medium', 'label' => 'متوسط', 'color' => 'warning'],
            $score >= 1 => ['key' => 'low', 'label' => 'منخفض', 'color' => 'info'],
            default => ['key' => 'clean', 'label' => 'سليم', 'color' => 'success'],
        };
    }

    public function eventLabel(string $eventType): string
    {
        return match ($eventType) {
            'page_hidden' => 'غادر صفحة الاختبار',
            'fullscreen_exit' => 'خرج من وضع ملء الشاشة',
            'copy_attempt' => 'حاول نسخ محتوى',
            'paste_attempt' => 'حاول لصق محتوى',
            'attempt_started' => 'بدأ المحاولة',
            'attempt_submitted' => 'سلّم المحاولة',
            default => $eventType,
        };
    }
}
