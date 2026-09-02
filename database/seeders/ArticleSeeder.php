<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use App\Services\ArticleService;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = app(ArticleService::class);

        $articles->save([
            'status' => 'published',
            'category' => 'news',
            'is_featured' => true,
            'sort_order' => 10,
            'translations' => [
                'ar' => [
                    'title' => 'تكامل معرفي بين الجامعة العربية المفتوحة ومؤسسة بيرلس ترينينغ آند ديفيلوبمنت لتطوير القدرات البشرية',
                    'slug' => 'تكامل-معرفي-بين-الجامعة-العربية-المفتوحة-ومؤسسة-بيرلس',
                    'excerpt' => 'شراكة استراتيجية لتعزيز جودة البرامج التدريبية وتبادل الخبرات في تطوير القدرات البشرية.',
                    'body' => '<p>أعلنت الجامعة العربية المفتوحة عن تكامل معرفي مع مؤسسة بيرلس ترينينغ آند ديفيلوبمنت، بهدف تطوير برامج تدريبية متقدمة تلبي احتياجات سوق العمل.</p>',
                ],
                'en' => [
                    'title' => 'Knowledge partnership between Arab Open University and Perls Training & Development',
                    'slug' => 'aou-perls-knowledge-partnership',
                    'excerpt' => 'A strategic partnership to strengthen training quality and exchange expertise in human-capacity development.',
                    'body' => '<p>Arab Open University announced a knowledge partnership with Perls Training & Development to advance training programs that meet labor-market needs.</p>',
                ],
            ],
        ]);

        $articles->save([
            'status' => 'published',
            'category' => 'event',
            'is_featured' => false,
            'sort_order' => 5,
            'translations' => [
                'ar' => [
                    'title' => 'الجامعة العربية المفتوحة تطلق برنامجاً تدريبياً لرفع كفاءة الكوادر المهنية بشركة قزاز للتجارة',
                    'slug' => 'برنامج-تدريبي-شركة-قزاز-للتجارة',
                    'excerpt' => 'إطلاق برنامج تدريبي متخصص لرفع كفاءة الكوادر المهنية في بيئة العمل.',
                    'body' => '<p>نفّذ المعهد برنامجاً تدريبياً لرفع كفاءة الكوادر المهنية بشركة قزاز للتجارة، ضمن مبادرات التطوير المؤسسي والتعلم المستمر.</p>',
                ],
                'en' => [
                    'title' => 'Arab Open University launches a professional-skills program with Qazzaz Trading',
                    'slug' => 'qazzaz-trading-professional-skills-program',
                    'excerpt' => 'A specialized training program to raise professional capability in the workplace.',
                    'body' => '<p>The center delivered a professional-skills program for Qazzaz Trading as part of its organizational development and continuing-learning initiatives.</p>',
                ],
            ],
        ]);

        $this->upgradeHomeNewsBlocks();
    }

    private function upgradeHomeNewsBlocks(): void
    {
        $home = CmsPage::query()->where('type', 'home')->first();

        if (! $home) {
            return;
        }

        foreach (['ar', 'en'] as $locale) {
            $translation = CmsPageTranslation::query()
                ->where('page_id', $home->id)
                ->where('locale', $locale)
                ->first();

            if (! $translation || ! is_array($translation->blocks)) {
                continue;
            }

            $blocks = array_map(function (array $block): array {
                if (($block['type'] ?? '') !== 'news_cards') {
                    return $block;
                }

                $data = $block['data'] ?? [];
                $data['source'] = 'latest_articles';
                $data['limit'] = (int) ($data['limit'] ?? 3);
                unset($data['items']);
                $block['data'] = $data;

                return $block;
            }, $translation->blocks);

            $translation->update(['blocks' => $blocks]);
        }
    }
}
