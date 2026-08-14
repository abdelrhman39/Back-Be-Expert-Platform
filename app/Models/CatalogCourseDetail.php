<?php

namespace App\Models;

use App\Support\CatalogCourseTabs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CatalogCourseDetail extends Model
{
    protected $primaryKey = 'course_id';

    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'meta_description_ar',
        'meta_description_en',
        'content_blocks',
        'brief_ar',
        'brief_en',
        'goals_ar',
        'goals_en',
        'audience_ar',
        'audience_en',
        'features_ar',
        'features_en',
        'topics_ar',
        'topics_en',
        'outcomes_ar',
        'outcomes_en',
        'conditions_ar',
        'conditions_en',
        'faq_ar',
        'faq_en',
        'article_ar',
        'article_en',
    ];

    protected function casts(): array
    {
        return [
            'content_blocks' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CatalogCourse::class, 'course_id');
    }

    /** @return array<string, array{label: string, ar: ?string, en: ?string}> */
    public function tabs(): array
    {
        $fields = [
            'brief', 'goals', 'audience', 'features', 'topics',
            'outcomes', 'conditions', 'faq', 'article',
        ];

        $tabs = [];
        foreach ($fields as $key) {
            $tabs[$key] = [
                'label' => CatalogCourseTabs::label($key),
                'ar' => $this->{"{$key}_ar"},
                'en' => $this->{"{$key}_en"},
            ];
        }

        return $tabs;
    }

    public function tabContent(string $key): ?string
    {
        $locale = app()->getLocale();
        $tabs = $this->tabs();

        if (! isset($tabs[$key])) {
            return null;
        }

        return $locale === 'en' && filled($tabs[$key]['en'])
            ? $tabs[$key]['en']
            : $tabs[$key]['ar'];
    }

    /** @return array<string, array{label: string, content: ?string, html_id: string}> */
    public function availableTabs(): array
    {
        $blocks = $this->normalizedContentBlocks();

        if ($blocks !== []) {
            $available = [];

            foreach ($blocks as $index => $block) {
                if (! ($block['enabled'] ?? true)) {
                    continue;
                }

                $content = (string) ($block['content'] ?? '');
                if (! filled(strip_tags($content))) {
                    continue;
                }

                $type = (string) ($block['type'] ?? 'custom');
                $key = 'block_'.$index;

                $available[$key] = [
                    'label' => filled($block['title'] ?? null)
                        ? (string) $block['title']
                        : CatalogCourseTabs::label($type),
                    'content' => $content,
                    'html_id' => $type === 'custom'
                        ? 'course_block_'.$index
                        : CatalogCourseTabs::htmlId($type).'_'.$index,
                ];
            }

            return $available;
        }

        $available = [];

        foreach ($this->tabs() as $key => $tab) {
            $content = $this->tabContent($key) ?: ($tab['ar'] ?: $tab['en']);
            if (filled(strip_tags((string) $content))) {
                $available[$key] = [
                    'label' => $tab['label'],
                    'content' => $content,
                    'html_id' => CatalogCourseTabs::htmlId($key),
                ];
            }
        }

        return $available;
    }

    /**
     * @return list<array{id: string, type: string, title: string, content: string, enabled: bool}>
     */
    public function normalizedContentBlocks(): array
    {
        $blocks = $this->content_blocks;

        if (! is_array($blocks) || $blocks === []) {
            return [];
        }

        $normalized = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? 'custom');
            $normalized[] = [
                'id' => (string) ($block['id'] ?? Str::uuid()),
                'type' => $type,
                'title' => (string) ($block['title'] ?? CatalogCourseTabs::label($type)),
                'content' => (string) ($block['content'] ?? ''),
                'enabled' => (bool) ($block['enabled'] ?? true),
            ];
        }

        return $normalized;
    }

    /**
     * Build editable blocks from legacy columns when content_blocks is empty.
     *
     * @return list<array{id: string, type: string, title: string, content: string, enabled: bool}>
     */
    public function blocksForEditor(): array
    {
        $stored = $this->normalizedContentBlocks();

        if ($stored !== []) {
            return $stored;
        }

        $blocks = [];

        foreach (array_keys(CatalogCourseTabs::definitions()) as $key) {
            $content = (string) ($this->{"{$key}_ar"} ?? '');
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => $key,
                'title' => CatalogCourseTabs::label($key),
                'content' => $content,
                'enabled' => filled(strip_tags($content)),
            ];
        }

        return $blocks;
    }

    public function defaultTabKey(): ?string
    {
        $tabs = $this->availableTabs();

        if ($tabs === []) {
            return null;
        }

        return isset($tabs['article']) ? 'article' : array_key_first($tabs);
    }
}
