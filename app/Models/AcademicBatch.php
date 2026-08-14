<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicBatch extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'code',
        'semester',
        'semester_key',
        'start_date',
        'end_date',
        'students_count',
        'capacity',
        'tuition_amount',
        'installment_allowed',
        'study_mode',
        'coordinator',
        'enrollment_open',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'enrollment_open' => 'boolean',
            'tuition_amount' => 'decimal:2',
            'installment_allowed' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(AcademicStudent::class, 'batch_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(AcademicSection::class, 'batch_id');
    }

    public function refreshStudentsCount(): void
    {
        $this->update(['students_count' => $this->students()->count()]);
    }

    public function enrolledCount(): int
    {
        return $this->students_count;
    }

    public function availableSeats(): ?int
    {
        return $this->capacity !== null
            ? max(0, $this->capacity - $this->students_count)
            : null;
    }

    public function displaySemester(): string
    {
        return \App\Support\AcademicBatchOptions::semesterLabel($this->semester_key, $this->semester);
    }
}
