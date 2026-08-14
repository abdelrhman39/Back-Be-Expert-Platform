<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use App\Support\CmsBlockDefaults;
use Illuminate\Database\Seeder;

class CmsPageBlocksSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['home', 'about', 'contact'] as $type) {
            $page = CmsPage::query()->where('type', $type)->first();

            if (! $page) {
                continue;
            }

            foreach (['ar', 'en'] as $locale) {
                CmsPageTranslation::query()->updateOrCreate(
                    ['page_id' => $page->id, 'locale' => $locale],
                    [
                        'title' => match ($type) {
                            'home' => $locale === 'ar' ? 'الصفحة الرئيسية' : 'Home',
                            'about' => $locale === 'ar' ? 'عن المنصة' : 'About Us',
                            'contact' => $locale === 'ar' ? 'تواصل معنا' : 'Contact Us',
                        },
                        'slug' => $type,
                        'blocks' => CmsBlockDefaults::forPageType($type, $locale),
                        'body' => null,
                    ],
                );
            }

            $page->update([
                'status' => 'published',
                'published_at' => $page->published_at ?? now(),
            ]);
        }
    }
}
