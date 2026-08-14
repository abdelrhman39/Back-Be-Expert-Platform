<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_media_library_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.media-library'))
            ->assertOk()
            ->assertSee('مكتبة الوسائط', false);
    }

    public function test_admin_can_create_folder_upload_and_share_asset(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $service = app(MediaLibraryService::class);

        $folder = $service->createFolder('الصفحة الرئيسية', null, $admin);
        $this->assertDatabaseHas('media_folders', ['id' => $folder->id, 'name' => 'الصفحة الرئيسية']);

        $file = UploadedFile::fake()->image('banner.jpg', 640, 480);
        $asset = $service->upload($file, $folder->id, $admin);

        $this->assertInstanceOf(MediaAsset::class, $asset);
        Storage::disk('public')->assertExists($asset->path);

        $shared = $service->enablePublicLink($asset);
        $this->assertTrue($shared->public_enabled);
        $this->assertNotEmpty($shared->public_token);

        $this->get(route('media.public', ['token' => $shared->public_token]))
            ->assertOk();

        Livewire::actingAs($admin)
            ->test('admin.media-library-page')
            ->set('folderName', 'مستندات')
            ->call('createFolder')
            ->assertSet('message', 'تم إنشاء المجلد.');

        $this->assertDatabaseHas('media_folders', ['name' => 'مستندات']);
    }

    public function test_guest_cannot_access_media_library(): void
    {
        $this->get(route('admin.media-library'))->assertRedirect();
    }

    public function test_deleting_folder_removes_nested_assets(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $service = app(MediaLibraryService::class);

        $folder = $service->createFolder('مجلد', null, $admin);
        $asset = $service->upload(UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'), $folder->id, $admin);
        $path = $asset->path;

        $service->deleteFolder($folder);

        $this->assertDatabaseMissing('media_folders', ['id' => $folder->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
