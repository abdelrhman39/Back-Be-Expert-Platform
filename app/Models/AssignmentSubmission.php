<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'attempt_number',
        'body_text',
        'submission_url',
        'submitted_at',
        'status',
        'score',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->score !== null;
    }

    public function finalScore(): ?int
    {
        if ($this->score === null) {
            return null;
        }

        $score = (int) $this->score;
        $assignment = $this->assignment;

        if ($assignment && $this->status === 'late' && $assignment->late_penalty_percent > 0) {
            $penalty = (int) round($score * ($assignment->late_penalty_percent / 100));

            return max(0, $score - $penalty);
        }

        return $score;
    }
}
