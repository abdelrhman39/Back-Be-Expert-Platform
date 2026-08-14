<?php

namespace App\Services;

use App\Models\ExamAnswer;

class ExamScoringService
{
    /** @return array{score: float, is_correct: ?bool, requires_manual: bool} */
    public function grade(ExamAnswer $answer): array
    {
        $snapshot = $answer->question_snapshot;
        $type = $snapshot['type'] ?? null;
        $points = (float) ($snapshot['points'] ?? 0);
        $key = $answer->grading_key ?? [];
        $settings = $snapshot['settings'] ?? [];

        if (! $this->hasResponse($answer)) {
            return ['score' => 0, 'is_correct' => false, 'requires_manual' => false];
        }

        if (in_array($type, ['essay', 'file_upload'], true)) {
            return ['score' => 0, 'is_correct' => null, 'requires_manual' => true];
        }

        $correct = match ($type) {
            'single_choice', 'true_false' => $this->scalarMatches(
                $this->answerValue($answer),
                $key['correct'] ?? $key['value'] ?? null,
                (bool) ($settings['case_sensitive'] ?? false),
            ),
            'multiple_choice' => $this->setMatches(
                (array) ($answer->answer['selected'] ?? $answer->answer ?? []),
                (array) ($key['correct'] ?? []),
            ),
            'short_text' => $this->textMatches(
                (string) ($answer->answer_text ?? $this->answerValue($answer) ?? ''),
                (array) ($key['accepted'] ?? []),
                (bool) ($settings['case_sensitive'] ?? false),
            ),
            'fill_blank' => $this->blanksMatch(
                (array) ($answer->answer['blanks'] ?? $answer->answer ?? []),
                (array) ($key['blanks'] ?? []),
                (bool) ($settings['case_sensitive'] ?? false),
            ),
            'matching' => $this->mapMatches(
                (array) ($answer->answer['matches'] ?? $answer->answer ?? []),
                (array) ($key['matches'] ?? []),
            ),
            'ordering' => $this->sequenceMatches(
                (array) ($answer->answer['order'] ?? $answer->answer ?? []),
                (array) ($key['order'] ?? []),
            ),
            'numeric' => $this->numericMatches(
                $this->answerValue($answer),
                $key['value'] ?? null,
                (float) ($key['tolerance'] ?? 0),
            ),
            default => false,
        };

        return [
            'score' => $correct ? $points : 0.0,
            'is_correct' => $correct,
            'requires_manual' => false,
        ];
    }

    private function answerValue(ExamAnswer $answer): mixed
    {
        return $answer->answer['value']
            ?? $answer->answer['selected']
            ?? $answer->answer_text;
    }

    private function scalarMatches(mixed $answer, mixed $expected, bool $caseSensitive): bool
    {
        if ($answer === null || $expected === null) {
            return false;
        }

        return $this->normalize((string) $answer, $caseSensitive)
            === $this->normalize((string) $expected, $caseSensitive);
    }

    /** @param array<int, mixed> $answer @param array<int, mixed> $expected */
    private function setMatches(array $answer, array $expected): bool
    {
        $answer = array_values(array_unique(array_map('strval', $answer)));
        $expected = array_values(array_unique(array_map('strval', $expected)));
        sort($answer);
        sort($expected);

        return $answer === $expected;
    }

    /** @param array<int, mixed> $accepted */
    private function textMatches(string $answer, array $accepted, bool $caseSensitive): bool
    {
        $normalized = $this->normalize($answer, $caseSensitive);

        return collect($accepted)->contains(
            fn ($value) => $normalized === $this->normalize((string) $value, $caseSensitive)
        );
    }

    /** @param array<int|string, mixed> $answer @param array<int|string, mixed> $expected */
    private function blanksMatch(array $answer, array $expected, bool $caseSensitive): bool
    {
        if (count($answer) !== count($expected)) {
            return false;
        }

        foreach ($expected as $index => $accepted) {
            $accepted = is_array($accepted) ? $accepted : [$accepted];

            if (! $this->textMatches((string) ($answer[$index] ?? ''), $accepted, $caseSensitive)) {
                return false;
            }
        }

        return true;
    }

    /** @param array<string|int, mixed> $answer @param array<string|int, mixed> $expected */
    private function mapMatches(array $answer, array $expected): bool
    {
        ksort($answer);
        ksort($expected);

        return array_map('strval', $answer) === array_map('strval', $expected);
    }

    /** @param array<int, mixed> $answer @param array<int, mixed> $expected */
    private function sequenceMatches(array $answer, array $expected): bool
    {
        return array_values(array_map('strval', $answer))
            === array_values(array_map('strval', $expected));
    }

    private function numericMatches(mixed $answer, mixed $expected, float $tolerance): bool
    {
        return is_numeric($answer)
            && is_numeric($expected)
            && abs((float) $answer - (float) $expected) <= max(0, $tolerance);
    }

    private function normalize(string $value, bool $caseSensitive): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);

        return $caseSensitive ? $value : mb_strtolower($value);
    }

    private function hasResponse(ExamAnswer $answer): bool
    {
        if (filled($answer->answer_text) || filled($answer->file_path)) {
            return true;
        }

        $values = [];
        $answerData = $answer->answer ?? [];
        array_walk_recursive($answerData, function ($value) use (&$values): void {
            $values[] = $value;
        });

        return collect($values)->contains(fn ($value) => $value !== null && $value !== '');
    }
}
