<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTranslation;
use App\Models\User;
use App\Support\ArticleOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleService
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    /** @return Collection<int, Article> */
    public function latestPublished(int $limit = 3, ?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        return Article::query()
            ->with(['translations', 'articleCategory'])
            ->where('status', 'published')
            ->whereHas('translations')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get()
            ->filter(fn (Article $article) => $article->translate($locale) !== null)
            ->values();
    }

    /**
     * @return LengthAwarePaginator<int, Article>
     */
    public function paginatePublished(int $perPage = 12, ?int $categoryId = null, ?string $locale = null): LengthAwarePaginator
    {
        $locale ??= app()->getLocale();
        $perPage = max(1, min(48, $perPage));

        return Article::query()
            ->with(['translations', 'articleCategory'])
            ->where('status', 'published')
            ->whereHas('translations')
            ->when($categoryId, fn ($q) => $q->where('article_category_id', $categoryId))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug, ?string $locale = null): ?Article
    {
        $locale ??= app()->getLocale();

        // Prefer the current locale, then fall back to any matching slug
        // (e.g. /en/news/{ar-slug} when no English translation exists yet).
        $translation = ArticleTranslation::query()
            ->where('slug', $slug)
            ->where('locale', $locale)
            ->first()
            ?? ArticleTranslation::query()
                ->where('slug', $slug)
                ->orderByRaw("CASE WHEN locale = 'ar' THEN 0 ELSE 1 END")
                ->first();

        if (! $translation) {
            return null;
        }

        return Article::query()
            ->with(['translations', 'articleCategory'])
            ->whereKey($translation->article_id)
            ->where('status', 'published')
            ->first();
    }

    /** @return array{total: int, published: int, draft: int, featured: int} */
    public function stats(): array
    {
        $base = Article::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'featured' => (clone $base)->where('is_featured', true)->count(),
        ];
    }

    public function setStatus(Article $article, string $status, ?User $actor = null): Article
    {
        abort_unless(array_key_exists($status, ArticleOptions::statuses()), 422);

        return $this->save($this->payloadFromModel($article, ['status' => $status]), $article, $actor);
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?Article $article = null, ?User $actor = null): Article
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($data, $article, $actor) {
            $status = $data['status'] ?? 'draft';
            $isNew = ! $article;
            $legacyCategory = $this->resolveLegacyCategory($data);

            $publishedAt = $data['published_at'] ?? null;
            if ($status === 'published' && ! $publishedAt) {
                $publishedAt = $article?->published_at ?? now();
            }
            if ($status === 'draft') {
                $publishedAt = null;
            }

            if (! $article) {
                $article = Article::query()->create([
                    'status' => $status,
                    'category' => $legacyCategory,
                    'article_category_id' => $data['article_category_id'] ?? null,
                    'featured_image' => $data['featured_image'] ?? null,
                    'video_url' => $data['video_url'] ?? null,
                    'is_featured' => (bool) ($data['is_featured'] ?? false),
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'legacy_slug' => $data['legacy_slug'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'created_by' => $actor?->id,
                    'updated_by' => $actor?->id,
                    'published_at' => $publishedAt,
                ]);
            } else {
                $article->update([
                    'status' => $status,
                    'category' => $legacyCategory,
                    'article_category_id' => $data['article_category_id'] ?? $article->article_category_id,
                    'featured_image' => array_key_exists('featured_image', $data)
                        ? $data['featured_image']
                        : $article->featured_image,
                    'video_url' => $data['video_url'] ?? $article->video_url,
                    'is_featured' => (bool) ($data['is_featured'] ?? $article->is_featured),
                    'sort_order' => (int) ($data['sort_order'] ?? $article->sort_order),
                    'legacy_slug' => $data['legacy_slug'] ?? $article->legacy_slug,
                    'internal_notes' => $data['internal_notes'] ?? $article->internal_notes,
                    'updated_by' => $actor?->id,
                    'published_at' => $publishedAt,
                ]);
            }

            foreach (['ar', 'en'] as $locale) {
                $t = $data['translations'][$locale] ?? null;

                if (! $t || blank($t['title'] ?? null)) {
                    continue;
                }

                $slug = $this->uniqueSlug($t['slug'] ?? $t['title'], $locale, $article->id);

                ArticleTranslation::query()->updateOrCreate(
                    ['article_id' => $article->id, 'locale' => $locale],
                    [
                        'title' => $t['title'],
                        'slug' => $slug,
                        'excerpt' => $t['excerpt'] ?? null,
                        'meta_title' => $t['meta_title'] ?? null,
                        'meta_description' => $t['meta_description'] ?? null,
                        'og_image' => $t['og_image'] ?? null,
                        'body' => $t['body'] ?? null,
                    ],
                );
            }

            $article->load(['translations', 'articleCategory']);

            $this->audit->log(
                action: $isNew ? 'article.created' : 'article.updated',
                descriptionAr: ($isNew ? 'إنشاء مقال: ' : 'تحديث مقال: ').($article->translate()?->title ?? '#'.$article->id),
                group: 'content',
                actor: $actor,
                subject: $article,
                subjectLabel: $article->translate()?->title,
            );

            return $article;
        });
    }

    public function delete(Article $article, ?User $actor = null): void
    {
        $actor ??= auth()->user();
        $title = $article->translate()?->title;

        $article->delete();

        $this->audit->log(
            action: 'article.deleted',
            descriptionAr: 'حذف مقال: '.($title ?? '#'.$article->id),
            group: 'content',
            actor: $actor,
        );
    }

    public function uniqueSlug(string $raw, string $locale, ?int $exceptArticleId = null): string
    {
        $base = Str::slug($raw);

        if ($base === '') {
            $base = 'article-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $i = 2;

        while ($this->slugTaken($slug, $locale, $exceptArticleId)) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function slugTaken(string $slug, string $locale, ?int $exceptArticleId): bool
    {
        return ArticleTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($exceptArticleId, fn ($q) => $q->where('article_id', '!=', $exceptArticleId))
            ->exists();
    }

    /** @return array<string, mixed> */
    private function payloadFromModel(Article $article, array $overrides = []): array
    {
        $payload = [
            'status' => $article->status,
            'category' => $article->category,
            'article_category_id' => $article->article_category_id,
            'featured_image' => $article->featured_image,
            'video_url' => $article->video_url,
            'is_featured' => $article->is_featured,
            'sort_order' => $article->sort_order,
            'legacy_slug' => $article->legacy_slug,
            'internal_notes' => $article->internal_notes,
            'published_at' => $article->published_at,
            'translations' => [],
        ];

        foreach ($article->translations as $translation) {
            $payload['translations'][$translation->locale] = $translation->only([
                'title', 'slug', 'excerpt', 'meta_title', 'meta_description', 'og_image', 'body',
            ]);
        }

        return array_merge($payload, $overrides);
    }

    /** @param  array<string, mixed>  $data */
    private function resolveLegacyCategory(array $data): string
    {
        if (! empty($data['article_category_id'])) {
            $category = ArticleCategory::query()->find($data['article_category_id']);

            if ($category) {
                return match ($category->slug) {
                    'events', 'event' => 'event',
                    default => 'news',
                };
            }
        }

        return $data['category'] ?? 'news';
    }

    public function storeFeaturedImage(mixed $file): string
    {
        if (! $file) {
            throw ValidationException::withMessages(['featuredImageUpload' => ['لم يتم اختيار صورة.']]);
        }

        $path = $file->store('articles', 'public');

        return '/storage/'.$path;
    }
}
