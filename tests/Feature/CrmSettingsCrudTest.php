<?php

namespace Tests\Feature;

use App\Models\CrmContact;
use App\Models\CrmSource;
use App\Models\CrmStatus;
use App\Models\User;
use App\Support\AccessControl;
use App\Support\CrmOptions;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmSettingsCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->sales = User::factory()->create(['role' => 'sales', 'status' => 'active']);
        $this->seed(AccessControlSeeder::class);
        AccessControl::forget();
        CrmOptions::forgetCache();
    }

    public function test_admin_can_create_edit_and_delete_custom_status_and_source(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('startCreateStatus')
            ->set('statusName', 'بانتظار الدفع')
            ->set('statusKey', 'awaiting_payment')
            ->set('statusColor', '#112233')
            ->set('statusSort', 55)
            ->set('statusClosed', false)
            ->call('saveStatus')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('crm_statuses', [
            'key' => 'awaiting_payment',
            'name_ar' => 'بانتظار الدفع',
            'is_active' => true,
        ]);

        $status = CrmStatus::query()->where('key', 'awaiting_payment')->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('startEditStatus', $status->id)
            ->set('statusName', 'بانتظار السداد')
            ->call('saveStatus')
            ->assertHasNoErrors();

        $this->assertSame('بانتظار السداد', $status->refresh()->name_ar);

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('startCreateSource')
            ->set('sourceName', 'إعلانات سناب')
            ->set('sourceKey', 'snapchat_ads')
            ->call('saveSource')
            ->assertHasNoErrors();

        $source = CrmSource::query()->where('key', 'snapchat_ads')->firstOrFail();
        $this->assertSame('إعلانات سناب', $source->name_ar);

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('deleteSource', $source->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('crm_sources', ['id' => $source->id]);

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('deleteStatus', $status->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('crm_statuses', ['id' => $status->id]);
    }

    public function test_system_status_cannot_be_deleted_and_used_status_is_blocked(): void
    {
        $system = CrmStatus::query()->where('key', 'won')->firstOrFail();

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('deleteStatus', $system->id);

        $this->assertDatabaseHas('crm_statuses', ['id' => $system->id]);

        $custom = CrmStatus::query()->create([
            'key' => 'custom_stage',
            'name_ar' => 'مرحلة مخصصة',
            'color' => '#123456',
            'sort_order' => 200,
            'is_active' => true,
        ]);
        CrmContact::query()->create([
            'name' => 'عميل مرتبط',
            'phone' => '+966500000099',
            'status' => 'custom_stage',
            'priority' => 'medium',
            'source' => 'manual',
        ]);

        Livewire::actingAs($this->admin)
            ->test('admin.crm-settings-page')
            ->call('deleteStatus', $custom->id);

        $this->assertDatabaseHas('crm_statuses', ['id' => $custom->id]);
    }

    public function test_sales_cannot_open_settings_and_dynamic_statuses_appear_in_crm(): void
    {
        CrmStatus::query()->create([
            'key' => 'waiting_docs',
            'name_ar' => 'بانتظار المستندات',
            'color' => '#334455',
            'sort_order' => 35,
            'is_active' => true,
        ]);
        CrmOptions::forgetCache();

        $this->actingAs($this->sales)->get(route('admin.crm.settings'))->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.crm'))
            ->assertOk()
            ->assertSee('بانتظار المستندات');
    }
}
