<?php

namespace App\Observers;

use App\Models\AcademicBatch;
use App\Models\AcademicStudent;
use App\Services\AutomaticCertificateIssuanceService;
use Illuminate\Support\Facades\DB;

class AcademicStudentObserver
{
    public function saved(AcademicStudent $student): void
    {
        $this->syncBatch($student->batch_id);

        if ($student->wasChanged('batch_id') && $student->getOriginal('batch_id')) {
            $this->syncBatch((int) $student->getOriginal('batch_id'));
        }

        if ($student->wasRecentlyCreated || $student->wasChanged('academic_status')) {
            $studentId = (int) $student->id;
            $trigger = $student->academic_status === 'graduated'
                ? 'graduation_approved'
                : 'student_status_changed';

            DB::afterCommit(function () use ($studentId, $trigger): void {
                app(AutomaticCertificateIssuanceService::class)
                    ->issueForStudentId($studentId, $trigger);
            });
        }
    }

    public function deleted(AcademicStudent $student): void
    {
        $this->syncBatch($student->batch_id);
    }

    protected function syncBatch(?int $batchId): void
    {
        if (! $batchId) {
            return;
        }

        $batch = AcademicBatch::query()->find($batchId);
        $batch?->refreshStudentsCount();
    }
}
