<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'answer',
        'answer_text',
        'file_path',
        'file_original_name',
        'status',
        'is_correct',
        'auto_score',
        'manual_score',
        'grader_feedback',
        'graded_by',
        'graded_at',
        'answered_at',
        'is_flagged',
        'time_spent_seconds',
        'question_snapshot',
        'grading_key',
    ];

    protected $hidden = ['grading_key'];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'is_correct' => 'boolean',
            'auto_score' => 'decimal:2',
            'manual_score' => 'decimal:2',
            'graded_at' => 'datetime',
            'answered_at' => 'datetime',
            'is_flagged' => 'boolean',
            'question_snapshot' => 'array',
            'grading_key' => 'encrypted:array',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function effectiveScore(): float
    {
        return (float) ($this->manual_score ?? $this->auto_score);
    }
}
