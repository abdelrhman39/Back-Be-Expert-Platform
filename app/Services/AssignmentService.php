<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Support\AssignmentOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssignmentService
{
    /** @return Collection<int, Assignment> */
    public function forStudent(?AcademicStudent $student): Collection
    {
        if (! $student?->section_id) {
            return collect();
        }

        return Assignment::query()
            ->with(['session', 'submissions' => fn ($q) => $q->where('student_id', $student->id)])
            ->where('section_id', $student->section_id)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->get();
    }

    /** @return Collection<int, Assignment> */
    public function forSession(AttendanceSession $session): Collection
    {
        return Assignment::query()
            ->where('section_id', $session->section_id)
            ->where('status', 'published')
            ->where(function ($q) use ($session) {
                $q->where('attendance_session_id', $session->id)
                    ->orWhere(function ($q) {
                        $q->where('scope', 'section')->whereNull('attendance_session_id');
                    });
            })
            ->orderByDesc('published_at')
            ->get();
    }

    public function studentCanAccess(User $user, Assignment $assignment): bool
    {
        $student = $user->academicStudent;

        return $student
            && $student->section_id === $assignment->section_id
            && $assignment->isPublished();
    }

    public function latestSubmission(Assignment $assignment, AcademicStudent $student): ?AssignmentSubmission
    {
        return AssignmentSubmission::query()
            ->where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->orderByDesc('attempt_number')
            ->first();
    }

    /** @param array<int, UploadedFile> $files */
    public function submit(
        Assignment $assignment,
        AcademicStudent $student,
        ?string $bodyText,
        ?string $submissionUrl,
        array $files,
        bool $finalize,
    ): AssignmentSubmission {
        if (! $assignment->acceptsSubmissions() && $finalize) {
            throw ValidationException::withMessages(['submit' => 'انتهى موعد التسليم ولا يُسمح بالتسليم المتأخر.']);
        }

        $attempt = $this->latestSubmission($assignment, $student);
        $attemptNumber = $attempt?->attempt_number ?? 0;

        if ($finalize) {
            if ($attempt && in_array($attempt->status, ['submitted', 'late', 'graded'], true)) {
                if ($attemptNumber >= $assignment->max_attempts) {
                    throw ValidationException::withMessages(['submit' => 'استنفدت عدد المحاولات المسموحة.']);
                }
                $attemptNumber++;
            } elseif (! $attempt) {
                $attemptNumber = 1;
            } else {
                $attemptNumber = $attempt->attempt_number;
            }
        } else {
            $attemptNumber = max(1, $attempt?->attempt_number ?? 1);
        }

        $status = 'draft';

        if ($finalize) {
            $status = $assignment->isOverdue() ? 'late' : 'submitted';
        }

        $submission = AssignmentSubmission::query()->updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'attempt_number' => $attemptNumber,
            ],
            [
                'body_text' => $bodyText,
                'submission_url' => $submissionUrl,
                'submitted_at' => $finalize ? now() : null,
                'status' => $status,
            ],
        );

        if ($files !== []) {
            $existingCount = $submission->files()->count();

            if ($existingCount + count($files) > $assignment->max_files) {
                throw ValidationException::withMessages([
                    'submissionFiles' => 'عدد الملفات يتجاوز الحد المسموح ('.$assignment->max_files.').',
                ]);
            }

            foreach ($files as $file) {
                $this->storeSubmissionFile($submission, $file);
            }
        }

        if ($finalize && blank($bodyText) && blank($submissionUrl) && $submission->files()->count() === 0) {
            throw ValidationException::withMessages(['submit' => 'أضف نصاً أو ملفاً أو رابطاً قبل التسليم.']);
        }

        return $submission->fresh(['files']);
    }

    public function grade(AssignmentSubmission $submission, int $score, ?string $feedback, User $grader): AssignmentSubmission
    {
        $max = $submission->assignment?->max_score ?? 100;
        $score = max(0, min($max, $score));

        $submission->update([
            'score' => $score,
            'feedback' => $feedback,
            'status' => 'graded',
            'graded_by' => $grader->id,
            'graded_at' => now(),
        ]);

        return $submission->fresh();
    }

    public function publish(Assignment $assignment): Assignment
    {
        $assignment->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        app(NotificationService::class)->notifyAssignmentPublished($assignment->fresh());

        return $assignment;
    }

    public function close(Assignment $assignment): Assignment
    {
        $assignment->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $assignment;
    }

    protected function storeSubmissionFile(AssignmentSubmission $submission, UploadedFile $file): SubmissionFile
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, AssignmentOptions::allowedExtensions(), true)) {
            throw ValidationException::withMessages(['submissionFiles' => 'نوع الملف غير مسموح: '.$extension]);
        }

        if ($file->getSize() > AssignmentOptions::maxFileKb() * 1024) {
            throw ValidationException::withMessages(['submissionFiles' => 'حجم الملف يتجاوز الحد المسموح.']);
        }

        $path = $file->store(
            "assignments/{$submission->assignment_id}/submissions/{$submission->id}",
            'public'
        );

        return SubmissionFile::query()->create([
            'assignment_submission_id' => $submission->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function deleteSubmissionFile(SubmissionFile $file): void
    {
        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();
    }
}
