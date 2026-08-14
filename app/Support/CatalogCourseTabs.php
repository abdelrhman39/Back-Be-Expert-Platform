<?php

namespace App\Support;

class CatalogCourseTabs
{
    /** @return array<string, array{label: string, html_id: string}> */
    public static function definitions(): array
    {
        return [
            'brief' => ['label' => 'الوصف العام', 'html_id' => 'course_brief'],
            'goals' => ['label' => 'الأهداف', 'html_id' => 'course_goals'],
            'audience' => ['label' => 'الفئة المستهدفة', 'html_id' => 'target_auidence'],
            'features' => ['label' => 'المميزات', 'html_id' => 'features'],
            'topics' => ['label' => 'المحاور', 'html_id' => 'course_topics'],
            'outcomes' => ['label' => 'المخرجات', 'html_id' => 'outcomes'],
            'conditions' => ['label' => 'المتطلبات', 'html_id' => 'course_conditions'],
            'faq' => ['label' => 'الأسئلة الشائعة', 'html_id' => 'faq'],
            'article' => ['label' => 'المقالة', 'html_id' => 'course_blog'],
        ];
    }

    public static function htmlId(string $key): string
    {
        return self::definitions()[$key]['html_id'] ?? $key;
    }

    public static function label(string $key): string
    {
        return self::definitions()[$key]['label'] ?? $key;
    }
}
