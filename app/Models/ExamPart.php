<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPart extends Model
{
    protected $fillable = [
        'exam_id',
        'title',
        'instructions',
        'sort_order',
        'shuffle_questions',
        'questions_to_draw',
        'pool_filters',
    ];

    protected function casts(): array
    {
        return [
            'shuffle_questions' => 'boolean',
            'pool_filters' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(
            ExamQuestion::class,
            'exam_part_questions',
            'exam_part_id',
            'question_id'
        )
            ->withPivot(['id', 'points', 'sort_order', 'is_required', 'settings'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function questionLinks(): HasMany
    {
        return $this->hasMany(ExamPartQuestion::class);
    }
}
