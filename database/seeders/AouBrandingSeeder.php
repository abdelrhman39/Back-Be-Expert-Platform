<?php

namespace Database\Seeders;

use App\Models\ArticleTranslation;
use App\Models\CmsPage;
use App\Models\CmsPageTranslation;
use App\Models\PaymentSetting;
use App\Models\PlatformSetting;
use App\Support\CampusMap;
use App\Support\IdentityThemes;
use App\Support\LogoSettings;
use Illuminate\Database\Seeder;

class AouBrandingSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::set('platform_org_ar', 'الجامعة العربية المفتوحة', 'general', 'الجهة / الجامعة (عربي)');
        PlatformSetting::set('platform_org_en', 'Arab Open University', 'general', 'الجهة / الجامعة (إنجليزي)');
        PlatformSetting::set(LogoSettings::KEY_PRIMARY, 'assets/branding/aou-logo.png', 'branding', 'الشعار الرئيسي');
        PlatformSetting::set(LogoSettings::KEY_FOOTER, 'assets/branding/aou-logo-footer.png', 'branding', 'شعار الفوتر');
        PlatformSetting::set(LogoSettings::KEY_FAVICON, 'assets/branding/aou-favicon.png', 'branding', 'أيقونة الموقع (Favicon)');
        PlatformSetting::set(LogoSettings::KEY_PRIMARY_VISIBLE, '1', 'branding', 'إظهار الشعار الرئيسي');
        PlatformSetting::set(LogoSettings::KEY_SECONDARY_VISIBLE, '0', 'branding', 'إظهار الشعار الثانوي');
        PlatformSetting::set(LogoSettings::KEY_FOOTER_VISIBLE, '1', 'branding', 'إظهار شعار الفوتر');
        PlatformSetting::set(LogoSettings::KEY_VISION_VISIBLE, '0', 'branding', 'إظهار شعار الرؤية');
        PlatformSetting::set(LogoSettings::KEY_FOOTER_LOGOS_MODE, LogoSettings::FOOTER_LOGOS_FIRST, 'branding', 'وضع شعارات الفوتر');
        PlatformSetting::set(
            'footer_copyright_ar',
            'جميع الحقوق محفوظة {platform_org}، تطوير وتصميم <span class="fw-bold">{platform_name}</span>',
            'general',
            'حقوق النشر في الفوتر (عربي)',
        );
        PlatformSetting::set(
            'footer_copyright_en',
            'All rights reserved, {platform_org}. Designed and developed by <span class="fw-bold">{platform_name}</span>',
            'general',
            'حقوق النشر في الفوتر (إنجليزي)',
        );

        IdentityThemes::apply(IdentityThemes::DEFAULT);

        $this->replaceOrgNameInPaymentInstructions();
        $this->replaceOrgNameInArticles();
        $this->replaceOrgNameInContactAddress();
        $this->replaceOrgNameInFooterAbout();
        $this->applyCampusImages();
        $this->applyAouContactMap();
    }

    protected function replaceOrgNameInPaymentInstructions(): void
    {
        foreach (['bank_transfer_instructions_ar', 'bank_transfer_instructions_en'] as $key) {
            $value = PaymentSetting::get($key);

            if (! filled($value)) {
                continue;
            }

            $updated = str_replace(
                ['جامعة الامير مقرن', 'University of Prince Muqrin', 'Muqrin University'],
                ['الجامعة العربية المفتوحة', 'Arab Open University', 'Arab Open University'],
                $value,
            );

            if ($updated !== $value) {
                PaymentSetting::set($key, $updated);
            }
        }
    }

    protected function replaceOrgNameInArticles(): void
    {
        ArticleTranslation::query()->each(function (ArticleTranslation $translation): void {
            $dirty = false;

            foreach (['title', 'excerpt', 'body'] as $field) {
                $value = (string) $translation->{$field};

                if ($value === '') {
                    continue;
                }

                $updated = str_replace(
                    ['جامعة الامير مقرن', 'جامعة حائل'],
                    ['الجامعة العربية المفتوحة', 'الجامعة العربية المفتوحة السعودية'],
                    $value
                );

                if ($updated !== $value) {
                    $translation->{$field} = $updated;
                    $dirty = true;
                }
            }

            if ($dirty) {
                $translation->save();
            }
        });
    }

    protected function replaceOrgNameInContactAddress(): void
    {
        $contact = CmsPage::query()->where('type', 'contact')->first();

        if (! $contact) {
            return;
        }

        foreach (CmsPageTranslation::query()->where('page_id', $contact->id)->get() as $translation) {
            $blocks = $translation->blocks;

            if (! is_array($blocks)) {
                continue;
            }

            $changed = false;

            foreach ($blocks as &$block) {
                if (($block['type'] ?? '') !== 'contact_channels') {
                    continue;
                }

                foreach ($block['data']['items'] ?? [] as &$item) {
                    if (($item['kind'] ?? '') !== 'address') {
                        continue;
                    }

                    $value = (string) ($item['value'] ?? '');

                    if ($value === '' || str_contains($value, 'مقرن') || str_contains($value, 'Muqrin') || str_contains($value, 'Hail') || str_contains($value, 'حائل')) {
                        $item['value'] = '{platform_org}';
                        $changed = true;
                    }
                }
                unset($item);
            }
            unset($block);

            if ($changed) {
                $translation->blocks = $blocks;
                $translation->save();
            }
        }
    }

    protected function replaceOrgNameInFooterAbout(): void
    {
        foreach (['footer_about_ar', 'footer_about_en'] as $key) {
            $value = PlatformSetting::get($key);

            if (! filled($value)) {
                continue;
            }

            $updated = str_replace(
                ['جامعة الامير مقرن', 'University of Prince Muqrin', 'Muqrin University'],
                ['الجامعة العربية المفتوحة', 'Arab Open University', 'Arab Open University'],
                $value,
            );

            if ($updated !== $value) {
                PlatformSetting::set($key, $updated, 'footer');
            }
        }
    }

    protected function applyCampusImages(): void
    {
        $aerial = platform_campus_path('aerial');
        $entrance = platform_campus_path('entrance');

        $map = [
            'home' => [
                'hero' => ['image' => $aerial],
            ],
            'about' => [
                'breadcrumb' => ['background_image' => $aerial],
                'about_intro' => ['image' => $entrance],
            ],
            'contact' => [
                'contact_breadcrumb' => ['background_image' => $entrance],
                'breadcrumb' => ['background_image' => $entrance],
            ],
        ];

        foreach ($map as $type => $blocksById) {
            $page = CmsPage::query()->where('type', $type)->first();

            if (! $page) {
                continue;
            }

            foreach (CmsPageTranslation::query()->where('page_id', $page->id)->get() as $translation) {
                $blocks = $translation->blocks;

                if (! is_array($blocks)) {
                    continue;
                }

                $changed = false;

                foreach ($blocks as &$block) {
                    $id = (string) ($block['id'] ?? '');
                    $updates = $blocksById[$id] ?? null;

                    if (! $updates) {
                        continue;
                    }

                    foreach ($updates as $key => $value) {
                        if (($block['data'][$key] ?? null) !== $value) {
                            $block['data'][$key] = $value;
                            $changed = true;
                        }
                    }
                }
                unset($block);

                if ($changed) {
                    $translation->blocks = $blocks;
                    $translation->save();
                }
            }
        }
    }

    protected function applyAouContactMap(): void
    {
        $embedUrl = CampusMap::embedUrl();
        $contact = CmsPage::query()->where('type', 'contact')->first();

        if (! $contact) {
            return;
        }

        foreach (CmsPageTranslation::query()->where('page_id', $contact->id)->get() as $translation) {
            $blocks = $translation->blocks;

            if (! is_array($blocks)) {
                continue;
            }

            $encoded = json_encode($blocks, JSON_UNESCAPED_UNICODE);

            if (! is_string($encoded)) {
                continue;
            }

            $replaced = str_replace(
                ['جامعة حائل', 'University of Hail', 'Univ. of Hail'],
                ['الجامعة العربية المفتوحة السعودية', 'Arab Open University Saudi Arabia', 'Arab Open University Saudi Arabia'],
                $encoded,
            );
            $blocks = json_decode($replaced, true);

            if (! is_array($blocks)) {
                continue;
            }

            foreach ($blocks as &$block) {
                if (($block['type'] ?? '') !== 'contact_map_form') {
                    continue;
                }

                $current = (string) ($block['data']['map_embed_url'] ?? '');

                if ($current !== $embedUrl) {
                    $block['data']['map_embed_url'] = $embedUrl;
                }

                $title = (string) ($block['data']['map_iframe_title'] ?? '');
                if ($title === '' || str_contains($title, 'حائل') || str_contains($title, 'Hail') || str_contains($title, 'مقرن')) {
                    $block['data']['map_iframe_title'] = '{platform_org}';
                }
            }
            unset($block);

            $translation->blocks = $blocks;
            $translation->save();
        }
    }
}
