<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CatalogCategory extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title_ar',
        'title_en',
        'slug',
        'sort_order',
        'sidebar_visible',
    ];

    protected function casts(): array
    {
        return [
            'sidebar_visible' => 'boolean',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(CatalogCourse::class, 'catalog_category_course', 'category_id', 'course_id');
    }

    public function displayTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->title_en)
            ? $this->title_en
            : $this->title_ar;
    }
}
