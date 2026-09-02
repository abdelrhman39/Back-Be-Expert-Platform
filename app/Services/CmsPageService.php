<?php

namespace App\Services;

use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use App\Models\User;
use App\Support\CmsBlockDefaults;
use App\Support\CmsOptions;
use App\Support\PublicCopy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsPageService
{
    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    public function findPublishedBySlug(string $slug, ?string $locale = null): ?CmsPage
    {
        $locale ??= app()->getLocale();

        $translation = CmsPageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->first();

        if (! $translation) {
            return null;
        }

        $page = CmsPage::query()
            ->with('translations')
            ->whereKey($translation->page_id)
            ->where('status', 'published')
            ->first();

        return $page;
    }

    public function findPublishedByType(string $type, ?string $locale = null): ?CmsPage
    {
        $locale ??= app()->getLocale();

        return CmsPage::query()
            ->with('translations')
            ->where('type', $type)
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /** @return Collection<int, CmsPage> */
    public function footerPages(?string $locale = null): Collection
    {
        return CmsPage::query()
            ->with('translations')
            ->where('show_in_footer', true)
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Published policy / CMS pages flagged for the footer, titled for the current locale.
     *
     * @return Collection<int, array{label: string, url: string, open_in_new_tab: bool}>
     */
    public function footerPolicyLinks(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        $payload = Cache::remember("cms_footer_policies.{$locale}", 300, function () use ($locale) {
            return $this->footerPages($locale)
                ->map(function (CmsPage $page) use ($locale): ?array {
                    $label = PublicCopy::pageTitle($page, $locale);
                    $url = $this->urlForPage($page, $locale);

                    if (! filled($label) || ! filled($url)) {
                        return null;
                    }

                    return [
                        'label' => $label,
                        'url' => $url,
                        'open_in_new_tab' => false,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });

        return collect($payload);
    }

    public function forgetPublicCaches(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("cms_footer_policies.{$locale}");
        }

        app(CmsMenuService::class)->forgetCache();
    }

    /** @return array{total: int, published: int, draft: int, policies: int, footer: int} */
    public function stats(): array
    {
        $base = CmsPage::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'policies' => (clone $base)->where('type', 'policy')->count(),
            'footer' => (clone $base)->where('show_in_footer', true)->count(),
        ];
    }

    public function duplicate(CmsPage $page, ?User $actor = null): CmsPage
    {
        $actor ??= auth()->user();

        return DB::transaction(function () use ($page, $actor) {
            $page->load('translations');

            $copy = CmsPage::query()->create([
                'type' => $page->type === 'home' ? 'custom' : $page->type,
                'layout' => $page->layout ?? 'default',
                'status' => 'draft',
                'sort_order' => $page->sort_order,
                'show_in_footer' => false,
                'show_title' => $page->show_title,
                'noindex' => $page->noindex,
                'legacy_slug' => null,
                'internal_notes' => $page->internal_notes,
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]);

            foreach ($page->translations as $translation) {
                CmsPageTranslation::query()->create([
                    'page_id' => $copy->id,
                    'locale' => $translation->locale,
                    'title' => $translation->title.' (نسخة)',
                    'slug' => $this->uniqueSlug($translation->slug.'-copy', $translation->locale, $copy->id),
                    'excerpt' => $translation->excerpt,
                    'meta_title' => $translation->meta_title,
                    'meta_description' => $translation->meta_description,
                    'og_image' => $translation->og_image,
                    'body' => $translation->body,
                    'blocks' => $translation->blocks,
                ]);
            }

            $this->audit->log(
                action: 'cms_page.duplicated',
                descriptionAr: 'نسخ صفحة CMS #'.$page->id.' → #'.$copy->id,
                group: 'content',
                actor: $actor,
                subject: $copy,
                subjectLabel: $copy->translate()?->title,
            );

            return $copy->load('translations');
        });
    }

    public function setStatus(CmsPage $page, string $status, ?User $actor = null): CmsPage
    {
        abort_unless(in_array($status, array_keys(CmsOptions::pageStatuses()), true), 422);

        return $this->save([
            'type' => $page->type,
            'layout' => $page->layout ?? 'default',
            'status' => $status,
            'sort_order' => $page->sort_order,
            'show_in_footer' => $page->show_in_footer,
            'show_title' => $page->show_title,
            'noindex' => $page->noindex,
            'legacy_slug' => $page->legacy_slug,
            'internal_notes' => $page->internal_notes,
            'translations' => $page->translations->mapWithKeys(fn (CmsPageTranslation $t) => [
                $t->locale => $t->only(['title', 'slug', 'excerpt', 'meta_title', 'meta_description', 'og_image', 'body', 'blocks']),
            ])->all(),
        ], $page, $actor);
    }

    public function adminPreviewUrl(CmsPage $page, ?string $locale = null): ?string
    {
        $locale ??= 'ar';

        return route('admin.cms-pages.preview', ['page' => $page->id, 'locale' => $locale]);
    }

    public function publicUrl(CmsPage $page, ?string $locale = null): ?string
    {
        $locale ??= 'ar';
        $translation = $page->translations->firstWhere('locale', $locale)
            ?? $page->translations->firstWhere('locale', 'ar');

        if (! $translation) {
            return null;
        }

        if (in_array($page->type, ['home', 'about', 'contact'], true) && $page->isPublished()) {
            return match ($page->type) {
                'home' => route('home', ['locale' => $locale]),
                'about' => route('about', ['locale' => $locale]),
                'contact' => route('contact', ['locale' => $locale]),
                default => null,
            };
        }

        if (! $page->isPublished()) {
            return null;
        }

        return route('cms.page', ['locale' => $locale, 'slug' => $translation->slug]);
    }

    /** @param  array<string, mixed>  $data */
    public function save(array $data, ?CmsPage $page = null, ?User $actor = null): CmsPage
    {
        $actor ??= auth()->user();

        $page = DB::transaction(function () use ($data, $page, $actor) {
            $isNew = ! $page;

            if (! $page) {
                $page = CmsPage::query()->create([
                    'type' => $data['type'] ?? 'custom',
                    'layout' => $data['layout'] ?? 'default',
                    'content_mode' => $data['content_mode']
                        ?? CmsBlockDefaults::defaultContentMode($data['type'] ?? 'custom'),
                    'status' => $data['status'] ?? 'draft',
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'show_in_footer' => (bool) ($data['show_in_footer'] ?? false),
                    'show_title' => (bool) ($data['show_title'] ?? true),
                    'noindex' => (bool) ($data['noindex'] ?? false),
                    'legacy_slug' => $data['legacy_slug'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'created_by' => $actor?->id,
                    'updated_by' => $actor?->id,
                    'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
                ]);
            } else {
                $newStatus = $data['status'] ?? $page->status;

                $page->update([
                    'type' => $data['type'] ?? $page->type,
                    'layout' => $data['layout'] ?? $page->layout ?? 'default',
                    'content_mode' => $data['content_mode']
                        ?? $page->content_mode
                        ?? CmsBlockDefaults::defaultContentMode($data['type'] ?? $page->type),
                    'status' => $newStatus,
                    'sort_order' => (int) ($data['sort_order'] ?? $page->sort_order),
                    'show_in_footer' => (bool) ($data['show_in_footer'] ?? $page->show_in_footer),
                    'show_title' => (bool) ($data['show_title'] ?? $page->show_title ?? true),
                    'noindex' => (bool) ($data['noindex'] ?? $page->noindex ?? false),
                    'legacy_slug' => $data['legacy_slug'] ?? $page->legacy_slug,
                    'internal_notes' => $data['internal_notes'] ?? $page->internal_notes,
                    'updated_by' => $actor?->id,
                    'published_at' => $newStatus === 'published'
                        ? ($page->published_at ?? now())
                        : ($newStatus === 'draft' ? null : $page->published_at),
                ]);
            }

            foreach (['ar', 'en'] as $locale) {
                $t = $data['translations'][$locale] ?? null;

                if (! $t || blank($t['title'] ?? null)) {
                    continue;
                }

                $slug = $this->uniqueSlug($t['slug'] ?? $t['title'], $locale, $page->id);

                CmsPageTranslation::query()->updateOrCreate(
                    ['page_id' => $page->id, 'locale' => $locale],
                    [
                        'title' => $t['title'],
                        'slug' => $slug,
                        'excerpt' => $t['excerpt'] ?? null,
                        'meta_title' => $t['meta_title'] ?? null,
                        'meta_description' => $t['meta_description'] ?? null,
                        'og_image' => $t['og_image'] ?? null,
                        'body' => $t['body'] ?? null,
                        'blocks' => $t['blocks'] ?? null,
                    ],
                );
            }

            $this->audit->log(
                action: $isNew ? 'cms_page.created' : 'cms_page.updated',
                descriptionAr: ($isNew ? 'إنشاء' : 'تحديث').' صفحة CMS #'.$page->id,
                group: 'content',
                actor: $actor,
                subject: $page,
                subjectLabel: $page->fresh()->translate()?->title,
            );

            return $page->fresh(['translations']);
        });

        $this->forgetPublicCaches();

        return $page;
    }

    public function delete(CmsPage $page, ?User $actor = null): void
    {
        $title = $page->translate()?->title;
        $page->delete();

        $this->forgetPublicCaches();

        $this->audit->log(
            action: 'cms_page.deleted',
            descriptionAr: 'حذف صفحة CMS: '.$title,
            group: 'content',
            actor: $actor ?? auth()->user(),
            subjectLabel: $title,
        );
    }

    public function uniqueSlug(string $raw, string $locale, ?int $ignorePageId = null): string
    {
        $base = Str::slug($raw);

        if ($base === '') {
            $base = 'page-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $i = 1;

        while (
            CmsPageTranslation::query()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->when($ignorePageId, fn ($q) => $q->where('page_id', '!=', $ignorePageId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function urlForPage(CmsPage $page, ?string $locale = null): ?string
    {
        $translation = $page->translate($locale);

        if (! $translation || ! $page->isPublished()) {
            return null;
        }

        return route('cms.page', ['locale' => $locale ?? app()->getLocale(), 'slug' => $translation->slug]);
    }
}
