<?php

namespace App\Observers;

use App\Models\ExamAttempt;
use App\Services\AutomaticCertificateIssuanceService;
use Illuminate\Support\Facades\DB;

class ExamAttemptObserver
{
    public function saved(ExamAttempt $attempt): void
    {
        if (! $attempt->passed || $attempt->status !== 'graded') {
            return;
        }

        if (! $attempt->wasRecentlyCreated
            && ! $attempt->wasChanged(['passed', 'status', 'graded_at'])) {
            return;
        }

        $studentId = (int) $attempt->student_id;

        DB::afterCommit(function () use ($studentId): void {
            app(AutomaticCertificateIssuanceService::class)
                ->issueForStudentId($studentId, 'exam_passed');
        });
    }
}
