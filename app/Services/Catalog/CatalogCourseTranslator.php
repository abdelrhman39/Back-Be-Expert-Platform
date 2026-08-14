<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Http;

class CatalogCourseTranslator
{
    public function translateHtmlToArabic(?string $html): ?string
    {
        if ($html === null || ! filled(strip_tags($html))) {
            return null;
        }

        if ($this->looksArabic($html)) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $document = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="utf-8" ?><div id="course-root">'.$html.'</div>';
        $document->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $root = $document->getElementById('course-root');
        if (! $root) {
            return $this->translatePlainText(strip_tags($html));
        }

        $this->translateDomNode($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        $output = trim($output);

        return filled(strip_tags($output)) ? $this->normalizeArabicHtml($output) : null;
    }

    public function translatePlainText(?string $text): ?string
    {
        if ($text === null || ! filled(trim($text))) {
            return null;
        }

        if ($this->looksArabic($text)) {
            return trim($text);
        }

        $chunks = $this->chunkText($text, 3500);
        $translated = [];

        foreach ($chunks as $chunk) {
            $translated[] = $this->requestTranslation($chunk);
            usleep(150000);
        }

        return trim(implode(' ', array_filter($translated)));
    }

    protected function translateDomNode(\DOMNode $node): void
    {
        if ($node instanceof \DOMText) {
            $text = trim($node->textContent);
            if ($text === '' || $this->looksArabic($text)) {
                return;
            }

            $node->textContent = $this->requestTranslation($text);
            usleep(120000);

            return;
        }

        if ($node instanceof \DOMElement && $node->hasAttribute('dir')) {
            $node->setAttribute('dir', 'rtl');
        }

        if ($node->childNodes->length === 0) {
            return;
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->translateDomNode($child);
        }
    }

    protected function requestTranslation(string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '') {
            return '';
        }

        try {
            $response = Http::timeout(20)->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx',
                'sl' => 'en',
                'tl' => 'ar',
                'dt' => 't',
                'q' => $text,
            ]);

            if (! $response->successful()) {
                return $text;
            }

            $payload = $response->json();
            $parts = collect($payload[0] ?? [])
                ->pluck(0)
                ->filter()
                ->implode('');

            return filled($parts) ? html_entity_decode($parts, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $text;
        } catch (\Throwable) {
            return $text;
        }
    }

    /** @return list<string> */
    protected function chunkText(string $text, int $limit): array
    {
        if (mb_strlen($text) <= $limit) {
            return [$text];
        }

        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];
        $buffer = '';

        foreach ($sentences as $sentence) {
            if (mb_strlen($buffer.' '.$sentence) > $limit && $buffer !== '') {
                $chunks[] = trim($buffer);
                $buffer = $sentence;
            } else {
                $buffer = trim($buffer.' '.$sentence);
            }
        }

        if ($buffer !== '') {
            $chunks[] = $buffer;
        }

        return $chunks;
    }

    protected function looksArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', strip_tags($text));
    }

    protected function normalizeArabicHtml(string $html): string
    {
        $html = preg_replace('/\sdir="ltr"/i', ' dir="rtl"', $html) ?? $html;

        if (! str_contains($html, 'dir="rtl"')) {
            if (preg_match('/^<p\b/i', $html)) {
                $html = preg_replace('/^<p\b/i', '<p dir="rtl"', $html, 1) ?? $html;
            } elseif (preg_match('/^<ul\b/i', $html)) {
                $html = preg_replace('/^<ul\b/i', '<ul dir="rtl"', $html, 1) ?? $html;
            } else {
                $html = '<div dir="rtl">'.$html.'</div>';
            }
        }

        return $html;
    }
}
