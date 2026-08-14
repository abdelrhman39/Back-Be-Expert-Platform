<?php

namespace App\Services;

use App\Models\AcademicBatch;
use App\Models\AcademicStudent;
use App\Models\User;
use App\Support\AcademicStudentOptions;
use App\Support\InstallmentSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcademicRegistrationService
{
    public function isEnabled(): bool
    {
        return InstallmentSettings::academicRegistrationEnabled();
    }

    /** @return Collection<int, AcademicBatch> */
    public function openBatches(): Collection
    {
        return AcademicBatch::query()
            ->with('program')
            ->where('enrollment_open', true)
            ->where('status', 'active')
            ->whereNotNull('tuition_amount')
            ->where('tuition_amount', '>', 0)
            ->orderBy('name')
            ->get()
            ->filter(fn (AcademicBatch $batch) => $batch->installment_allowed);
    }

    public function userCanRegister(User $user): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $student = $user->academicStudent;

        if (! $student) {
            return true;
        }

        return $student->academic_status === 'pending';
    }

    public function existingStudent(User $user): ?AcademicStudent
    {
        return $user->academicStudent;
    }

    public function ensureBatchAvailable(AcademicBatch $batch): void
    {
        if (! $batch->enrollment_open || $batch->status !== 'active') {
            throw ValidationException::withMessages(['batchId' => 'التسجيل غير متاح لهذه الدفعة.']);
        }

        if (! $batch->installment_allowed) {
            throw ValidationException::withMessages(['batchId' => 'التقسيط غير متاح لهذه الدفعة.']);
        }

        $tuition = (float) $batch->tuition_amount;

        if ($tuition <= 0) {
            throw ValidationException::withMessages(['batchId' => 'لم تُحدَّد رسوم البرنامج لهذه الدفعة.']);
        }

        if ($batch->capacity !== null && $batch->students_count >= $batch->capacity) {
            throw ValidationException::withMessages(['batchId' => 'اكتملت سعة الدفعة.']);
        }
    }

    /** @param  array<string, mixed>  $profile */
    public function createOrRefreshPendingStudent(User $user, AcademicBatch $batch, array $profile = []): AcademicStudent
    {
        $this->ensureBatchAvailable($batch);

        $existing = $user->academicStudent;

        if ($existing && $existing->academic_status !== 'pending') {
            throw ValidationException::withMessages([
                'batchId' => 'لديك سجل أكاديمي نشط بالفعل ('.AcademicStudentOptions::academicStatusLabel($existing->academic_status).').',
            ]);
        }

        $data = [
            'batch_id' => $batch->id,
            'user_id' => $user->id,
            'name_ar' => $user->name_ar ?: $user->name,
            'name_en' => $user->name_en,
            'national_id' => $user->national_id,
            'mobile' => $user->phone,
            'email' => $user->email,
            'gender' => $profile['gender'] ?? $existing?->gender,
            'city' => $profile['city'] ?? $existing?->city,
            'nationality' => $profile['nationality'] ?? $existing?->nationality,
            'employment_status' => $profile['employment_status'] ?? $existing?->employment_status,
            'study_period' => $profile['study_period'] ?? $existing?->study_period,
            'academic_status' => 'pending',
            'study_status' => AcademicStudentOptions::academicStatusLabel('pending'),
            'login_allowed' => true,
            'joined_at' => now(),
        ];

        if ($existing) {
            $existing->update($data);

            return $existing->fresh(['batch.program']);
        }

        $data['academic_id'] = $this->generateAcademicId();

        return AcademicStudent::query()->create($data)->fresh(['batch.program']);
    }

    protected function generateAcademicId(): string
    {
        do {
            $id = now()->format('y').Str::padLeft((string) random_int(1, 999999), 6, '0');
        } while (AcademicStudent::query()->where('academic_id', $id)->exists());

        return $id;
    }
}
