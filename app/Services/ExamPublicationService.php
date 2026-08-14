<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamPublication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamPublicationService
{
    public function publish(Exam $exam, User $actor): ExamPublication
    {
        $publication = DB::transaction(function () use ($exam, $actor): ExamPublication {
            $exam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            $report = app(ExamReadinessService::class)->inspect($exam, includeGradingKeys: true);

            if (! $report['ready']) {
                throw ValidationException::withMessages([
                    'publish' => collect($report['issues'])
                        ->where('severity', 'error')
                        ->map(fn (array $issue) => $issue['title'].': '.$issue['detail'])
                        ->values()
                        ->all(),
                ]);
            }

            $wasPublished = $exam->status === 'published';
            $version = (int) $exam->publications()->max('version') + 1;
            $blueprint = $report['blueprint'];
            $settings = [
                'title' => $exam->title,
                'title_en' => $exam->title_en,
                'instructions' => $exam->instructions,
                'instructions_en' => $exam->instructions_en,
                'type' => $exam->type,
                'language_policy' => $exam->language_policy,
                'opens_at' => $exam->opens_at?->toIso8601String(),
                'closes_at' => $exam->closes_at?->toIso8601String(),
                'duration_minutes' => $exam->duration_minutes,
                'max_attempts' => $exam->max_attempts,
                'attempt_policy' => $exam->attempt_policy,
                'grade_selection' => $exam->grade_selection,
                'passing_percent' => (float) $exam->passing_percent,
                'shuffle_questions' => $exam->shuffle_questions,
                'shuffle_options' => $exam->shuffle_options,
                'one_question_per_page' => $exam->one_question_per_page,
                'allow_back_navigation' => $exam->allow_back_navigation,
                'result_release' => $exam->result_release,
                'review_policy' => $exam->review_policy,
            ];
            $checksum = hash('sha256', json_encode([
                'blueprint' => $blueprint,
                'settings' => $settings,
                'total_points' => $report['total_points'],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            $publication = $exam->publications()->create([
                'version' => $version,
                'total_points' => $report['total_points'],
                'question_count' => $report['question_count'],
                'question_blueprint' => $blueprint,
                'settings_snapshot' => $settings,
                'checksum' => $checksum,
                'published_by' => $actor->id,
                'published_at' => now(),
            ]);

            $exam->snapshotCandidates();
            $exam->update([
                'total_points' => $report['total_points'],
                'status' => 'published',
                'published_at' => $exam->published_at ?? now(),
                'archived_at' => null,
            ]);

            if (! $wasPublished) {
                DB::afterCommit(fn () => app(NotificationService::class)
                    ->notifyExamPublished($exam->fresh()));
            }

            return $publication;
        });

        app(AuditLogService::class)->log(
            action: 'exam.publication_created',
            descriptionAr: "نشر النسخة {$publication->version} من اختبار «{$exam->title}»",
            group: 'exams',
            actor: $actor,
            subject: $exam,
            subjectLabel: $exam->title,
            newValues: [
                'publication_id' => $publication->id,
                'version' => $publication->version,
                'checksum' => $publication->checksum,
                'question_count' => $publication->question_count,
                'total_points' => $publication->total_points,
            ],
        );

        return $publication;
    }
}
