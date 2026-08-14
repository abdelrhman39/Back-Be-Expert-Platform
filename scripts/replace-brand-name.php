<?php

use App\Models\CmsPageTranslation;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Longer phrases first.
$replacements = [
    'منصة كن خبير' => 'منصة مركز التعلم المستمر',
    'منصة كُن خبير' => 'منصة مركز التعلم المستمر',
    'كُن خبير' => 'منصة مركز التعلم المستمر',
    'كن خبير' => 'منصة مركز التعلم المستمر',
    'Be Expert Platform' => 'Continuing Learning Center Platform',
    'Be Expert platform' => 'Continuing Learning Center Platform',
    'Be Expert' => 'Continuing Learning Center',
    'Be expert' => 'Continuing Learning Center',
    'be expert' => 'Continuing Learning Center',
];

function brand_replace(string $text, array $replacements): string
{
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

function brand_replace_deep(mixed $value, array $replacements): mixed
{
    if (is_string($value)) {
        return brand_replace($value, $replacements);
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = brand_replace_deep($item, $replacements);
        }
    }

    return $value;
}

$updated = 0;

if (class_exists(CmsPageTranslation::class)) {
    foreach (CmsPageTranslation::query()->get() as $row) {
        $dirty = false;

        if (is_array($row->blocks)) {
            $next = brand_replace_deep($row->blocks, $replacements);
            if ($next !== $row->blocks) {
                $row->blocks = $next;
                $dirty = true;
            }
        }

        foreach (['body', 'title', 'meta_title', 'meta_description', 'excerpt'] as $col) {
            $current = $row->{$col} ?? null;
            if (! is_string($current) || $current === '') {
                continue;
            }
            $next = brand_replace($current, $replacements);
            if ($next !== $current) {
                $row->{$col} = $next;
                $dirty = true;
            }
        }

        if ($dirty) {
            $row->save();
            $updated++;
            echo "cms_page_translations#{$row->id}\n";
        }
    }
}

$stringTables = [
    'platform_settings' => ['value', 'description'],
    'article_translations' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description'],
    'faqs' => ['question', 'answer', 'question_ar', 'answer_ar', 'question_en', 'answer_en'],
    'faq_translations' => ['question', 'answer'],
    'testimonial_translations' => ['name', 'content', 'body', 'quote', 'role'],
];

foreach ($stringTables as $table => $columns) {
    if (! Schema::hasTable($table)) {
        continue;
    }

    $existing = Schema::getColumnListing($table);
    $columns = array_values(array_intersect($columns, $existing));
    if ($columns === []) {
        continue;
    }

    foreach (DB::table($table)->get() as $row) {
        $payload = [];
        foreach ($columns as $col) {
            $current = $row->{$col} ?? null;
            if (! is_string($current) || $current === '') {
                continue;
            }
            $next = brand_replace($current, $replacements);
            if ($next !== $current) {
                $payload[$col] = $next;
            }
        }
        if ($payload !== []) {
            DB::table($table)->where('id', $row->id)->update($payload);
            $updated++;
            echo "{$table}#{$row->id}\n";
        }
    }
}

echo "Updated records: {$updated}\n";
