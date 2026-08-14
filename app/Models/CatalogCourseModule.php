<?php

namespace App\Models;

use App\Support\CourseModuleOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogCourseModule extends Model
{
    protected $fillable = [
        'course_id',
        'title_ar',
        'title_en',
        'code',
        'summary_ar',
        'summary_en',
        'description_ar',
        'description_en',
        'objectives_ar',
        'objectives_en',
        'status',
        'is_optional',
        'estimated_duration_minutes',
        'prerequisite_module_ids',
        'drip_days',
        'unlock_at',
        'completion_rule',
        'icon',
        'image_path',
        'image_name',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
        'notes_internal',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_optional' => 'boolean',
            'estimated_duration_minutes' => 'integer',
            'prerequisite_module_ids' => 'array',
            'drip_days' => 'integer',
            'unlock_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CatalogCourse::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CatalogCourseLesson::class, 'module_id')->orderBy('sort_order');
    }

    public function displayTitle(): string
    {
        return $this->title_ar ?: $this->title_en ?: 'وحدة #'.$this->id;
    }

    public function displaySummary(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->summary_en)
            ? $this->summary_en
            : $this->summary_ar;
    }

    public function displayDescription(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->description_en)
            ? $this->description_en
            : $this->description_ar;
    }

    public function displayObjectives(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->objectives_en)
            ? $this->objectives_en
            : $this->objectives_ar;
    }

    public function statusLabel(): string
    {
        return CourseModuleOptions::statusLabel($this->status ?? 'published');
    }

    public function statusBadgeClass(): string
    {
        return CourseModuleOptions::statusBadgeClass($this->status ?? 'published');
    }

    public function completionRuleLabel(): string
    {
        return CourseModuleOptions::completionRuleLabel($this->completion_rule ?? 'all_lessons');
    }

    public function isPublished(): bool
    {
        return ($this->status ?? 'published') === 'published';
    }

    /** @return array<int> */
    public function prerequisiteIds(): array
    {
        return array_values(array_filter(
            array_map('intval', $this->prerequisite_module_ids ?? []),
            fn (int $id) => $id > 0,
        ));
    }
}
