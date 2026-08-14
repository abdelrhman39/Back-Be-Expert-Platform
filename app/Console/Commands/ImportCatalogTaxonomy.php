<?php

namespace App\Console\Commands;

use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Models\CatalogField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportCatalogTaxonomy extends Command
{
    protected $signature = 'catalog:import-taxonomy {--force : Re-sync course relations}';

    protected $description = 'Import catalog categories, fields, and course relations from legacy HTML';

    /** @var array<string, int> */
    protected array $fieldSlugToLegacyId = [
        'accounting' => 14,
        'marketing' => 11,
        'computer' => 10,
        'project-management' => 8,
        'tourism-and-hotels' => 16,
    ];

    public function handle(): int
    {
        $coursesHtml = dirname(base_path()).'/New-Platform/courses.html';

        if (! is_readable($coursesHtml)) {
            $this->error('courses.html not found.');

            return self::FAILURE;
        }

        $html = file_get_contents($coursesHtml);

        DB::transaction(function () use ($html) {
            $this->importCategories($html);
            $this->importFields($html);
            $this->importCourseCategories($html);
            $this->importCourseFields();
        });

        $this->info('Catalog taxonomy imported.');

        return self::SUCCESS;
    }

    protected function importCategories(string $html): void
    {
        $items = $this->parseCheckboxList($html, 'categories');

        foreach ($items as $index => $item) {
            CatalogCategory::query()->updateOrCreate(
                ['id' => $item['id']],
                [
                    'title_ar' => $item['label'],
                    'slug' => Str::slug($item['label']),
                    'sort_order' => $index + 1,
                    'sidebar_visible' => ! $item['hidden'],
                ],
            );
        }

        $this->info('Categories: '.count($items));
    }

    protected function importFields(string $html): void
    {
        $items = $this->parseCheckboxList($html, 'fields');

        foreach ($items as $index => $item) {
            CatalogField::query()->updateOrCreate(
                ['id' => $item['id']],
                [
                    'title_ar' => $item['label'],
                    'slug' => Str::slug($item['label']),
                    'icon' => CatalogField::defaultIconMap()[$item['id']] ?? null,
                    'sort_order' => $index + 1,
                    'sidebar_visible' => ! $item['hidden'],
                    'home_visible' => ! $item['hidden'],
                ],
            );
        }

        $this->info('Fields: '.count($items));
    }

    /** @return list<array{id: int, label: string, hidden: bool}> */
    protected function parseCheckboxList(string $html, string $name): array
    {
        $visible = [];
        $hidden = [];

        if (! preg_match_all(
            '/name="'.preg_quote($name, '/').'\[\]" value="(\d+)"[\s\S]*?checked-title">\s*([\s\S]*?)\s*<\/span>/u',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        foreach ($matches as $match) {
            $label = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($match[2]))));
            $entry = ['id' => (int) $match[1], 'label' => $label, 'hidden' => false];
            $visible[] = $entry;
        }

        if (preg_match_all(
            '/viewall-one[\s\S]*?name="'.preg_quote($name, '/').'\[\]" value="(\d+)"[\s\S]*?checked-title">\s*([\s\S]*?)\s*<\/span>/u',
            $html,
            $hiddenMatches,
            PREG_SET_ORDER
        )) {
            foreach ($hiddenMatches as $match) {
                $label = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($match[2]))));
                $hidden[] = ['id' => (int) $match[1], 'label' => $label, 'hidden' => true];
            }
        }

        $merged = [];
        foreach ([...$visible, ...$hidden] as $item) {
            $merged[$item['id']] = $item;
        }

        return array_values($merged);
    }

    protected function importCourseCategories(string $html): void
    {
        if (! preg_match_all(
            '/cart-form-(\d+)[\s\S]*?cardBadge[\s\S]*?<\/svg>\s*([^<]+?)\s*<\/a>/u',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        $labels = CatalogCategory::query()->pluck('id', 'title_ar');

        foreach ($matches as $match) {
            $courseId = (int) $match[1];
            $label = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($match[2]))));

            if (! CatalogCourse::query()->whereKey($courseId)->exists()) {
                continue;
            }

            $categoryId = $labels[$label] ?? $this->resolveCategoryIdByLabel($label);

            if (! $categoryId) {
                continue;
            }

            CatalogCourse::query()->find($courseId)?->categories()->syncWithoutDetaching([$categoryId]);
        }

        $this->info('Course categories linked.');
    }

    protected function resolveCategoryIdByLabel(string $label): ?int
    {
        return CatalogCategory::query()
            ->get()
            ->first(fn (CatalogCategory $category) => trim($category->title_ar) === $label)
            ?->id;
    }

    protected function importCourseFields(): void
    {
        $fieldDir = dirname(base_path()).'/en-version/mirror/en/field';

        if (! is_dir($fieldDir)) {
            $this->warn('Field HTML directory not found — skipping field relations.');

            return;
        }

        foreach (glob($fieldDir.'/*.html') as $path) {
            $slug = basename($path, '.html');
            $fieldId = $this->fieldSlugToLegacyId[$slug] ?? null;

            if (! $fieldId || ! CatalogField::query()->whereKey($fieldId)->exists()) {
                continue;
            }

            $html = file_get_contents($path);

            if (! preg_match_all('/name="course_id"\s+value="(\d+)"/', $html, $matches)) {
                continue;
            }

            $courseIds = array_unique(array_map('intval', $matches[1]));

            foreach ($courseIds as $courseId) {
                if (! CatalogCourse::query()->whereKey($courseId)->exists()) {
                    continue;
                }

                CatalogCourse::query()->find($courseId)?->fields()->syncWithoutDetaching([$fieldId]);
            }
        }

        $this->info('Course fields linked.');
    }
}
