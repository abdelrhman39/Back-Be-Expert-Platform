<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_key',
        'content',
        'content_en',
        'is_correct',
        'weight',
        'feedback',
        'feedback_en',
        'match_data',
        'match_data_en',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'weight' => 'decimal:4',
            'match_data' => 'array',
            'match_data_en' => 'array',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}
