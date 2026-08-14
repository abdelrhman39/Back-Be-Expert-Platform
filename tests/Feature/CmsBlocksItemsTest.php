<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\CmsBlockDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CmsBlocksItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_and_remove_logo_and_testimonial_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $defaults = CmsBlockDefaults::forPageType('home', 'ar');

        $logoIndex = collect($defaults)->search(fn ($b) => ($b['type'] ?? '') === 'logo_carousel');
        $testimonialIndex = collect($defaults)->search(fn ($b) => ($b['type'] ?? '') === 'testimonials');

        $this->assertNotFalse($logoIndex);
        $this->assertNotFalse($testimonialIndex);

        $beforeLogos = count($defaults[$logoIndex]['data']['logos'] ?? []);
        $beforeItems = count($defaults[$testimonialIndex]['data']['items'] ?? []);

        Livewire::actingAs($admin)
            ->test('admin.cms-page-form-page')
            ->set('blocksAr', $defaults)
            ->set('contentMode', 'blocks')
            ->call('addLogoItem', $logoIndex)
            ->call('addTestimonialItem', $testimonialIndex)
            ->assertSet('blocksAr.'.$logoIndex.'.data.logos', function ($logos) use ($beforeLogos) {
                return count($logos) === $beforeLogos + 1;
            })
            ->assertSet('blocksAr.'.$testimonialIndex.'.data.items', function ($items) use ($beforeItems) {
                return count($items) === $beforeItems + 1;
            })
            ->call('removeLogoItem', $logoIndex, $beforeLogos)
            ->assertSet('blocksAr.'.$logoIndex.'.data.logos', function ($logos) use ($beforeLogos) {
                return count($logos) === $beforeLogos;
            });
    }

    public function test_cms_image_upload_stores_public_path(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $defaults = CmsBlockDefaults::forPageType('home', 'ar');
        $logoIndex = collect($defaults)->search(fn ($b) => ($b['type'] ?? '') === 'logo_carousel');

        Livewire::actingAs($admin)
            ->test('admin.cms-page-form-page')
            ->set('blocksAr', $defaults)
            ->set('contentMode', 'blocks')
            ->call('prepareCmsImageUpload', $logoIndex, 'logos', 0, 'image')
            ->set('cmsImageFile', UploadedFile::fake()->image('partner.png', 200, 80))
            ->assertSet('blocksAr.'.$logoIndex.'.data.logos.0.image', function ($path) {
                return is_string($path) && str_starts_with($path, '/storage/media-library/cms/');
            });
    }
}
