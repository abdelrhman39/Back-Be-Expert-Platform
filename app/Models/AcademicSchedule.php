<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'batch_id',
        'level_id',
        'semester_key',
        'period',
        'staff_id',
        'trainer_name',
        'day_of_week',
        'time_start',
        'time_end',
        'meeting_url',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicBatch::class, 'batch_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(AcademicStaff::class, 'staff_id');
    }

    public function displayTrainer(): string
    {
        return $this->trainer_name ?: $this->staff?->name_ar ?: '—';
    }
}
