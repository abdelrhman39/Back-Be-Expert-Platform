<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPublication extends Model
{
    protected $fillable = [
        'exam_id',
        'version',
        'total_points',
        'question_count',
        'question_blueprint',
        'settings_snapshot',
        'checksum',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'total_points' => 'decimal:2',
            'question_blueprint' => 'encrypted:array',
            'settings_snapshot' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'publication_id');
    }
}
