<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Exam;
use App\Models\ExamPart;
use App\Models\ExamQuestion;
use App\Support\ExamOptions;
use Illuminate\Support\Collection;

class ExamReadinessService
{
    private string $languagePolicy = 'ar_only';

    public function inspect(Exam $exam, bool $includeGradingKeys = false): array
    {
        $this->languagePolicy = $exam->language_policy ?? 'ar_only';
        $exam->load(['section', 'course', 'parts.questionLinks']);
        $issues = collect();
        $seenQuestionIds = collect();
        $parts = [];
        $totalPoints = 0.0;
        $questionCount = 0;
        $manualCount = 0;

        $this->validateSettings($exam, $issues);

        foreach ($exam->parts as $part) {
            $partResult = $this->buildPart(
                $part,
                $issues,
                $seenQuestionIds,
                $includeGradingKeys,
            );
            $parts[] = $partResult['blueprint'];
            $totalPoints += $partResult['total_points'];
            $questionCount += $partResult['question_count'];
            $manualCount += $partResult['manual_count'];
        }

        if ($questionCount === 0) {
            $this->issue($issues, 'error', 'لا توجد أسئلة', 'أضف سؤالاً ثابتاً أو مجموعة عشوائية قبل النشر.', 'questions');
        }

        if ($manualCount > 0) {
            $this->issue(
                $issues,
                'warning',
                'الاختبار يحتاج تصحيحاً يدوياً',
                "{$manualCount} سؤالاً مقالياً أو ملفاً مرفوعاً لن تكتمل نتيجته قبل التصحيح.",
                'grading',
            );
        }

        $candidateCount = AcademicStudent::query()
            ->where('section_id', $exam->section_id)
            ->count();

        if ($candidateCount === 0) {
            $this->issue($issues, 'warning', 'لا يوجد طلاب مرشحون', 'الشعبة الحالية لا تحتوي طلاباً لإسناد الاختبار إليهم.', 'candidates');
        }

        $errors = $issues->where('severity', 'error')->count();
        $warnings = $issues->where('severity', 'warning')->count();
        $score = max(0, 100 - ($errors * 18) - ($warnings * 5));

        return [
            'ready' => $errors === 0,
            'score' => $score,
            'errors_count' => $errors,
            'warnings_count' => $warnings,
            'issues' => $issues->values()->all(),
            'question_count' => $questionCount,
            'manual_count' => $manualCount,
            'candidate_count' => $candidateCount,
            'total_points' => round($totalPoints, 2),
            'blueprint' => [
                'exam_id' => $exam->id,
                'parts' => $parts,
            ],
        ];
    }

    private function buildPart(
        ExamPart $part,
        Collection $issues,
        Collection $seenQuestionIds,
        bool $includeGradingKeys,
    ): array {
        $fixed = [];
        $poolItems = [];
        $fixedPoints = 0.0;
        $manualCount = 0;

        foreach ($part->questionLinks->sortBy('sort_order') as $link) {
            $question = ExamQuestion::withTrashed()->with('options')->find($link->question_id);

            if (! $question) {
                $this->issue($issues, 'error', 'سؤال مفقود', "تعذر العثور على السؤال المرتبط بالجزء «{$part->title}».", 'questions');

                continue;
            }

            $this->validateQuestion($question, $issues);
            $this->detectDuplicate($question, $seenQuestionIds, $issues);
            $points = (float) $link->points;

            if ($points <= 0) {
                $this->issue($issues, 'error', 'درجة غير صالحة', "السؤال رقم {$question->id} درجته صفر أو أقل.", 'questions');
            }

            $fixed[] = $this->questionItem(
                $question,
                $points,
                'fixed',
                (int) $link->sort_order,
                $includeGradingKeys,
            );
            $fixedPoints += $points;
            $manualCount += in_array($question->type, ['essay', 'file_upload'], true) ? 1 : 0;
        }

        $pool = $part->pool_filters ?? [];
        $drawCount = ! empty($pool['question_ids']) ? (int) ($part->questions_to_draw ?? 0) : 0;
        $poolPoints = (float) ($pool['points_per_question'] ?? 1);

        if ($drawCount > 0) {
            $poolQuestions = ExamQuestion::withTrashed()
                ->with('options')
                ->whereIn('id', $pool['question_ids'])
                ->get();

            foreach ($poolQuestions as $question) {
                $this->validateQuestion($question, $issues, pool: true);
                $this->detectDuplicate($question, $seenQuestionIds, $issues);
                $poolItems[] = $this->questionItem(
                    $question,
                    $poolPoints,
                    'random_pool',
                    count($poolItems) + 1,
                    $includeGradingKeys,
                );
            }

            if ($poolPoints <= 0) {
                $this->issue($issues, 'error', 'درجة المجموعة غير صالحة', "درجة السؤال في مجموعة «{$part->title}» يجب أن تكون أكبر من صفر.", 'pool');
            }

            if (count($poolItems) < $drawCount) {
                $this->issue(
                    $issues,
                    'error',
                    'المجموعة العشوائية غير مكتملة',
                    'المتاح '.count($poolItems)." أسئلة بينما المطلوب {$drawCount}. أعد حفظ إعدادات المجموعة.",
                    'pool',
                );
            }

            $manualPoolCount = collect($poolItems)
                ->whereIn('type', ['essay', 'file_upload'])
                ->count();
            $manualCount += min($drawCount, $manualPoolCount);
        }

        return [
            'blueprint' => [
                'part_id' => $part->id,
                'title' => $part->title,
                'instructions' => $part->instructions,
                'sort_order' => $part->sort_order,
                'fixed' => $fixed,
                'pool' => [
                    'draw_count' => $drawCount,
                    'points_per_question' => $poolPoints,
                    'items' => $poolItems,
                ],
            ],
            'question_count' => count($fixed) + $drawCount,
            'total_points' => $fixedPoints + ($drawCount * $poolPoints),
            'manual_count' => $manualCount,
        ];
    }

