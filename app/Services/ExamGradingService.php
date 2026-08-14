<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamGradingService
{
    public function gradeAnswer(
        ExamAnswer $answer,
        float $score,
        ?string $feedback,
        User $grader,
    ): ExamAttempt {
        return DB::transaction(function () use ($answer, $score, $feedback, $grader) {
            $answer = ExamAnswer::query()
                ->with('attempt.exam')
                ->lockForUpdate()
                ->findOrFail($answer->id);
            $maxPoints = (float) ($answer->question_snapshot['points'] ?? 0);

            if ($score < 0 || $score > $maxPoints) {
                throw ValidationException::withMessages([
                    'score' => "الدرجة يجب أن تكون بين 0 و {$maxPoints}.",
                ]);
            }

            $answer->update([
                'manual_score' => $score,
                'grader_feedback' => $feedback,
                'graded_by' => $grader->id,
                'graded_at' => now(),
                'status' => 'graded',
                'is_correct' => $maxPoints > 0 ? $score >= $maxPoints : null,
            ]);

            return $this->recalculateAttempt($answer->attempt);
        });
    }

    public function recalculateAttempt(ExamAttempt $attempt): ExamAttempt
    {
        $attempt->loadMissing(['exam', 'answers']);
        $pendingManual = $attempt->answers->contains(
            fn (ExamAnswer $answer) => in_array(
                $answer->question_snapshot['type'] ?? null,
                ['essay', 'file_upload'],
                true
            ) && $answer->status === 'pending_grading'
        );

        $autoScore = (float) $attempt->answers->sum('auto_score');
        $manualScore = (float) $attempt->answers->sum(
            fn (ExamAnswer $answer) => (float) ($answer->manual_score ?? 0)
        );

        if ($pendingManual) {
            $attempt->update([
                'status' => 'pending_grading',
                'auto_score' => $autoScore,
                'manual_score' => $manualScore,
                'total_score' => null,
                'percentage' => null,
                'passed' => null,
            ]);

            return $attempt->fresh();
        }

        $total = $autoScore + $manualScore;
        $totalPoints = $attempt->effectiveTotalPoints();
        $percentage = $totalPoints > 0
            ? round(($total / $totalPoints) * 100, 2)
            : 0;

        $attempt->update([
            'status' => 'graded',
            'auto_score' => $autoScore,
            'manual_score' => $manualScore,
            'total_score' => $total,
            'percentage' => $percentage,
            'passed' => $percentage >= $attempt->effectivePassingPercent(),
            'graded_at' => now(),
        ]);

        return $attempt->fresh();
    }
}
