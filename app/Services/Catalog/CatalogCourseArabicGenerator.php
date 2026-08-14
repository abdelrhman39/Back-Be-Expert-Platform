<?php

namespace App\Services\Catalog;

class CatalogCourseArabicGenerator
{
    public function __construct(
        private readonly CatalogCourseTranslator $translator,
    ) {}

    /** @param  array<string, mixed>  $payload */
    public function generateArabicFields(array $payload): array
    {
        $fields = [
            'brief', 'goals', 'audience', 'features', 'topics',
            'outcomes', 'conditions', 'faq', 'article',
        ];

        foreach ($fields as $field) {
            $enKey = $field.'_en';
            $arKey = $field.'_ar';

            if (filled(strip_tags((string) ($payload[$arKey] ?? '')))) {
                continue;
            }

            if (! filled(strip_tags((string) ($payload[$enKey] ?? '')))) {
                continue;
            }

            $payload[$arKey] = $this->translator->translateHtmlToArabic($payload[$enKey]);

            if ($field === 'brief' && filled($payload[$arKey])) {
                $payload[$arKey] = $this->appendContactCta((string) $payload[$arKey]);
            }
        }

        if (! filled($payload['meta_description_ar'] ?? null) && filled($payload['meta_description_en'] ?? null)) {
            $payload['meta_description_ar'] = $this->translator->translatePlainText($payload['meta_description_en']);
        }

        return $payload;
    }

    protected function appendContactCta(string $html): string
    {
        if (str_contains($html, 'fixed-phone') || str_contains($html, 'fixed-whatsapp')) {
            return $html;
        }

        return $html.view('partials.catalog.course-contact-cta')->render();
    }
}
