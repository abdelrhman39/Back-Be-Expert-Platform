<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPartQuestion extends Model
{
    protected $fillable = [
        'exam_part_id',
        'question_id',
        'points',
        'sort_order',
        'is_required',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'is_required' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(ExamPart::class, 'exam_part_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
