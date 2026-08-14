<?php

namespace App\Models;

use App\Support\AcademicRequestOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicRequest extends Model
{
    protected $fillable = [
        'request_no',
        'type',
        'student_id',
        'student_name',
        'student_national_id',
        'program_id',
        'program_name',
        'semester_key',
        'semester_label',
        'status',
        'review_status',
        'reason',
        'payload',
        'reviewer_id',
        'reviewed_at',
        'admin_notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class, 'student_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function typeLabel(): string
    {
        return AcademicRequestOptions::typeLabel($this->type);
    }

    public function statusLabel(): string
    {
        return AcademicRequestOptions::statusLabel($this->status);
    }

    public function reviewStatusLabel(): string
    {
        return AcademicRequestOptions::reviewStatusLabel($this->review_status);
    }

    public function listRoute(): string
    {
        return AcademicRequestOptions::listRoute($this->type);
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->payload, $key, $default);
    }

    public function canReview(): bool
    {
        return in_array($this->status, ['pending', 'processing'], true);
    }

    public function approve(?User $reviewer = null, ?string $notes = null): array
    {
        if (! $reviewer) {
            $this->update([
                'status' => 'approved',
                'review_status' => 'reviewed',
                'reviewed_at' => now(),
                'admin_notes' => $notes ?: $this->admin_notes,
            ]);

            return ['request' => $this->fresh(), 'effects' => []];
        }

        return app(\App\Services\AcademicRequestService::class)->decide($this, 'approved', $reviewer, $notes);
    }

    public function reject(?User $reviewer = null, ?string $notes = null): array
    {
        if (! $reviewer) {
            $this->update([
                'status' => 'rejected',
                'review_status' => 'reviewed',
                'reviewed_at' => now(),
                'admin_notes' => $notes,
            ]);

            return ['request' => $this->fresh(), 'effects' => []];
        }

        return app(\App\Services\AcademicRequestService::class)->decide($this, 'rejected', $reviewer, $notes);
    }

    public function markProcessing(?User $reviewer = null): array
    {
        if (! $reviewer) {
            if ($this->status === 'pending') {
                $this->update(['status' => 'processing']);
            }

            return ['request' => $this->fresh(), 'effects' => []];
        }

        return app(\App\Services\AcademicRequestService::class)->decide($this, 'processing', $reviewer);
    }

    public function belongsToUser(User $user): bool
    {
        if (! $this->student_id) {
            return false;
        }

        return app(\App\Services\AcademicRequestService::class)->resolveStudent($user)?->id === $this->student_id;
    }
}
