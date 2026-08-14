<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'section_id',
        'attendance_session_id',
        'created_by',
        'scope',
        'title',
        'instructions',
        'max_score',
        'due_at',
        'allow_late_submission',
        'late_penalty_percent',
        'max_attempts',
        'max_files',
        'allow_text_submission',
        'status',
        'published_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'allow_late_submission' => 'boolean',
            'allow_text_submission' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'archived'], true);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && now()->greaterThan($this->due_at);
    }

    public function acceptsSubmissions(): bool
    {
        if (! $this->isPublished() || $this->isClosed()) {
            return false;
        }

        if ($this->isOverdue() && ! $this->allow_late_submission) {
            return false;
        }

        return true;
    }
}
