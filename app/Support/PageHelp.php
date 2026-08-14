<?php

namespace App\Support;

class PageHelp
{
    /** @return array{title: string, description: string, steps: array<int, string>} */
    public static function current(): array
    {
        $routeName = request()->route()?->getName() ?? '';
        $exact = config('page-help.routes', [])[$routeName] ?? null;

        if (is_array($exact)) {
            return static::normalize($exact);
        }

        foreach (config('page-help.modules', []) as $module) {
            foreach ($module['patterns'] ?? [] as $pattern) {
                if (fnmatch($pattern, $routeName)) {
                    return static::normalize($module);
                }
            }
        }

        return static::normalize([]);
    }

    /** @param array<string, mixed> $help
     * @return array{title: string, description: string, steps: array<int, string>}
     */
    private static function normalize(array $help): array
    {
        return [
            'title' => (string) ($help['title'] ?? 'دليل استخدام الصفحة'),
            'description' => (string) ($help['description'] ?? 'تعرف على هدف الصفحة وآلية استخدامها.'),
            'steps' => array_values(array_filter($help['steps'] ?? [], 'is_string')),
        ];
    }
}
