<?php

namespace App\Services\Support;

use App\Models\CatalogCourse;
use App\Models\PlatformSetting;
use App\Support\OpenAiSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AiSupportKnowledgeBase
{
    /** @return list<array{id: string, title: string, tags: list<string>, content: string}> */
    public function allChunks(): array
    {
        return Cache::remember('ai_support.knowledge_chunks.v1', now()->addMinutes(30), function () {
            $chunks = [];

            foreach ($this->loadMarkdownFiles() as $chunk) {
                $chunks[] = $chunk;
            }

            foreach ($this->builtInFaqChunks() as $chunk) {
                $chunks[] = $chunk;
            }

            $live = $this->liveCatalogChunk();
            if ($live !== null) {
                $chunks[] = $live;
            }

            $contact = $this->liveContactChunk();
            if ($contact !== null) {
                $chunks[] = $contact;
            }

            return $chunks;
        });
    }

    /**
     * Lexical retrieval ranked by keyword overlap (Arabic + English).
     *
     * @return list<array{id: string, title: string, tags: list<string>, content: string, score: float}>
     */
    public function retrieve(string $query, ?int $limit = null): array
    {
        $limit = $limit ?? OpenAiSettings::knowledgeChunks();
        $tokens = $this->tokenize($query);

        $scored = [];

        foreach ($this->allChunks() as $chunk) {
            $haystack = Str::lower($chunk['title'].' '.implode(' ', $chunk['tags']).' '.$chunk['content']);
            $score = 0.0;

            foreach ($tokens as $token) {
                if ($token === '') {
                    continue;
                }
                if (str_contains($haystack, $token)) {
                    $score += mb_strlen($token) >= 4 ? 2.0 : 1.0;
                }
            }

            // Prefer always-relevant platform overview lightly
            if ($chunk['id'] === 'platform-overview') {
                $score += 0.35;
            }

            if ($score > 0) {
                $scored[] = $chunk + ['score' => $score];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        if ($scored === []) {
            // Fallback: overview + FAQ + contact
            $fallbackIds = ['platform-overview', 'faq-core', 'contact-channels', 'visitor-guide'];
            foreach ($this->allChunks() as $chunk) {
                if (in_array($chunk['id'], $fallbackIds, true)) {
                    $scored[] = $chunk + ['score' => 0.1];
                }
            }
        }

        return array_slice($scored, 0, $limit);
    }

    public function formatForPrompt(array $chunks): string
    {
        if ($chunks === []) {
            return 'لا تتوفر مقاطع معرفة إضافية لهذا السؤال.';
        }

        $parts = [];
        foreach ($chunks as $i => $chunk) {
            $n = $i + 1;
            $parts[] = "### مصدر {$n}: {$chunk['title']} (id: {$chunk['id']})\n{$chunk['content']}";
        }

        return implode("\n\n", $parts);
    }

    public function forgetCache(): void
    {
        Cache::forget('ai_support.knowledge_chunks.v1');
    }

    /** @return list<array{id: string, title: string, tags: list<string>, content: string}> */
    private function loadMarkdownFiles(): array
    {
        $dir = resource_path('support/knowledge');
        if (! File::isDirectory($dir)) {
            return [];
        }

        $chunks = [];
        foreach (File::files($dir) as $file) {
            if (strtolower($file->getExtension()) !== 'md') {
                continue;
            }

            $raw = File::get($file->getPathname());
            $meta = $this->parseFrontMatter($raw);
            $body = trim((string) ($meta['body'] ?? $raw));
            $id = (string) ($meta['id'] ?? pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $title = (string) ($meta['title'] ?? $id);
            $tags = $this->normalizeTags($meta['tags'] ?? []);

            if ($body === '') {
                continue;
            }

            // Split long docs into sections by H2 for better retrieval
            $sections = preg_split('/\n(?=##\s+)/u', $body) ?: [$body];
            if (count($sections) <= 1) {
                $chunks[] = [
                    'id' => $id,
                    'title' => $title,
                    'tags' => $tags,
                    'content' => $this->truncate($body, 4500),
                ];
                continue;
            }

            foreach ($sections as $index => $section) {
                $section = trim($section);
                if ($section === '') {
                    continue;
                }
                $sectionTitle = $title;
                if (preg_match('/^##\s+(.+)$/mu', $section, $m)) {
                    $sectionTitle = $title.' — '.trim($m[1]);
                }
                $chunks[] = [
                    'id' => $id.($index === 0 ? '' : '-'.$index),
                    'title' => $sectionTitle,
                    'tags' => $tags,
                    'content' => $this->truncate($section, 3500),
                ];
            }
        }

        return $chunks;
    }

    /** @return list<array{id: string, title: string, tags: list<string>, content: string}> */
    private function builtInFaqChunks(): array
    {
        return [[
            'id' => 'faq-core',
            'title' => 'الأسئلة الشائعة الأساسية',
            'tags' => ['faq', 'دعم', 'شائع', 'password', 'دفع', 'شهادة', 'تذكرة'],
            'content' => <<<'TXT'
1) كيف أتابع دوراتي بعد الشراء؟
بعد إتمام الدفع تظهر الدورة في «قائمة التعلم» من لوحة التحكم. اضغط «متابعة التعلم» للوصول للمحتوى.

2) كيف أسترجع كلمة المرور؟
من صفحة تسجيل الدخول اختر «نسيت كلمة المرور» — الاستعادة عبر البريد الإلكتروني أو رقم الجوال المسجّل (OTP للجوال).

3) ما طرق الدفع المتاحة؟
- دفع إلكتروني عبر Moyasar (بطاقات / مدى)
- تحويل بنكي (حسب تفعيل الإدارة)
- تقسيط طرف ثالث: Tabby / Tamara عند توفرها على المنتج
- تقسيط داخلي للمنصة (للدبلومات المؤهلة) عبر صفحة «أقساطي»

4) كيف أتحقق من الشهادة؟
من صفحة «التحقق من الشهادة» أدخل رمز الشهادة، أو من لوحة التحكم → شهاداتي.

5) كيف أصل للمادة العلمية؟
قائمة التعلم → متابعة التعلم → الوحدات والدروس (فيديو / قراءة / ملفات).

6) هل يلزم حضور المحاضرات يومياً؟
يختلف حسب نوع الدورة (عن بعد / حضوري / ذاتي). راجع تفاصيل البرنامج عند التسجيل.

7) كيف أفتح تذكرة دعم؟
من /support/ticket/new — املأ النموذج واحفظ رقم التذكرة (reference code) للمتابعة من صفحة البحث عن تذكرة.

