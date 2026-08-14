<?php

namespace App\Console\Commands;

use App\Models\CatalogCourse;
use App\Support\CatalogSlugResolver;
use Illuminate\Console\Command;

class ImportLegacyCatalog extends Command
{
    protected $signature = 'catalog:import-legacy';

    protected $description = 'Import catalog courses from New-Platform/courses.html';

    public function handle(): int
    {
        $path = dirname(base_path()).'/New-Platform/courses.html';

        if (! is_readable($path)) {
            $this->error('courses.html not found.');

            return self::FAILURE;
        }

        $html = file_get_contents($path);
        $imported = 0;

        preg_match_all(
            '/cart-form-(\d+)[\s\S]*?<img src="(\.\/assets\/[^"]+)"[\s\S]*?<div class="gigs-title">\s*<h3>\s*<a href="(\.\/[^"]+)">\s*([\s\S]*?)\s*<\/a>[\s\S]*?data-prices="([^"]+)"/',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $id = (int) $match[1];
            $pricesJson = html_entity_decode($match[5]);
            $prices = json_decode($pricesJson, true) ?: [];

            $course = CatalogCourse::query()->updateOrCreate(
                ['id' => $id],
                [
                    'title_ar' => trim(html_entity_decode(strip_tags($match[4]))),
                    'image' => ltrim($match[2], './'),
                    'price_online' => isset($prices['online']) ? (float) $prices['online'] : null,
                    'price_onsite' => isset($prices['onsite']) ? (float) $prices['onsite'] : null,
                    'delivery_type' => isset($prices['online']) ? 'online' : 'onsite',
                    'status' => 'published',
                ]
            );

            CatalogSlugResolver::assignSlug($course);

            $imported++;
        }

        $this->info("Imported {$imported} catalog courses.");

        return self::SUCCESS;
    }
}
