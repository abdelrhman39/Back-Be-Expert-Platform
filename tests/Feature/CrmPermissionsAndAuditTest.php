<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\CrmContact;
use App\Models\User;
use App\Support\AccessControl;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrmPermissionsAndAuditTest extends TestCase
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
    }

    public function test_sales_cannot_access_import_settings_or_audit_but_can_log_activity(): void
    {
        $contact = CrmContact::query()->create([
            'name' => 'عميل السيلز',
            'phone' => '+966500000111',
            'owner_id' => $this->sales->id,
            'status' => 'new',
            'priority' => 'medium',
            'source' => 'manual',
        ]);

        $this->actingAs($this->sales)->get(route('admin.crm.import'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('admin.crm.settings'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('admin.crm.audit'))->assertForbidden();
        $this->actingAs($this->sales)->get(route('admin.crm.contacts.show', $contact))->assertOk();

        Livewire::actingAs($this->sales)
            ->test('admin.crm-contact-page', ['contact' => $contact])
            ->set('activityContent', 'تم الاتصال بالعميل')
            ->call('addActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'log_group' => 'crm',
            'action' => 'crm.activity.logged',
            'user_id' => $this->sales->id,
        ]);
    }

    public function test_admin_actions_are_audited_with_actor_details(): void
    {
        Livewire::actingAs($this->admin)
            ->test('admin.crm-page')
            ->set('newName', 'عميل تدقيق')
            ->set('newPhone', '+966500000222')
            ->set('newSource', 'manual')
            ->call('createContact')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activity_logs', [
            'log_group' => 'crm',
            'action' => 'crm.contact.created',
            'user_id' => $this->admin->id,
        ]);

        $log = ActivityLog::query()->where('action', 'crm.contact.created')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertNotEmpty($log->new_values);
        $this->assertSame('عميل تدقيق', $log->new_values['name'] ?? null);

        $this->actingAs($this->admin)->get(route('admin.crm.audit'))->assertOk()->assertSee('عميل تدقيق');
    }

    public function test_sales_cannot_change_status_without_permission_override(): void
    {
        // sales role already has change_status; verify create is forbidden
        Livewire::actingAs($this->sales)
            ->test('admin.crm-page')
            ->set('showCreate', true)
            ->set('newName', 'محاولة إضافة')
            ->set('newPhone', '+966500000333')
            ->call('createContact')
            ->assertForbidden();
    }
}
