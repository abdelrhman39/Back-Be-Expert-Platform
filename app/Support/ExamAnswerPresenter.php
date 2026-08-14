<?php

namespace App\Support;

use App\Models\ExamAnswer;

class ExamAnswerPresenter
{
    /**
     * @return array{
     *     type: string,
     *     options: array<int, array{key: string, content: string, selected: bool, correct: bool}>,
     *     student_label: string,
     *     correct_label: string,
     *     is_choice: bool,
     *     is_correct: bool|null,
     *     raw_text: string|null,
     *     has_file: bool,
     *     file_name: string|null
     * }
     */
    public static function present(ExamAnswer $answer): array
    {
        $snapshot = $answer->question_snapshot ?? [];
        $type = (string) ($snapshot['type'] ?? '');
        $options = collect($snapshot['options'] ?? [])->map(function ($option) {
            if (! is_array($option)) {
                return null;
            }

            return [
                'key' => (string) ($option['key'] ?? ''),
                'content' => (string) ($option['content'] ?? $option['key'] ?? ''),
            ];
        })->filter()->values();

        if ($type === 'true_false' && $options->isEmpty()) {
            $options = collect([
                ['key' => 'true', 'content' => 'صح'],
                ['key' => 'false', 'content' => 'خطأ'],
            ]);
        }

        $selectedKeys = self::selectedKeys($answer, $type);
        $correctKeys = self::correctKeys($answer, $type);
        $optionLabel = function (mixed $key) use ($options): string {
            $match = $options->firstWhere('key', (string) $key);

            return (string) ($match['content'] ?? $key);
        };

        $presentedOptions = $options->map(fn (array $option) => [
            'key' => $option['key'],
            'content' => $option['content'],
            'selected' => in_array($option['key'], $selectedKeys, true),
            'correct' => in_array($option['key'], $correctKeys, true),
        ])->all();

        $isChoice = in_array($type, ['single_choice', 'multiple_choice', 'true_false'], true);
        $studentLabel = match ($type) {
            'single_choice', 'true_false' => $optionLabel($answer->answer['value'] ?? ''),
            'multiple_choice' => collect($answer->answer['selected'] ?? [])->map($optionLabel)->filter()->join('، ') ?: '—',
            'short_text', 'essay' => (string) ($answer->answer_text ?? ''),
            'numeric' => (string) ($answer->answer['value'] ?? $answer->answer_text ?? ''),
            'matching' => collect($answer->answer['matches'] ?? [])
                ->map(fn ($value, $key) => $optionLabel($key).' ← '.$value)
                ->join(' | ') ?: '—',
            'ordering' => collect($answer->answer['order'] ?? [])->map($optionLabel)->join(' ← ') ?: '—',
            'file_upload' => $answer->file_original_name ?: ($answer->file_path ? 'ملف مرفوع' : '—'),
            default => $answer->answer_text
                ?: (is_array($answer->answer) ? json_encode($answer->answer, JSON_UNESCAPED_UNICODE) : (string) $answer->answer),
        };

        $key = $answer->grading_key ?? ($snapshot['answer_key'] ?? []);
        $correctLabel = match ($type) {
            'single_choice', 'true_false' => $optionLabel($key['correct'] ?? $key['value'] ?? ''),
            'multiple_choice' => collect($key['correct'] ?? [])->map($optionLabel)->filter()->join('، ') ?: '—',
            'short_text' => (string) ($key['text'] ?? $key['value'] ?? '—'),
            'numeric' => (string) ($key['value'] ?? '—'),
            'matching' => collect($key['matches'] ?? [])
                ->map(fn ($value, $option) => $optionLabel($option).' ← '.$value)
                ->join(' | ') ?: '—',
            'ordering' => collect($key['order'] ?? [])->map($optionLabel)->join(' ← ') ?: '—',
            default => '—',
        };

        $isCorrect = null;
        if ($answer->auto_score !== null && isset($snapshot['points'])) {
            $isCorrect = (float) $answer->auto_score >= (float) $snapshot['points'];
        } elseif ($isChoice && $selectedKeys !== [] && $correctKeys !== []) {
            sort($selectedKeys);
            sort($correctKeys);
            $isCorrect = $selectedKeys === $correctKeys;
        }

        return [
            'type' => $type,
            'options' => $presentedOptions,
            'student_label' => $studentLabel !== '' ? $studentLabel : '—',
            'correct_label' => $correctLabel,
            'is_choice' => $isChoice,
            'is_correct' => $isCorrect,
            'raw_text' => $answer->answer_text,
            'has_file' => filled($answer->file_path),
            'file_name' => $answer->file_original_name,
        ];
    }

    /** @return array<int, string> */
    private static function selectedKeys(ExamAnswer $answer, string $type): array
    {
        $payload = $answer->answer ?? [];

        return match ($type) {
            'single_choice', 'true_false' => array_values(array_filter([
                isset($payload['value']) ? (string) $payload['value'] : null,
            ])),
            'multiple_choice' => array_values(array_map('strval', $payload['selected'] ?? [])),
            default => [],
        };
    }

    /** @return array<int, string> */
    private static function correctKeys(ExamAnswer $answer, string $type): array
    {
        $key = $answer->grading_key ?? ($answer->question_snapshot['answer_key'] ?? []);

        return match ($type) {
            'single_choice', 'true_false' => array_values(array_filter([
                isset($key['correct']) ? (string) $key['correct'] : (isset($key['value']) ? (string) $key['value'] : null),
            ])),
            'multiple_choice' => array_values(array_map('strval', $key['correct'] ?? [])),
            default => [],
        };
    }
}
