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
                    'title' => 'تكامل معرفي بين جامعة الامير مقرن ومؤسسة بيرلس ترينينغ آند ديفيلوبمنت لتطوير القدرات البشرية',
                    'slug' => 'تكامل-معرفي-بين-معهد-البحوث-بجامعة-مقرن-ومؤسسة-بيرلس',
                    'excerpt' => 'شراكة استراتيجية لتعزيز جودة البرامج التدريبية وتبادل الخبرات في تطوير القدرات البشرية.',
                    'body' => '<p>أعلن جامعة الامير مقرن عن تكامل معرفي مع مؤسسة بيرلس ترينينغ آند ديفيلوبمنت، بهدف تطوير برامج تدريبية متقدمة تلبي احتياجات سوق العمل.</p>',
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
                    'title' => 'جامعة الامير مقرن تطلق برنامجاً تدريبياً لرفع كفاءة الكوادر المهنية بشركة قزاز للتجارة',
                    'slug' => 'برنامج-تدريبي-شركة-قزاز-للتجارة',
                    'excerpt' => 'إطلاق برنامج تدريبي متخصص لرفع كفاءة الكوادر المهنية في بيئة العمل.',
                    'body' => '<p>نفّذ المعهد برنامجاً تدريبياً لرفع كفاءة الكوادر المهنية بشركة قزاز للتجارة، ضمن مبادرات التطوير المؤسسي والتعلم المستمر.</p>',
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
