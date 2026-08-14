<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicProgram extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'name_on_certificate',
        'code',
        'symbol',
        'duration_months',
        'duration_label',
        'start_date',
        'status',
        'type',
        'coordinator',
        'email',
        'phone',
        'city',
        'summary',
        'skills',
        'study_status',
        'poster_image',
        'media_url',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'skills' => 'array',
            'attachments' => 'array',
        ];
    }

    public function displayDuration(): string
    {
        if ($this->duration_label) {
            return $this->duration_label;
        }

        return $this->duration_months ? $this->duration_months.' شهر' : '—';
    }

    public function posterUrl(): string
    {
        return \App\Support\PosterSettings::resolve($this->poster_image);
    }

    public function hasCustomPoster(): bool
    {
        return filled($this->poster_image);
    }

    public function displayName(): string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->name_en)
            ? $this->name_en
            : $this->name_ar;
    }

    public function batches(): HasMany
    {
        return $this->hasMany(AcademicBatch::class, 'program_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(AcademicLevel::class, 'program_id')->orderBy('sort_order');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(AcademicCourse::class, 'program_id');
    }

    public function scheduleDocuments(): HasMany
    {
        return $this->hasMany(AcademicScheduleDocument::class, 'program_id');
    }
}
