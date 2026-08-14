<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'publication_id',
        'student_id',
        'attempt_number',
        'status',
        'language',
        'started_at',
        'expires_at',
        'last_activity_at',
        'submitted_at',
        'graded_at',
        'auto_score',
        'manual_score',
        'total_score',
        'percentage',
        'passed',
        'question_snapshot',
        'settings_snapshot',
        'ip_address',
        'user_agent',
        'integrity_flags',
        'integrity_review_status',
        'integrity_review_notes',
        'integrity_reviewed_by',
        'integrity_reviewed_at',
        'submission_reason',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
            'auto_score' => 'decimal:2',
            'manual_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
            'question_snapshot' => 'array',
            'settings_snapshot' => 'array',
            'integrity_reviewed_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(ExamPublication::class, 'publication_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'attempt_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ExamAttemptEvent::class, 'attempt_id');
    }

    public function integrityReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'integrity_reviewed_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->greaterThanOrEqualTo($this->expires_at);
    }

    public function remainingSeconds(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }

    public function effectiveTotalPoints(): float
    {
        return (float) (
            $this->settings_snapshot['total_points']
            ?? $this->publication?->total_points
            ?? $this->exam?->total_points
            ?? 0
        );
    }

    public function effectivePassingPercent(): float
    {
        return (float) (
            $this->settings_snapshot['passing_percent']
            ?? $this->publication?->settings_snapshot['passing_percent']
            ?? $this->exam?->passing_percent
            ?? 0
        );
    }

    public function effectiveExamTitle(): string
    {
        if ($this->language === 'en') {
            return (string) (
                $this->settings_snapshot['title_en']
                ?? $this->publication?->settings_snapshot['title_en']
                ?? $this->exam?->title_en
                ?? $this->settings_snapshot['title']
                ?? $this->exam?->title
                ?? 'Exam'
            );
        }

        return (string) (
            $this->settings_snapshot['title']
            ?? $this->publication?->settings_snapshot['title']
            ?? $this->exam?->title
            ?? 'اختبار'
        );
    }
}
