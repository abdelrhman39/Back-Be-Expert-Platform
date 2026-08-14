<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamPartQuestion;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamQuestionAuthoringService
{
    public function createAndAttach(
        Exam $exam,
        User $actor,
        string $type,
        string $prompt,
        ?string $explanation,
        string $difficulty,
        float $points,
        array $options = [],
        ?string $correctScalar = null,
        ?string $structuredAnswer = null,
        float $numericTolerance = 0,
        ?int $categoryId = null,
        array $tags = [],
        ?string $promptEn = null,
        ?string $explanationEn = null,
        ?string $structuredAnswerEn = null,
    ): ExamQuestion {
        [$answerKey, $optionRows] = $this->buildDefinition(
            $type,
            $options,
            $correctScalar,
            $structuredAnswer,
            $numericTolerance,
        );
        $optionRows = $this->applyEnglishContent($type, $optionRows, $options, $structuredAnswerEn);
        $answerKeyEn = $this->buildEnglishAnswerKey($type, $answerKey, $options, $correctScalar, $structuredAnswerEn, $numericTolerance);

        return DB::transaction(function () use (
            $exam,
            $actor,
            $type,
            $prompt,
            $explanation,
            $difficulty,
            $points,
            $answerKey,
            $answerKeyEn,
            $optionRows,
            $categoryId,
            $tags,
            $promptEn,
            $explanationEn,
        ) {
            $question = $this->createQuestion([
                'category_id' => $categoryId,
                'course_id' => $exam->course_id,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'type' => $type,
                'prompt' => $prompt,
                'prompt_en' => $promptEn,
                'explanation' => $explanation,
                'explanation_en' => $explanationEn,
                'default_points' => $points,
                'difficulty' => $difficulty,
                'scope' => 'course',
                'status' => 'published',
                'answer_key' => $answerKey,
                'answer_key_en' => $answerKeyEn,
                'settings' => [
                    'case_sensitive' => false,
                    'blank_count' => count($answerKey['blanks'] ?? []),
                ],
                'tags' => $tags,
                'published_at' => now(),
            ], $optionRows);

            $part = $exam->parts()->orderBy('sort_order')->firstOrFail();
            $part->questionLinks()->create([
                'question_id' => $question->id,
                'points' => $points,
                'sort_order' => $part->questionLinks()->max('sort_order') + 1,
                'is_required' => true,
            ]);
            $exam->refreshTotalPoints();

            return $question;
        });
    }

    public function updateAttached(
        Exam $exam,
        ExamPartQuestion $link,
        User $actor,
        string $type,
        string $prompt,
        ?string $explanation,
        string $difficulty,
        float $points,
        array $options = [],
        ?string $correctScalar = null,
        ?string $structuredAnswer = null,
        float $numericTolerance = 0,
        ?int $categoryId = null,
        array $tags = [],
        ?string $promptEn = null,
        ?string $explanationEn = null,
        ?string $structuredAnswerEn = null,
    ): ExamQuestion {
        [$answerKey, $optionRows] = $this->buildDefinition(
            $type,
            $options,
            $correctScalar,
            $structuredAnswer,
            $numericTolerance,
        );
        $optionRows = $this->applyEnglishContent($type, $optionRows, $options, $structuredAnswerEn);
        $answerKeyEn = $this->buildEnglishAnswerKey($type, $answerKey, $options, $correctScalar, $structuredAnswerEn, $numericTolerance);

        return DB::transaction(function () use (
            $exam,
            $link,
            $actor,
            $type,
            $prompt,
            $explanation,
            $difficulty,
            $points,
            $answerKey,
            $answerKeyEn,
            $optionRows,
            $categoryId,
            $tags,
            $promptEn,
            $explanationEn,
        ) {
            $link = ExamPartQuestion::query()
                ->with('question')
                ->lockForUpdate()
                ->findOrFail($link->id);
            $question = $link->question;
            $isShared = ExamPartQuestion::query()
                ->where('question_id', $question->id)
                ->where('id', '!=', $link->id)
                ->exists();

            $attributes = [
                'category_id' => $categoryId,
                'course_id' => $exam->course_id,
                'created_by' => $isShared ? $actor->id : $question->created_by,
                'updated_by' => $actor->id,
                'type' => $type,
                'prompt' => $prompt,
                'prompt_en' => $promptEn,
                'explanation' => $explanation,
                'explanation_en' => $explanationEn,
                'default_points' => $points,
                'difficulty' => $difficulty,
                'scope' => 'course',
                'status' => 'published',
                'answer_key' => $answerKey,
                'answer_key_en' => $answerKeyEn,
                'settings' => [
                    'case_sensitive' => false,
                    'blank_count' => count($answerKey['blanks'] ?? []),
                ],
                'tags' => $tags,
                'published_at' => $question->published_at ?? now(),
                'version' => $isShared ? 1 : $question->version + 1,
            ];

            if ($isShared) {
                $question = ExamQuestion::query()->create($attributes);
                $link->update(['question_id' => $question->id, 'points' => $points]);
            } else {
                $question->update($attributes);
                $question->options()->delete();
                $link->update(['points' => $points]);
            }

            foreach ($optionRows as $index => $row) {
                $question->options()->create([
                    'option_key' => $row['key'],
                    'content' => $row['content'],
                    'content_en' => $row['content_en'] ?? null,
                    'is_correct' => $row['correct'] ?? false,
                    'weight' => ($row['correct'] ?? false) ? 1 : 0,
                    'match_data' => $row['match_data'] ?? null,
                    'match_data_en' => $row['match_data_en'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }

            $exam->refreshTotalPoints();

            return $question->fresh('options');
        });
    }

    public function createForBank(
        Exam $exam,
        User $actor,
        string $type,
        string $prompt,
        ?string $explanation,
        string $difficulty,
        float $points,
        array $options = [],
        ?string $correctScalar = null,
        ?string $structuredAnswer = null,
        float $numericTolerance = 0,
        ?int $categoryId = null,
        array $tags = [],
        ?string $promptEn = null,
        ?string $explanationEn = null,
        ?string $structuredAnswerEn = null,
    ): ExamQuestion {
        [$answerKey, $optionRows] = $this->buildDefinition(
            $type,
            $options,
            $correctScalar,
            $structuredAnswer,
            $numericTolerance,
        );
        $optionRows = $this->applyEnglishContent($type, $optionRows, $options, $structuredAnswerEn);
        $answerKeyEn = $this->buildEnglishAnswerKey($type, $answerKey, $options, $correctScalar, $structuredAnswerEn, $numericTolerance);

        return $this->createQuestion([
            'category_id' => $categoryId,
            'course_id' => $exam->course_id,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'type' => $type,
            'prompt' => $prompt,
            'prompt_en' => $promptEn,
            'explanation' => $explanation,
            'explanation_en' => $explanationEn,
            'default_points' => $points,
            'difficulty' => $difficulty,
            'scope' => 'course',
            'status' => 'published',
            'answer_key' => $answerKey,
            'answer_key_en' => $answerKeyEn,
            'settings' => [
                'case_sensitive' => false,
                'blank_count' => count($answerKey['blanks'] ?? []),
            ],
            'tags' => $tags,
            'published_at' => now(),
        ], $optionRows);
    }

    private function createQuestion(array $attributes, array $optionRows): ExamQuestion
    {
        $question = ExamQuestion::query()->create($attributes);

        foreach ($optionRows as $index => $row) {
            $question->options()->create([
                'option_key' => $row['key'],
                'content' => $row['content'],
                'content_en' => $row['content_en'] ?? null,
                'is_correct' => $row['correct'] ?? false,
                'weight' => ($row['correct'] ?? false) ? 1 : 0,
                'match_data' => $row['match_data'] ?? null,
                'match_data_en' => $row['match_data_en'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }

        return $question;
    }

    private function applyEnglishContent(
        string $type,
        array $rows,
        array $options,
        ?string $structuredAnswerEn,
    ): array {
        if (in_array($type, ['single_choice', 'multiple_choice'], true)) {
            return collect($rows)->map(function (array $row, int $index) use ($options): array {
                $row['content_en'] = filled($options[$index]['content_en'] ?? null)
                    ? trim($options[$index]['content_en'])
                    : null;

                return $row;
            })->all();
        }

        if ($type === 'true_false') {
            foreach ($rows as &$row) {
                $row['content_en'] = $row['key'] === 'true' ? 'True' : 'False';
            }

            return $rows;
        }

        if (! in_array($type, ['ordering', 'matching'], true) || blank($structuredAnswerEn)) {
            return $rows;
        }

        $lines = collect(preg_split('/\r\n|\r|\n/', trim($structuredAnswerEn)) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        foreach ($rows as $index => &$row) {
            $line = $lines->get($index);
            if (! $line) {
                continue;
            }
            if ($type === 'matching') {
                $pair = array_map('trim', preg_split('/=>|=|←|→/u', $line, 2) ?: []);
                $row['content_en'] = $pair[0] ?? null;
                $row['match_data_en'] = isset($pair[1]) ? ['target' => $pair[1]] : null;
            } else {
                $row['content_en'] = $line;
            }
        }

        return $rows;
    }

    private function buildEnglishAnswerKey(
        string $type,
        ?array $fallback,
        array $options,
        ?string $correctScalar,
        ?string $structuredAnswerEn,
        float $numericTolerance,
    ): ?array {
        if (in_array($type, ['essay', 'file_upload'], true)) {
            return null;
        }

        if (in_array($type, ['single_choice', 'multiple_choice', 'true_false', 'numeric', 'ordering'], true)) {
            return $fallback;
        }

        if (blank($structuredAnswerEn)) {
            return null;
        }

        [$answerKey] = $this->buildDefinition(
            $type,
            collect($options)->map(fn (array $option) => [
                ...$option,
                'content' => $option['content_en'] ?? $option['content'] ?? '',
            ])->all(),
            $correctScalar,
            $structuredAnswerEn,
            $numericTolerance,
        );

        return $answerKey;
    }

    private function buildDefinition(
        string $type,
        array $options,
        ?string $correctScalar,
        ?string $structuredAnswer,
        float $numericTolerance,
    ): array {
        if (in_array($type, ['essay', 'file_upload'], true)) {
            return [null, []];
        }

        if (in_array($type, ['single_choice', 'multiple_choice'], true)) {
            $rows = collect($options)
                ->filter(fn ($option) => filled($option['content'] ?? null))
                ->values()
                ->map(fn ($option, $index) => [
                    'key' => chr(65 + $index),
                    'content' => trim($option['content']),
                    'correct' => $type === 'single_choice'
                        ? (string) $index === (string) $correctScalar
                        : (bool) ($option['correct'] ?? false),
                ])
                ->all();
            $correct = collect($rows)->where('correct', true)->pluck('key')->values()->all();

            if (count($rows) < 2) {
                throw ValidationException::withMessages(['options' => 'أضف خيارين على الأقل.']);
            }
            if ($type === 'single_choice' && count($correct) !== 1) {
                throw ValidationException::withMessages(['options' => 'حدد إجابة صحيحة واحدة فقط.']);
            }
            if ($type === 'multiple_choice' && $correct === []) {
                throw ValidationException::withMessages(['options' => 'حدد إجابة صحيحة واحدة على الأقل.']);
            }

            return [['correct' => $type === 'single_choice' ? $correct[0] : $correct], $rows];
        }

        if ($type === 'true_false') {
            $correct = $correctScalar === 'false' ? 'false' : 'true';

            return [['correct' => $correct], [
                ['key' => 'true', 'content' => 'صح', 'correct' => $correct === 'true'],
                ['key' => 'false', 'content' => 'خطأ', 'correct' => $correct === 'false'],
            ]];
        }

        if ($type === 'numeric') {
            if (! is_numeric($correctScalar)) {
                throw ValidationException::withMessages(['correctScalar' => 'أدخل إجابة رقمية صحيحة.']);
            }

            return [[
                'value' => (float) $correctScalar,
                'tolerance' => max(0, $numericTolerance),
            ], []];
        }

        $lines = collect(preg_split('/\r\n|\r|\n/', trim((string) $structuredAnswer)) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages(['structuredAnswer' => 'أدخل نموذج الإجابة.']);
        }

        return match ($type) {
            'short_text' => [['accepted' => $lines->all()], []],
            'fill_blank' => [['blanks' => $lines->map(fn ($line) => array_map('trim', explode('|', $line)))->all()], []],
            'ordering' => [
                ['order' => $lines->keys()->map(fn ($index) => 'I'.($index + 1))->all()],
                $lines->map(fn ($line, $index) => ['key' => 'I'.($index + 1), 'content' => $line, 'correct' => true])->all(),
            ],
            'matching' => $this->matchingDefinition($lines),
            default => [['accepted' => $lines->all()], []],
        };
    }

    private function matchingDefinition($lines): array
    {
        $matches = [];
        $rows = [];

        foreach ($lines as $index => $line) {
            $pair = array_map('trim', preg_split('/=>|=|←|→/u', $line, 2) ?: []);

            if (count($pair) !== 2 || blank($pair[0]) || blank($pair[1])) {
                throw ValidationException::withMessages([
                    'structuredAnswer' => 'اكتب كل مطابقة بصيغة: العنصر => الإجابة',
                ]);
            }

            $key = 'M'.($index + 1);
            $matches[$key] = $pair[1];
            $rows[] = [
                'key' => $key,
                'content' => $pair[0],
                'correct' => true,
                'match_data' => ['target' => $pair[1]],
            ];
        }

        return [['matches' => $matches], $rows];
    }
}
