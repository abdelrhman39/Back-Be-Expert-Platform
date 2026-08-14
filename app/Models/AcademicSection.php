<?php

namespace App\Models;

use App\Support\AcademicBatchOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicSection extends Model
{
    protected $fillable = [
        'batch_id',
        'program_id',
        'course_id',
        'level_id',
        'name',
        'code',
        'subtitle',
        'max_capacity',
        'students_count',
        'supervisor',
        'period',
        'semester',
        'semester_key',
        'status',
        'added_by',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicBatch::class, 'batch_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(AcademicCourse::class, 'course_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(AcademicStudent::class, 'section_id');
    }

    public function refreshStudentsCount(): void
    {
        $this->update(['students_count' => $this->students()->count()]);
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(AcademicSchedule::class, 'section_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'section_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'section_id');
    }

    public function displaySemester(): string
    {
        return AcademicBatchOptions::semesterLabel($this->semester_key, $this->semester);
    }

    public function batchFullLabel(): string
    {
        $parts = array_filter([
            $this->batch?->name,
            $this->program?->name_ar,
        ]);

        return $parts ? implode(' — ', $parts) : '—';
    }

    public function fillPercent(): ?int
    {
        if (! $this->max_capacity || $this->max_capacity <= 0) {
            return null;
        }

        return min(100, (int) round(($this->students_count / $this->max_capacity) * 100));
    }

    public function availableSeats(): ?int
    {
        return $this->max_capacity
            ? max(0, $this->max_capacity - $this->students_count)
            : null;
    }

    public function trainerName(): string
    {
        return $this->schedule?->displayTrainer() ?? '—';
    }

    /** @return HasMany<AcademicStudent, $this> */
    public function batchStudents(): HasMany
    {
        return $this->hasMany(AcademicStudent::class, 'batch_id', 'batch_id');
    }
}
