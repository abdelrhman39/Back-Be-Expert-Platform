<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'article_category_id');
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en' && filled($this->name_en)) {
            return $this->name_en;
        }

        return $this->name_ar;
    }
}
