<?php

namespace App\Services;

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\User;
use App\Support\CmsOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CmsMenuService
{
    public function __construct(
        private readonly CmsPageService $pages,
        private readonly AuditLogService $audit,
    ) {}

    /** @return Collection<int, array<string, mixed>> */
    public function tree(string $menuKey, ?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        $payload = Cache::remember("cms_menu.{$menuKey}.{$locale}", 300, function () use ($menuKey, $locale) {
            $menu = CmsMenu::query()
                ->where('key', $menuKey)
                ->where('is_active', true)
                ->first();

            if (! $menu) {
                return [];
            }

            if ($menu->locale_scope !== 'all' && $menu->locale_scope !== $locale) {
                return [];
            }

            $items = $menu->items()
                ->with(['children' => fn ($q) => $q->with('page.translations')->orderBy('sort_order'), 'page.translations'])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return $items->map(fn (CmsMenuItem $item) => $this->mapItem($item, $locale, false))->filter()->values()->all();
        });

        return collect($payload)
            ->map(fn (array $item) => $this->filterVisibleItem($item))
            ->filter()
            ->values();
    }

    /** @return array<string, mixed>|null */
    protected function mapItem(CmsMenuItem $item, string $locale, bool $filterPermission = true): ?array
    {
        if (
            $filterPermission
            && $item->permission
            && (! auth()->check() || ! auth()->user()?->canAdmin($item->permission))
        ) {
            return null;
        }

        $children = $item->children
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->map(fn (CmsMenuItem $child) => $this->mapItem($child, $locale, $filterPermission))
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $item->id,
            'label' => $item->label($locale),
            'url' => $this->resolveUrl($item, $locale),
            'open_in_new_tab' => $item->open_in_new_tab,
            'permission' => $item->permission,
            'has_children' => $children !== [],
            'children' => $children,
        ];
    }

    /** @param array<string, mixed> $item
     * @return array<string, mixed>|null
     */
    protected function filterVisibleItem(array $item): ?array
    {
        $permission = $item['permission'] ?? null;

        if ($permission && (! auth()->check() || ! auth()->user()?->canAdmin($permission))) {
            return null;
        }

        $children = collect($item['children'] ?? [])
            ->map(fn (array $child) => $this->filterVisibleItem($child))
            ->filter()
            ->values()
            ->all();

        $item['children'] = $children;
        $item['has_children'] = $children !== [];
        unset($item['permission']);

        return $item;
    }

    public function resolveUrl(CmsMenuItem $item, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($item->link_type) {
            'route' => $item->route_name ? $this->safeRoute($item->route_name, $locale) : null,
            'page' => $item->page ? $this->pages->urlForPage($item->page, $locale) : null,
            'url' => $item->url,
            default => null,
        };
    }

    protected function safeRoute(string $name, string $locale): ?string
    {
        if (! \Route::has($name)) {
            return null;
        }

        try {
            return route($name, ['locale' => $locale]);
        } catch (\Throwable) {
            return null;
        }
    }

    public function forgetCache(?string $menuKey = null): void
    {
        if ($menuKey) {
            foreach (['ar', 'en'] as $locale) {
                Cache::forget("cms_menu.{$menuKey}.{$locale}");
            }

            return;
        }

        foreach (array_keys(CmsOptions::menuKeys()) as $key) {
            $this->forgetCache($key);
        }
    }

    /** @param  array<string, mixed>  $data */
    public function saveItem(array $data, ?CmsMenuItem $item = null, ?User $actor = null): CmsMenuItem
    {
        $menu = CmsMenu::query()->findOrFail($data['menu_id']);

        $payload = [
            'menu_id' => $menu->id,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'label_ar' => $data['label_ar'],
            'label_en' => $data['label_en'] ?? null,
            'link_type' => $data['link_type'] ?? 'none',
            'route_name' => $data['route_name'] ?? null,
            'page_id' => $data['page_id'] ?? null,
            'url' => $data['url'] ?? null,
            'open_in_new_tab' => (bool) ($data['open_in_new_tab'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'permission' => $data['permission'] ?? null,
        ];

        if ($item) {
            $item->update($payload);
        } else {
            $item = CmsMenuItem::query()->create($payload);
        }

        $this->forgetCache($menu->key);

        $this->audit->log(
            action: 'cms_menu_item.saved',
            descriptionAr: 'حفظ عنصر قائمة: '.$item->label_ar,
            group: 'content',
            actor: $actor ?? auth()->user(),
            subject: $item,
            subjectLabel: $menu->label_ar,
        );

        return $item->fresh();
    }

    public function deleteItem(CmsMenuItem $item, ?User $actor = null): void
    {
        $menuKey = $item->menu?->key;
        $label = $item->label_ar;
        $item->delete();
        $this->forgetCache($menuKey);

        $this->audit->log(
            action: 'cms_menu_item.deleted',
            descriptionAr: 'حذف عنصر قائمة: '.$label,
            group: 'content',
            actor: $actor ?? auth()->user(),
            subjectLabel: $label,
        );
    }

    public function moveItem(CmsMenuItem $item, string $direction): void
    {
        $siblings = CmsMenuItem::query()
            ->where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->orderBy('sort_order')
            ->get();

        $index = $siblings->search(fn ($s) => $s->id === $item->id);

        if ($index === false) {
            return;
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! isset($siblings[$swapIndex])) {
            return;
        }

        $other = $siblings[$swapIndex];
        $currentOrder = $item->sort_order;
        $item->update(['sort_order' => $other->sort_order]);
        $other->update(['sort_order' => $currentOrder]);

        $this->forgetCache($item->menu?->key);
    }

    /** @param  array<int|string>  $orderedIds */
    public function reorderItems(?int $parentId, array $orderedIds, ?int $menuId = null): void
    {
        foreach (array_values($orderedIds) as $index => $id) {
            $query = CmsMenuItem::query()->whereKey((int) $id);

            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                $query->whereNull('parent_id');
            }

            if ($menuId) {
                $query->where('menu_id', $menuId);
            }

            $query->update(['sort_order' => ($index + 1) * 10]);
        }

        if ($menuId) {
            $menu = CmsMenu::query()->find($menuId);
            $this->forgetCache($menu?->key);
        }
    }
}
