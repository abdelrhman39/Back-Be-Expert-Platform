<?php

namespace App\Models;

use App\Support\CourseContentOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogCourseLesson extends Model
{
    protected $fillable = [
        'module_id',
        'title_ar',
        'title_en',
        'code',
        'summary_ar',
        'summary_en',
        'type',
        'status',
        'is_preview',
        'completion_required',
        'body_ar',
        'body_en',
        'external_url',
        'video_provider',
        'resource_url',
        'file_path',
        'file_name',
        'duration_minutes',
        'notes_internal',
        'meta_title_ar',
        'meta_title_en',
        'meta_description_ar',
        'meta_description_en',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
            'completion_required' => 'boolean',
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CatalogCourseModule::class, 'module_id');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(CatalogContentProgress::class, 'lesson_id');
    }

    public function displayTitle(): string
    {
        return $this->title_ar ?: $this->title_en ?: 'درس #'.$this->id;
    }

    public function displaySummary(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->summary_en)
            ? $this->summary_en
            : $this->summary_ar;
    }

    public function displayBody(): ?string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->body_en)
            ? $this->body_en
            : $this->body_ar;
    }

    public function isPublished(): bool
    {
        return ($this->status ?? 'published') === 'published';
    }

    public function statusLabel(): string
    {
        return CourseContentOptions::lessonStatusLabel($this->status ?? 'published');
    }

    public function statusBadgeClass(): string
    {
        return CourseContentOptions::lessonStatusBadgeClass($this->status ?? 'published');
    }

    public function videoEmbedUrl(): ?string
    {
        return CourseContentOptions::normalizeVideoEmbedUrl($this->external_url, $this->video_provider);
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'video' => 'fa-circle-play',
            'document' => 'fa-file-lines',
            default => 'fa-book-open',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'video' => 'فيديو',
            'document' => 'قراءة',
            default => 'محتوى',
        };
    }

    public function typeBadgeClass(): string
    {
        return match ($this->type) {
            'video' => 'portal-player-type--video',
            'document' => 'portal-player-type--document',
            default => 'portal-player-type--html',
        };
    }
}
