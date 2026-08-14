<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CatalogField extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title_ar',
        'title_en',
        'slug',
        'icon',
        'sort_order',
        'sidebar_visible',
        'home_visible',
    ];

    protected function casts(): array
    {
        return [
            'sidebar_visible' => 'boolean',
            'home_visible' => 'boolean',
        ];
    }

    public function iconUrl(): string
    {
        if ($this->icon) {
            return cms_media_url(ltrim($this->icon, './'));
        }

        $fallback = self::defaultIconMap()[$this->id] ?? 'assets/category-icon.svg';

        return cms_media_url(ltrim($fallback, './'));
    }

    public function coursesIndexUrl(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route('courses.index', [
            'locale' => $locale,
            'fields' => [$this->id],
        ]);
    }

    /** @return array<int, string> */
    public static function defaultIconMap(): array
    {
        return [
            8 => 'assets/1856665158746841.png',
            10 => 'assets/1856665508933119.png',
            11 => 'assets/1856730091621963.png',
            13 => 'assets/1856665158746841.png',
            14 => 'assets/1857695311898902.png',
            16 => 'assets/1857695252938610.png',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(CatalogCourse::class, 'catalog_field_course', 'field_id', 'course_id');
    }

    public function displayTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->title_en)
            ? $this->title_en
            : $this->title_ar;
    }
}
