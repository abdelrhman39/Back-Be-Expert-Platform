<?php

namespace App\Console\Commands;

use App\Support\CatalogSlugResolver;
use Illuminate\Console\Command;

class FixCatalogSlugs extends Command
{
    protected $signature = 'catalog:fix-slugs';

    protected $description = 'Assign unique slugs to all catalog courses';

    public function handle(): int
    {
        $fixed = CatalogSlugResolver::fixAllCourses();

        $this->info("Updated {$fixed} course slug(s).");

        return self::SUCCESS;
    }
}