8) لم أجد إجابتي؟
افتح تذكرة دعم أو تواصل عبر قنوات التواصل / واتساب الظاهر في الموقع.
TXT
        ]];
    }

    /** @return array{id: string, title: string, tags: list<string>, content: string}|null */
    private function liveCatalogChunk(): ?array
    {
        try {
            $courses = CatalogCourse::query()
                ->where('status', 'published')
                ->orderByDesc('is_featured')
                ->orderBy('title_ar')
                ->limit(40)
                ->get(['title_ar', 'title_en', 'slug', 'price_online', 'price_onsite', 'delivery_type', 'duration_label', 'city']);
        } catch (\Throwable) {
            return null;
        }

        if ($courses->isEmpty()) {
            return null;
        }

        $lines = ['دورات منشورة حالياً في كتالوج المنصة (عينة حية من قاعدة البيانات):'];
        foreach ($courses as $course) {
            $price = $course->price_online ?: $course->price_onsite;
            $priceLabel = $price !== null ? number_format((float) $price, 2).' SAR' : 'حسب التفاصيل';
            $lines[] = sprintf(
                '- %s | %s | توصيل: %s | مدة: %s | سعر تقريبي: %s | رابط slug: %s',
                $course->title_ar,
                $course->title_en ?: '—',
                $course->delivery_type ?: '—',
                $course->duration_label ?: '—',
                $priceLabel,
                $course->slug
            );
        }
        $lines[] = 'للتفاصيل الكاملة وجّه الزائر إلى صفحة /courses أو صفحة الدورة نفسها. لا تخترع أسعاراً غير موجودة في القائمة أعلاه.';

        return [
            'id' => 'live-catalog',
            'title' => 'كتالوج الدورات الحي',
            'tags' => ['courses', 'دورات', 'كتالوج', 'أسعار', 'catalog'],
            'content' => implode("\n", $lines),
        ];
    }

    /** @return array{id: string, title: string, tags: list<string>, content: string}|null */
    private function liveContactChunk(): ?array
    {
        $email = PlatformSetting::get('support_email', config('mail.from.address'));
        $phone = PlatformSetting::get('support_phone', '966543406744');
        $whatsapp = PlatformSetting::get('whatsapp_number', $phone);

        return [
            'id' => 'contact-channels',
            'title' => 'قنوات التواصل الرسمية',
            'tags' => ['contact', 'تواصل', 'واتساب', 'بريد', 'هاتف', 'دعم'],
            'content' => implode("\n", [
                'قنوات الدعم الرسمية لمنصة كن خبيراً / مركز التعلم المستمر:',
                '- البريد: '.($email ?: 'غير مضبوط حالياً — وجّه المستخدم لصفحة التواصل'),
                '- الهاتف: '.($phone ?: '—'),
                '- واتساب: '.($whatsapp ?: '—'),
                '- صفحة التواصل: /{locale}/contact و /{locale}/support/contact',
                '- الأسئلة الشائعة: /{locale}/support/faq',
                '- تذكرة دعم جديدة: /{locale}/support/ticket/new',
                '- متابعة تذكرة: /{locale}/support/ticket/search',
            ]),
        ];
    }

    /** @param  array<string, mixed>  $meta */
    private function parseFrontMatter(string $raw): array
    {
        if (! preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $raw, $m)) {
            return ['body' => $raw];
        }

        $meta = ['body' => $m[2]];
        foreach (preg_split('/\r\n|\r|\n/', $m[1]) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $meta[$key] = trim($value, " \t\"'");
        }

        if (isset($meta['tags']) && is_string($meta['tags'])) {
            $meta['tags'] = array_values(array_filter(array_map('trim', explode(',', $meta['tags']))));
        }

        return $meta;
    }

    /** @param  mixed  $tags
     *  @return list<string>
     */
    private function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }
        if (! is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(fn ($t) => Str::lower(trim((string) $t)), $tags)));
    }

    /** @return list<string> */
    private function tokenize(string $query): array
    {
        $query = Str::lower($query);
        $parts = preg_split('/[^\p{L}\p{N}_]+/u', $query) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => mb_strlen($p) >= 2));

        // Keep unique, prefer longer tokens first for scoring loops
        $parts = array_values(array_unique($parts));
        usort($parts, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return array_slice($parts, 0, 24);
    }

    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 1).'…';
    }
}
