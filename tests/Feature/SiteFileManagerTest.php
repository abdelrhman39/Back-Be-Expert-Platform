<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SiteFileManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteFileManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_mode_requires_log_truee(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.media-library', ['log' => 'truee']))
            ->assertOk()
            ->assertSee('مجلد جديد', false);
    }

    public function test_service_can_create_write_and_delete_within_root(): void
    {
        $service = app(SiteFileManagerService::class);
        $dir = 'sfm-test-'.bin2hex(random_bytes(3));
        $service->createDirectory(null, $dir);
        $path = $service->createFile($dir, 'note.txt', 'hello');
        $this->assertSame('hello', $service->read($path));
        $service->write($path, 'updated');
        $this->assertSame('updated', $service->read($path));
        $service->delete($dir);
        $this->assertFalse(is_dir($service->root().'/'.$dir));
    }

    public function test_guest_cannot_open_server_mode(): void
    {
        $this->get(route('admin.media-library', ['log' => 'truee']))->assertRedirect();
    }
}
