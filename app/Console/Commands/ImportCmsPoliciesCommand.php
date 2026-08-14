<?php

namespace App\Console\Commands;

use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportCmsPoliciesCommand extends Command
{
    protected $signature = 'cms:import-policies {--force : Overwrite existing body content}';

    protected $description = 'Import policy page HTML from en-version/mirror/en/page/ into CMS';

    public function handle(): int
    {
        $sourceDir = base_path('../en-version/mirror/en/page');

        if (! is_dir($sourceDir)) {
            $this->error('Source directory not found: '.$sourceDir);

            return self::FAILURE;
        }

        $imported = 0;

        foreach (File::files($sourceDir) as $file) {
            if ($file->getExtension() !== 'html') {
                continue;
            }

            $slug = $file->getFilenameWithoutExtension();
            $html = File::get($file->getPathname());
            $body = $this->extractBody($html);

            if ($body === '') {
                $this->warn("Skipped empty content: {$slug}");

                continue;
            }

            $page = CmsPage::query()->where('legacy_slug', $slug)->first();

            if (! $page) {
                $this->warn("No CMS page for legacy slug: {$slug}");

                continue;
            }

            $enTranslation = $page->translations()->where('locale', 'en')->first();
            $title = $this->extractTitle($html) ?: ($enTranslation?->title ?? $slug);

            if ($enTranslation && filled($enTranslation->body) && ! $this->option('force')) {
                $this->line("Skipped (has content): {$slug}");

                continue;
            }

            CmsPageTranslation::query()->updateOrCreate(
                ['page_id' => $page->id, 'locale' => 'en'],
                [
                    'title' => $title,
                    'slug' => $slug,
                    'body' => $body,
                ],
            );

            $ar = $page->translations()->where('locale', 'ar')->first();

            if ($ar && (blank($ar->body) || str_contains($ar->body, 'يمكن تعديله من لوحة التحكم')) && $this->option('force')) {
                $ar->update(['body' => $body]);
            }

            $imported++;
            $this->info("Imported: {$slug}");
        }

        app(\App\Services\CmsMenuService::class)->forgetCache();
        $this->info("Done. Imported {$imported} policies.");

        return self::SUCCESS;
    }

    protected function extractTitle(string $html): ?string
    {
        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
            return trim(html_entity_decode(strip_tags($m[1])));
        }

        return null;
    }

    protected function extractBody(string $html): string
    {
        if (! preg_match('/<div class="page-content pages-detail">(.*?)<\/div>\s*(?:<a href="tel:|<!--)/s', $html, $m)) {
            return '';
        }

        $chunk = $m[1];

        if (preg_match('/<div class="col-md-8 col-12">\s*<p>\s*(.*?)\s*<\/p>\s*<\/div>/s', $chunk, $inner)) {
            return trim($inner[1]);
        }

        return trim(strip_tags($chunk)) === '' ? '' : trim($chunk);
    }
}
