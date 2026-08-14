<?php

namespace App\Support;

use App\Models\Article;
use App\Models\ArticleCategory;

class ArticleOptions
{
    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'draft' => 'مسودة',
            'published' => 'منشور',
            'archived' => 'مؤرشف',
        ];
    }

    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            'news' => 'خبر',
            'event' => 'فعالية',
        ];
    }

    /** @return array<int, string> */
    public static function categoryOptions(): array
    {
        return ArticleCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (ArticleCategory $category) => [$category->id => $category->name_ar])
            ->all();
    }

    public static function statusLabel(string $status): string
    {
        return self::statuses()[$status] ?? $status;
    }

    public static function categoryLabel(string $category): string
    {
        return self::categories()[$category] ?? $category;
    }

    public static function articleCategoryLabel(Article $article, ?string $locale = null): string
    {
        if ($article->articleCategory) {
            return $article->articleCategory->displayName($locale);
        }

        return self::categoryLabel($article->category);
    }

    public static function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'published' => 'article-badge article-badge--published',
            'archived' => 'article-badge article-badge--archived',
            default => 'article-badge article-badge--draft',
        };
    }
}
