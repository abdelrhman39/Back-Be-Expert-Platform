<?php

namespace App\Support;

use App\Models\User;

class AdminNavigation
{
    /** @return array<int, array<string, mixed>> */
    public static function sidebarForJs(?User $user = null): array
    {
        $user ??= auth()->user();

        return static::resolveMenu(static::filterMenuConfig(config('admin.sidebar', []), $user));
    }

    /** @return array<int, array<string, mixed>> */
    public static function subnavForJs(?User $user = null): array
    {
        $user ??= auth()->user();
        $items = [];

        foreach (config('admin.subnav', []) as $item) {
            if (! AdminPermissions::canAccessRoute($user, $item['route'])) {
                continue;
            }

            $items[] = [
                'id' => $item['id'],
                'href' => route($item['route']),
                'label' => $item['label'],
            ];
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private static function filterMenuConfig(array $items, ?User $user): array
    {
        $filtered = [];

        foreach ($items as $item) {
            $type = $item['type'] ?? '';

            if ($type === 'link') {
                if (AdminPermissions::canAccessRoute($user, $item['route'] ?? null)) {
                    $filtered[] = $item;
                }

                continue;
            }

            if ($type === 'group') {
                $sections = [];

                foreach ($item['children'] ?? [] as $section) {
                    $links = array_values(array_filter(
                        $section['items'] ?? [],
                        fn (array $link): bool => AdminPermissions::canAccessRoute($user, $link['route'] ?? null)
                    ));

                    if ($links === []) {
                        continue;
                    }

                    $section['items'] = $links;
                    $sections[] = $section;
                }

                if ($sections !== []) {
                    $item['children'] = $sections;
                    $filtered[] = $item;
                }
            }
        }

        return $filtered;
    }

    /** @param  array<int, array<string, mixed>>  $items */
    private static function resolveMenu(array $items): array
    {
        return array_map(function (array $item): array {
            if (($item['type'] ?? '') === 'link') {
                $item['href'] = route($item['route']);
                unset($item['route']);

                return $item;
            }

            if (($item['type'] ?? '') === 'group') {
                $item['children'] = array_map(function (array $section): array {
                    if (isset($section['items'])) {
                        $section['items'] = array_map(function (array $link): array {
                            $link['href'] = route($link['route']);
                            unset($link['route']);

                            return $link;
                        }, $section['items']);
                    }

                    return $section;
                }, $item['children'] ?? []);
            }

            return $item;
        }, $items);
    }

    /** @return array<int, array{href?: string, label: string}> */
    public static function breadcrumb(string $currentLabel, ?string $parentRoute = 'admin.dashboard', ?string $parentLabel = 'الرئيسية'): array
    {
        $trail = [];

        if ($parentRoute) {
            $trail[] = ['href' => route($parentRoute), 'label' => $parentLabel ?? 'الرئيسية'];
        }

        $trail[] = ['label' => $currentLabel];

        return $trail;
    }
}
