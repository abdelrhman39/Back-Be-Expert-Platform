<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\Statement;
use App\Models\User;
use App\Support\StatementOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StatementService
{
    /** @return Collection<int, Statement> */
    public function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Statement::query()
            ->where('user_id', $user->id)
            ->latest('requested_at')
            ->get();
    }

    public function request(User $user, string $type, ?string $notes = null): Statement
    {
        $student = $user->academicStudent;
        $title = StatementOptions::typeLabel($type);

        return Statement::query()->create([
            'reference_no' => $this->nextReference(),
            'user_id' => $user->id,
            'academic_student_id' => $student?->id,
            'type' => $type,
            'title' => $title,
            'status' => 'pending',
            'student_notes' => $notes,
            'payload' => $this->buildPayload($user, $student),
            'requested_at' => now(),
        ]);
    }

    public function issue(Statement $statement, User $issuer, ?string $adminNotes = null): Statement
    {
        $statement->update([
            'status' => 'issued',
            'issued_at' => now(),
            'issued_by' => $issuer->id,
            'admin_notes' => $adminNotes ?? $statement->admin_notes,
        ]);

        return $statement->fresh();
    }

    public function reject(Statement $statement, User $reviewer, string $reason): Statement
    {
        $statement->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'issued_by' => $reviewer->id,
        ]);

        return $statement->fresh();
    }

    /** @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Statement> */
    public function adminList(?string $status = null, ?string $search = null, int $perPage = 20)
    {
        return Statement::query()
            ->with(['user', 'academicStudent'])
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when(filled($search), function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name_ar', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest('requested_at')
            ->paginate($perPage);
    }

    protected function nextReference(): string
    {
        do {
            $ref = 'ST-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (Statement::query()->where('reference_no', $ref)->exists());

        return $ref;
    }

    /** @return array<string, mixed> */
    protected function buildPayload(User $user, ?AcademicStudent $student): array
    {
        $student?->loadMissing(['batch.program', 'section']);

        return [
            'holder_name' => $user->displayName(),
            'national_id' => $user->national_id ?? $student?->national_id,
            'academic_id' => $student?->academic_id,
            'program_name' => $student?->batch?->program?->name_ar,
            'section_name' => $student?->section?->name ?? $student?->section?->subtitle,
            'study_status' => $student?->study_status,
            'academic_status' => $student?->academic_status,
        ];
    }
}