    private function validateSettings(Exam $exam, Collection $issues): void
    {
        if (blank($exam->title) || ! $exam->course_id || ! $exam->section_id) {
            $this->issue($issues, 'error', 'بيانات الاختبار غير مكتملة', 'العنوان والمقرر والشعبة بيانات إلزامية.', 'settings');
        }

        if ($this->languagePolicy !== 'ar_only' && blank($exam->title_en)) {
            $this->issue($issues, 'error', 'العنوان الإنجليزي مفقود', 'سياسة اللغة الحالية تتطلب عنواناً إنجليزياً للاختبار.', 'languages');
        }

        if ($exam->opens_at && $exam->closes_at && $exam->closes_at->lessThanOrEqualTo($exam->opens_at)) {
            $this->issue($issues, 'error', 'فترة الإتاحة غير صحيحة', 'وقت الإغلاق يجب أن يكون بعد وقت الفتح.', 'settings');
        }

        if ($exam->closes_at && $exam->closes_at->isPast()) {
            $this->issue($issues, 'warning', 'موعد الإغلاق مضى', 'لن يتمكن الطلاب من بدء الاختبار قبل تعديل موعد الإغلاق.', 'settings');
        }

        if ($exam->require_access_code && blank($exam->access_code_hash)) {
            $this->issue($issues, 'error', 'رمز الدخول غير محفوظ', 'أوقف طلب الرمز أو عيّن رمز دخول من إعدادات الاختبار.', 'settings');
        }

        if ((float) $exam->passing_percent < 0 || (float) $exam->passing_percent > 100) {
            $this->issue($issues, 'error', 'نسبة النجاح غير صالحة', 'يجب أن تكون نسبة النجاح بين 0 و100.', 'settings');
        }
    }

    private function validateQuestion(ExamQuestion $question, Collection $issues, bool $pool = false): void
    {
        $location = $pool ? 'المجموعة العشوائية' : 'الأسئلة الثابتة';

        if ($question->trashed() || $question->status !== 'published') {
            $this->issue($issues, 'error', 'سؤال غير متاح', "السؤال رقم {$question->id} في {$location} محذوف أو غير منشور.", 'questions');
        }

        if (blank(strip_tags($question->prompt))) {
            $this->issue($issues, 'error', 'نص سؤال فارغ', "السؤال رقم {$question->id} لا يحتوي نصاً.", 'questions');
        }

        if ($this->languagePolicy !== 'ar_only' && blank(strip_tags((string) $question->prompt_en))) {
            $this->issue($issues, 'error', 'ترجمة السؤال مفقودة', "السؤال رقم {$question->id} لا يحتوي نصاً إنجليزياً.", 'languages');
        }

        if (in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true)) {
            $correct = $question->options->where('is_correct', true)->count();
            $validCorrectCount = $question->type === 'multiple_choice'
                ? $correct >= 1
                : $correct === 1;

            if ($question->options->count() < 2 || ! $validCorrectCount) {
                $this->issue($issues, 'error', 'خيارات السؤال غير مكتملة', "راجع خيارات وإجابة السؤال رقم {$question->id}.", 'questions');
            }
            if ($this->languagePolicy !== 'ar_only' && $question->options->contains(fn ($option) => blank($option->content_en))) {
                $this->issue($issues, 'error', 'ترجمة الخيارات غير مكتملة', "راجع الخيارات الإنجليزية للسؤال رقم {$question->id}.", 'languages');
            }
        } elseif (in_array($question->type, ExamOptions::autoGradableTypes(), true) && empty($question->answer_key)) {
            $this->issue($issues, 'error', 'نموذج الإجابة مفقود', "السؤال رقم {$question->id} لا يحتوي نموذج إجابة للتصحيح الآلي.", 'questions');
        }

        if (
            $this->languagePolicy !== 'ar_only'
            && in_array($question->type, ['short_text', 'fill_blank', 'matching'], true)
            && empty($question->answer_key_en)
        ) {
            $this->issue($issues, 'error', 'نموذج الإجابة الإنجليزي مفقود', "السؤال رقم {$question->id} يحتاج نموذج إجابة إنجليزياً للتصحيح الآلي.", 'languages');
        }
    }

    private function detectDuplicate(
        ExamQuestion $question,
        Collection $seenQuestionIds,
        Collection $issues,
    ): void {
        if ($seenQuestionIds->contains($question->id)) {
            $this->issue(
                $issues,
                'error',
                'سؤال مكرر في النموذج',
                "السؤال رقم {$question->id} موجود في أكثر من موضع أو مجموعة. أزل التكرار قبل النشر.",
                'questions',
            );

            return;
        }

        $seenQuestionIds->push($question->id);
    }

    private function questionItem(
        ExamQuestion $question,
        float $points,
        string $source,
        int $sortOrder,
        bool $includeGradingKeys,
    ): array {
        $snapshot = $question->snapshot($points);
        $snapshot['source'] = $source;

        $item = [
            'question_id' => $question->id,
            'question_version' => $question->version,
            'type' => $question->type,
            'points' => $points,
            'sort_order' => $sortOrder,
            'snapshot' => $snapshot,
        ];

        if ($includeGradingKeys) {
            $item['grading_key'] = $question->answer_key;
            $item['grading_key_en'] = $question->answer_key_en;
        }

        return $item;
    }

    private function issue(
        Collection $issues,
        string $severity,
        string $title,
        string $detail,
        string $section,
    ): void {
        $issues->push(compact('severity', 'title', 'detail', 'section'));
    }
}
