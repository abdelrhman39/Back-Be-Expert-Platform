<?php

namespace App\Models;

use App\Support\CmsBlockDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CmsPage extends Model
{
    protected $fillable = [
        'type',
        'layout',
        'content_mode',
        'status',
        'sort_order',
        'show_in_footer',
        'show_title',
        'noindex',
        'legacy_slug',
        'internal_notes',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'show_in_footer' => 'boolean',
            'show_title' => 'boolean',
            'noindex' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function usesBlocksContent(): bool
    {
        $mode = $this->content_mode ?: CmsBlockDefaults::defaultContentMode($this->type);

        return $mode === 'blocks';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CmsPageTranslation::class, 'page_id');
    }

    public function translation(?string $locale = null): HasOne
    {
        $locale ??= app()->getLocale();

        return $this->hasOne(CmsPageTranslation::class, 'page_id')->where('locale', $locale);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function translate(?string $locale = null): ?CmsPageTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'ar');
    }
}
