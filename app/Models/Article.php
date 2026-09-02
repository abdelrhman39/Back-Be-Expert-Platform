<?php

namespace App\Models;

use App\Support\ArticleOptions;
use App\Support\PosterSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Article extends Model
{
    protected $fillable = [
        'status',
        'category',
        'article_category_id',
        'featured_image',
        'video_url',
        'is_featured',
        'sort_order',
        'legacy_slug',
        'internal_notes',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function translation(?string $locale = null): HasOne
    {
        $locale ??= app()->getLocale();

        return $this->hasOne(ArticleTranslation::class)->where('locale', $locale);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function articleCategory(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function categoryDisplayName(?string $locale = null): string
    {
        if ($this->articleCategory) {
            return $this->articleCategory->displayName($locale);
        }

        return ArticleOptions::categoryLabel($this->category, $locale);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function translate(?string $locale = null): ?ArticleTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'ar');
    }

    public function featuredImageUrl(): string
    {
        return PosterSettings::resolve($this->featured_image);
    }

    public function videoEmbedUrl(): ?string
    {
        $url = trim($this->video_url ?? '');

        if ($url === '') {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    public function publicUrl(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $translation = $this->translate($locale) ?? $this->translate('ar');

        if (! $translation || ! $this->isPublished()) {
            return null;
        }

        return route('articles.show', ['locale' => $locale, 'slug' => $translation->slug]);
    }
}
