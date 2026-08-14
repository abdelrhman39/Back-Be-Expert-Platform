<?php

namespace App\Services\Catalog;

use App\Models\CatalogCourse;

class LegacyCourseHtmlParser
{
    /** @var array<string, string> */
    public const TAB_MAP = [
        'course_brief' => 'brief',
        'course_goals' => 'goals',
        'target_auidence' => 'audience',
        'features' => 'features',
        'course_topics' => 'topics',
        'outcomes' => 'outcomes',
        'course_conditions' => 'conditions',
        'faq' => 'faq',
        'course_blog' => 'article',
    ];

    public function extractMainCourseId(string $html): ?int
    {
        if (preg_match(
            '/class="card p-4 sticky-top[\s\S]{0,4000}?name="course_id"\s+value="(\d+)"/',
            $html,
            $match,
        )) {
            return (int) $match[1];
        }

        if (preg_match('/<h1 class="breadcrumb-title[^"]*">/i', $html, $breadcrumb, PREG_OFFSET_CAPTURE)) {
            $segment = substr($html, $breadcrumb[0][1], 12000);
            if (preg_match('/name="course_id"\s+value="(\d+)"/', $segment, $match)) {
                return (int) $match[1];
            }
        }

        return null;
    }

    public function extractTitle(string $html): ?string
    {
        if (preg_match('/<h1 class="breadcrumb-title[^"]*">\s*([^<]+)/i', $html, $match)) {
            return trim(html_entity_decode(strip_tags($match[1])));
        }

        if (preg_match('/<title>([^<]+)<\/title>/i', $html, $match)) {
            return trim(html_entity_decode(strip_tags($match[1])));
        }

        return null;
    }

    public function extractMetaDescription(string $html): ?string
    {
        if (! preg_match('/<meta name="description" content="([^"]*)"/', $html, $match)) {
            return null;
        }

        $value = html_entity_decode($match[1]);

        return filled($value) ? $value : null;
    }

    /** @return array<string, ?string> */
    public function extractTabs(string $html): array
    {
        $tabs = [];

        foreach (self::TAB_MAP as $htmlId => $field) {
            $tabs[$field] = $this->extractTabPane($html, $htmlId);
        }

        return $tabs;
    }

    public function extractTabPane(string $html, string $tabId): ?string
    {
        if (! preg_match('/<div[^>]+id="'.preg_quote($tabId, '/').'"[^>]*role="tabpanel"[^>]*>/i', $html, $open, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $offset = $open[0][1] + strlen($open[0][0]);
        $fragment = substr($html, $offset);
        $depth = 1;
        $length = strlen($fragment);
        $index = 0;

        while ($index < $length) {
            if (! preg_match('/<\/?div\b/i', $fragment, $tag, PREG_OFFSET_CAPTURE, $index)) {
                break;
            }

            $position = $tag[0][1];
            $tagName = $tag[0][0];

            if (preg_match('/^<div/i', $tagName)) {
                $depth++;
            } elseif (preg_match('/^<\/div/i', $tagName)) {
                $depth--;
                if ($depth === 0) {
                    return $this->cleanTabHtml(substr($fragment, 0, $position));
                }
            }

            $index = $position + strlen($tagName);
        }

        return null;
    }

    public function cleanTabHtml(string $html): ?string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        if (preg_match('/<div class="service-wrap"[^>]*>(.*?)<\/div>\s*$/s', $html, $wrap)) {
            $html = $wrap[1];
        }

        $html = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is', '', $html) ?? $html;
        $html = preg_replace('/<h3\b[^>]*>.*?<\/h3>/is', '', $html, 1) ?? $html;
        $html = trim($html);

        return filled(strip_tags($html)) ? $html : null;
    }

    public function resolveArabicHtmlPath(string $arDir, CatalogCourse $course): ?string
    {
        $candidates = array_filter([
            $course->slug,
            $course->slug ? str_replace('.html', '', $course->slug) : null,
        ]);

        foreach ($candidates as $slug) {
            foreach ([$slug, $slug.'.html'] as $name) {
                $path = rtrim($arDir, '/\\').'/'.$name;
                if (! is_file($path)) {
                    continue;
                }

                $html = file_get_contents($path);
                $mainId = $this->extractMainCourseId($html);

                if ($mainId === (int) $course->id) {
                    return $path;
                }
            }
        }

        foreach (glob(rtrim($arDir, '/\\').'/course*.html') ?: [] as $path) {
            $html = file_get_contents($path);
            if ($this->extractMainCourseId($html) === (int) $course->id) {
                return $path;
            }
        }

        return null;
    }
}
