<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Exam;
use App\Models\ExamAccommodation;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamPart;
use App\Models\ExamPublication;
use App\Models\ExamQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ExamAttemptService
{
    public function start(
        Exam $exam,
        AcademicStudent $student,
        ?string $accessCode = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $language = null,
    ): ExamAttempt {
        $expiredAttempt = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->latest('attempt_number')
            ->first();

        if ($expiredAttempt?->isExpired()) {
            $this->submit($expiredAttempt, 'time_expired');
        }

        return DB::transaction(function () use ($exam, $student, $accessCode, $ipAddress, $userAgent, $language) {
            $exam = Exam::query()->lockForUpdate()->findOrFail($exam->id);

            if (! $exam->studentIsEligible($student)) {
                throw ValidationException::withMessages(['exam' => 'أنت غير مدرج ضمن المرشحين لهذا الاختبار.']);
            }

            $active = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'in_progress')
                ->latest('attempt_number')
                ->first();

            if ($active && ! $active->isExpired()) {
                return $active;
            }

            $this->assertStudentCanStart($exam, $student, $accessCode);
            $accommodation = $this->accommodationFor($exam, $student);
            $publication = $exam->latestPublication();
            $publishedSettings = $publication?->settings_snapshot ?? [];
            $languagePolicy = $publishedSettings['language_policy'] ?? $exam->language_policy ?? 'ar_only';
            $attemptLanguage = match ($languagePolicy) {
                'en_only' => 'en',
                'student_choice' => in_array($language, ['ar', 'en'], true)
                    ? $language
                    : (app()->getLocale() === 'en' ? 'en' : 'ar'),
                'student_locale' => app()->getLocale() === 'en' ? 'en' : 'ar',
                default => 'ar',
            };
            $attemptNumber = (int) ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->max('attempt_number') + 1;
            $baseDuration = $publishedSettings['duration_minutes'] ?? $exam->duration_minutes;
            $duration = $baseDuration
                ? $baseDuration + (int) ($accommodation?->extra_time_minutes ?? 0)
                : null;
            $closesAt = $accommodation?->override_exam_availability
                ? $accommodation->closes_at
                : ($accommodation?->closes_at ?? $exam->closes_at);
            $expiresAt = $duration ? now()->addMinutes($duration) : null;

            if ($closesAt && (! $expiresAt || $closesAt->lessThan($expiresAt))) {
                $expiresAt = $closesAt;
            }

            $questions = $publication
                ? $this->selectPublicationQuestions($exam, $publication)
                : $this->selectQuestions($exam);
            $questionSnapshot = $questions->pluck('snapshot')->values()->all();

            $attempt = ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'publication_id' => $publication?->id,
                'student_id' => $student->id,
                'attempt_number' => $attemptNumber,
                'status' => 'in_progress',
                'language' => $attemptLanguage,
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'last_activity_at' => now(),
                'question_snapshot' => $questionSnapshot,
                'settings_snapshot' => [
                    'title' => $publishedSettings['title'] ?? $exam->title,
                    'title_en' => $publishedSettings['title_en'] ?? $exam->title_en,
                    'instructions' => $publishedSettings['instructions'] ?? $exam->instructions,
                    'instructions_en' => $publishedSettings['instructions_en'] ?? $exam->instructions_en,
                    'language_policy' => $languagePolicy,
                    'total_points' => (float) ($publication?->total_points ?? $exam->total_points),
                    'passing_percent' => (float) ($publishedSettings['passing_percent'] ?? $exam->passing_percent),
                    'shuffle_questions' => $publishedSettings['shuffle_questions'] ?? $exam->shuffle_questions,
                    'shuffle_options' => $publishedSettings['shuffle_options'] ?? $exam->shuffle_options,
                    'one_question_per_page' => $publishedSettings['one_question_per_page'] ?? $exam->one_question_per_page,
                    'allow_back_navigation' => $publishedSettings['allow_back_navigation'] ?? $exam->allow_back_navigation,
                    'review_policy' => $publishedSettings['review_policy'] ?? $exam->review_policy,
                    'result_release' => $publishedSettings['result_release'] ?? $exam->result_release,
                    'duration_minutes' => $duration,
                    'attempt_policy' => $publishedSettings['attempt_policy'] ?? $exam->attempt_policy,
                    'max_attempts' => $publishedSettings['max_attempts'] ?? $exam->max_attempts,
                    'grade_selection' => $publishedSettings['grade_selection'] ?? $exam->grade_selection,
                    'publication_version' => $publication?->version,
                    'publication_checksum' => $publication?->checksum,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            foreach ($questions as $selected) {
                /** @var ExamQuestion $question */
                $question = $selected['question'];
                ExamAnswer::query()->create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'status' => 'unanswered',
                    'question_snapshot' => $selected['snapshot'],
                    'grading_key' => $attemptLanguage === 'en'
                        ? ($selected['grading_key_en'] ?? $question->answer_key_en ?? $selected['grading_key'] ?? $question->answer_key)
                        : ($selected['grading_key'] ?? $question->answer_key),
                ]);
            }

            $this->recordEvent($attempt, 'attempt_started', [
                'attempt_number' => $attemptNumber,
                'question_count' => $questions->count(),
            ], $ipAddress);

            return $attempt->load('answers');
        });
    }

    public function saveAnswer(
        ExamAttempt $attempt,
        int $questionId,
        ?array $answer = null,
        ?string $answerText = null,
        bool $isFlagged = false,
        int $timeSpentSeconds = 0,
    ): ExamAnswer {
        $attempt->refresh();

        if ($attempt->isActive() && $attempt->isExpired()) {
            $this->submit($attempt, 'time_expired');
            throw ValidationException::withMessages([
                'attempt' => 'انتهى وقت الاختبار وتم تسليم المحاولة تلقائياً.',
            ]);
        }

        return DB::transaction(function () use (
            $attempt,
            $questionId,
            $answer,
            $answerText,
            $isFlagged,
            $timeSpentSeconds,
        ) {
            $attempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);
            $this->assertAttemptIsWritable($attempt);

            $examAnswer = ExamAnswer::query()
                ->where('attempt_id', $attempt->id)
                ->where('question_id', $questionId)
                ->firstOrFail();
            $hasAnswer = $answer !== null || filled($answerText) || filled($examAnswer->file_path);

            $examAnswer->update([
                'answer' => $answer,
                'answer_text' => $answerText,
                'status' => $hasAnswer ? 'answered' : 'unanswered',
                'answered_at' => $hasAnswer ? now() : null,
                'is_flagged' => $isFlagged,
                'time_spent_seconds' => max(
                    (int) $examAnswer->time_spent_seconds,
                    $timeSpentSeconds,
                ),
            ]);

            $attempt->update(['last_activity_at' => now()]);

            return $examAnswer->fresh();
        });
    }

    public function submit(ExamAttempt $attempt, string $reason = 'student_submit'): ExamAttempt
    {
        $submittedNow = false;

        $result = DB::transaction(function () use ($attempt, $reason, &$submittedNow) {
            $attempt = ExamAttempt::query()
                ->with(['exam', 'answers'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($attempt->status !== 'in_progress') {
                return $attempt;
            }

            $requiresManual = false;
            $autoScore = 0.0;
            $scoring = app(ExamScoringService::class);

            foreach ($attempt->answers as $answer) {
                $result = $scoring->grade($answer);
                $requiresManual = $requiresManual || $result['requires_manual'];
                $autoScore += $result['score'];

                $answer->update([
                    'auto_score' => $result['score'],
                    'is_correct' => $result['is_correct'],
                    'status' => $result['requires_manual']
                        ? 'pending_grading'
                        : ($answer->status === 'unanswered' ? 'unanswered' : 'graded'),
                    'graded_at' => $result['requires_manual'] ? null : now(),
                ]);
            }

            $status = $requiresManual ? 'pending_grading' : 'graded';
            $totalScore = $requiresManual ? null : $autoScore;
            $totalPoints = $attempt->effectiveTotalPoints();
            $passingPercent = $attempt->effectivePassingPercent();
            $percentage = $totalScore !== null && $totalPoints > 0
                ? round(($totalScore / $totalPoints) * 100, 2)
                : null;

            $attempt->update([
                'status' => $status,
                'submitted_at' => now(),
                'graded_at' => $requiresManual ? null : now(),
                'auto_score' => $autoScore,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'passed' => $percentage !== null
                    ? $percentage >= $passingPercent
                    : null,
                'submission_reason' => $reason,
                'last_activity_at' => now(),
            ]);
            $submittedNow = true;

            $this->recordEvent($attempt, 'attempt_submitted', [
                'reason' => $reason,
                'requires_manual_grading' => $requiresManual,
            ], $attempt->ip_address);

            return $attempt->fresh(['answers', 'exam']);
        });

        if ($submittedNow) {
            app(NotificationService::class)->notifyExamSubmitted($result);
            app(AuditLogService::class)->log(
                action: 'exam.attempt_submitted',
                descriptionAr: 'تسليم محاولة اختبار «'.$result->exam->title.'»',
                group: 'exams',
                actor: $result->student?->user,
                subject: $result,
                subjectLabel: $result->exam->title.' — محاولة '.$result->attempt_number,
                newValues: ['status' => $result->status, 'reason' => $reason],
            );
        }

        return $result;
    }

    public function recordEvent(
        ExamAttempt $attempt,
        string $eventType,
        ?array $metadata = null,
        ?string $ipAddress = null,
    ): void {
        $attempt->events()->create([
            'event_type' => $eventType,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
            'occurred_at' => now(),
        ]);
    }

    private function assertStudentCanStart(Exam $exam, AcademicStudent $student, ?string $accessCode): void
    {
        $accommodation = $this->accommodationFor($exam, $student);

        if (! $exam->isAvailableFor($student)) {
            throw ValidationException::withMessages(['exam' => 'الاختبار غير متاح حالياً.']);
        }

        if (
            $exam->require_access_code
            && ! $accommodation?->ignore_access_code
            && (! $accessCode || ! Hash::check($accessCode, (string) $exam->access_code_hash))
        ) {
            throw ValidationException::withMessages(['accessCode' => 'رمز دخول الاختبار غير صحيح.']);
        }

        $attemptsCount = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->count();
        $allowedAttempts = $exam->attemptLimitFor($student);

        if ($allowedAttempts !== null && $attemptsCount >= $allowedAttempts) {
            throw ValidationException::withMessages(['exam' => 'استنفدت جميع المحاولات المتاحة.']);
        }
    }

    private function assertAttemptIsWritable(ExamAttempt $attempt): void
    {
        if (! $attempt->isActive()) {
            throw ValidationException::withMessages(['attempt' => 'تم تسليم هذه المحاولة ولا يمكن تعديلها.']);
        }

        if ($attempt->isExpired()) {
            throw ValidationException::withMessages(['attempt' => 'انتهى وقت الاختبار.']);
        }
    }

    private function accommodationFor(Exam $exam, AcademicStudent $student): ?ExamAccommodation
    {
        return ExamAccommodation::query()
            ->where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();
    }

    /** @return Collection<int, array{question: ExamQuestion, snapshot: array}> */
    private function selectQuestions(Exam $exam): Collection
    {
        $exam->load([
            'parts.questions' => fn ($query) => $query
                ->where('status', 'published')
                ->with('options'),
        ]);

        $selected = collect();

        foreach ($exam->parts as $part) {
            /** @var ExamPart $part */
            $questions = $part->questions;
            $pool = $part->pool_filters ?? [];
            $usesAdvancedPool = ! empty($pool['question_ids']) && $part->questions_to_draw;

            if ($part->shuffle_questions || $exam->shuffle_questions) {
                $questions = $questions->shuffle();
            }

            if ($part->questions_to_draw && ! $usesAdvancedPool) {
                $questions = $questions->take($part->questions_to_draw);
            }

            foreach ($questions as $question) {
                $points = (float) $question->pivot->points;
                $selected->push($this->selectedQuestion($question, $part, $exam, $points));
            }

            if ($usesAdvancedPool) {
                $poolQuestions = ExamQuestion::withTrashed()
                    ->whereIn('id', $pool['question_ids'])
                    ->whereNotIn('id', $questions->pluck('id'))
                    ->with('options')
                    ->get()
                    ->shuffle()
                    ->take((int) $part->questions_to_draw);

                if ($poolQuestions->count() < (int) $part->questions_to_draw) {
                    throw ValidationException::withMessages([
                        'exam' => 'تعذر تكوين نموذج الاختبار لأن مجموعة الأسئلة العشوائية لم تعد مكتملة.',
                    ]);
                }

                foreach ($poolQuestions as $question) {
                    $selected->push($this->selectedQuestion(
                        $question,
                        $part,
                        $exam,
                        (float) ($pool['points_per_question'] ?? $question->default_points),
                    ));
                }
            }
        }

        if ($exam->shuffle_questions) {
            $selected = $selected->shuffle()->values();
        }

        return $selected;
    }

    /** @return Collection<int, array{question: ExamQuestion, snapshot: array, grading_key: ?array}> */
    private function selectPublicationQuestions(Exam $exam, ExamPublication $publication): Collection
    {
        $selected = collect();
        $blueprint = $publication->question_blueprint ?? [];
        $settings = $publication->settings_snapshot ?? [];
        $shuffleOptions = (bool) ($settings['shuffle_options'] ?? $exam->shuffle_options);

        foreach ($blueprint['parts'] ?? [] as $part) {
            foreach ($part['fixed'] ?? [] as $item) {
                $selected->push($this->publicationSelection($item, $part, $shuffleOptions));
            }

            $pool = $part['pool'] ?? [];
            $drawCount = (int) ($pool['draw_count'] ?? 0);

            if ($drawCount > 0) {
                $poolItems = collect($pool['items'] ?? [])->shuffle()->take($drawCount);

                if ($poolItems->count() < $drawCount) {
                    throw ValidationException::withMessages([
                        'exam' => 'نسخة النشر لا تحتوي عدداً كافياً من أسئلة المجموعة العشوائية.',
                    ]);
                }

                foreach ($poolItems as $item) {
                    $selected->push($this->publicationSelection($item, $part, $shuffleOptions));
                }
            }
        }

        if ((bool) ($settings['shuffle_questions'] ?? $exam->shuffle_questions)) {
            $selected = $selected->shuffle()->values();
        }

        return $selected;
    }

    private function publicationSelection(array $item, array $part, bool $shuffleOptions): array
    {
        $question = ExamQuestion::withTrashed()->findOrFail($item['question_id']);
        $snapshot = $item['snapshot'];
        $snapshot['part_id'] = $part['part_id'] ?? null;
        $snapshot['part_title'] = $part['title'] ?? '';

        if ($shuffleOptions && count($snapshot['options'] ?? []) > 1) {
            $snapshot['options'] = collect($snapshot['options'])->shuffle()->values()->all();
        }

        return [
            'question' => $question,
            'snapshot' => $snapshot,
            'grading_key' => $item['grading_key'] ?? null,
            'grading_key_en' => $item['grading_key_en'] ?? null,
        ];
    }

    private function selectedQuestion(
        ExamQuestion $question,
        ExamPart $part,
        Exam $exam,
        float $points,
    ): array {
        $snapshot = $question->snapshot($points);
        $snapshot['part_id'] = $part->id;
        $snapshot['part_title'] = $part->title;
        $snapshot['source'] = 'fixed';

        if (in_array($question->id, $part->pool_filters['question_ids'] ?? [], true)) {
            $snapshot['source'] = 'random_pool';
        }

        if ($exam->shuffle_options && count($snapshot['options']) > 1) {
            $snapshot['options'] = collect($snapshot['options'])->shuffle()->values()->all();
        }

        return [
            'question' => $question,
            'snapshot' => $snapshot,
            'grading_key' => $question->answer_key,
            'grading_key_en' => $question->answer_key_en,
        ];
    }
}
