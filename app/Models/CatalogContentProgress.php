<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogContentProgress extends Model
{
    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CatalogEnrollment::class, 'enrollment_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CatalogCourseLesson::class, 'lesson_id');
    }
}
