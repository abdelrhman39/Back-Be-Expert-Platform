<?php

namespace Tests\Feature;

use App\Support\CmsBlockDefaults;
use Database\Seeders\CmsPageBlocksSeeder;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_defaults_include_intro_channels_and_form(): void
    {
        $types = collect(CmsBlockDefaults::contact('ar'))->pluck('type')->all();

        $this->assertContains('breadcrumb', $types);
        $this->assertContains('contact_intro', $types);
        $this->assertContains('contact_channels', $types);
        $this->assertContains('contact_map_form', $types);

        $breadcrumb = collect(CmsBlockDefaults::contact('ar'))->firstWhere('id', 'contact_breadcrumb');
        $this->assertSame(platform_campus_path('entrance'), $breadcrumb['data']['background_image'] ?? null);

        $mapForm = collect(CmsBlockDefaults::contact('ar'))->firstWhere('type', 'contact_map_form');
        $mapUrl = (string) ($mapForm['data']['map_embed_url'] ?? '');
        $this->assertStringContainsString('output=embed', $mapUrl);
        $this->assertStringNotContainsString('41.699758', $mapUrl);
        $this->assertStringNotContainsString('157645de57c7ca57', $mapUrl);
    }

    public function test_published_contact_page_renders_redesigned_layout(): void
    {
        $this->seed([CmsSeeder::class, CmsPageBlocksSeeder::class]);

        $response = $this->get(route('contact', ['locale' => 'ar']));

        $response->assertOk();
        $response->assertSee('atelier-contact', false);
        $response->assertSee('نرحب بتواصلكم');
        $response->assertSee('نحن هنا لمساعدتكم');
        $response->assertSee('فتح تذكرة دعم');
        $response->assertSee('contact-channel', false);
        $response->assertSee('contact-stage', false);
        $response->assertSee('ابقَ على تواصل');
        $response->assertSee('id="contact-form"', false);
        $response->assertSee('contact-input__icon', false);
        $response->assertSee('الاسم الكامل');
        $response->assertSee('اكتب رسالتك هنا');
        $response->assertSee('الجامعة العربية المفتوحة');
        $response->assertSee('aou-campus-entrance.jpg', false);
        $response->assertSee('output=embed', false);
        $response->assertDontSee('41.699758');
        $response->assertDontSee('{platform_org}');
        $response->assertDontSee('برنامج مهارات');
    }
}
