<?php

namespace App\Services;

use App\Models\ArticleCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleCategoryService
{
    /** @return Collection<int, ArticleCategory> */
    public function active(): Collection
    {
        return ArticleCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, ArticleCategory> */
    public function all(): Collection
    {
        return ArticleCategory::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?ArticleCategory $category = null): ArticleCategory
    {
        $slug = Str::slug($data['slug'] ?? $data['name_ar'] ?? '');

        if ($slug === '') {
            throw ValidationException::withMessages(['slug' => ['تعذّر إنشاء رابط للتصنيف.']]);
        }

        $payload = [
            'slug' => $this->uniqueSlug($slug, $category?->id),
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'color' => $data['color'] ?? '#1b8354',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if ($category) {
            $category->update($payload);

            return $category->fresh();
        }

        return ArticleCategory::query()->create($payload);
    }

    public function delete(ArticleCategory $category): void
    {
        if ($category->articles()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['لا يمكن حذف تصنيف مرتبط بمقالات. عطّله أو انقل المقالات أولاً.'],
            ]);
        }

        $category->delete();
    }

    private function uniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = $base;
        $i = 2;

        while (ArticleCategory::query()
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
