<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAccommodation extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'extra_time_minutes',
        'extra_attempts',
        'unlimited_attempts',
        'override_exam_availability',
        'opens_at',
        'closes_at',
        'ignore_access_code',
        'notes',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'ignore_access_code' => 'boolean',
            'unlimited_attempts' => 'boolean',
            'override_exam_availability' => 'boolean',
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
}
