<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AcademicCourse extends Model
{
    protected $fillable = [
        'program_id',
        'level_id',
        'name_ar',
        'name_en',
        'symbol_ar',
        'symbol_en',
        'code',
        'credit_hours',
        'status',
        'target_group',
        'summary',
        'image_url',
        'added_by',
    ];

    /** @return Attribute<string|null, never> */
    protected function resolvedImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image_url) {
                return null;
            }

            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }

            if (str_starts_with($this->image_url, 'new-platform/')) {
                return asset($this->image_url);
            }

            return Storage::disk('public')->url($this->image_url);
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'level_id');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'course_id');
    }

    public function displayLevel(): string
    {
        return $this->level?->name_ar ?? '—';
    }
}
