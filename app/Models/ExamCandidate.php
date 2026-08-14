<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamCandidate extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'user_id',
        'section_id',
        'batch_id',
        'academic_id',
        'student_name',
        'status',
        'assigned_at',
        'excluded_at',
        'exclusion_reason',
        'snapshot',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'excluded_at' => 'datetime',
            'snapshot' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
