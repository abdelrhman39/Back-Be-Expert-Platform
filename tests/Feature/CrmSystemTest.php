<?php

namespace Tests\Feature;

use App\Models\AcademicProgram;
use App\Models\CrmAssignmentRule;
use App\Models\CrmContact;
use App\Models\User;
use App\Services\CrmAssignmentService;
use App\Services\CrmImportService;
use App\Support\AccessControl;
use Database\Seeders\AccessControlSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class CrmSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $salesOne;

    private User $salesTwo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->salesOne = User::factory()->create(['role' => 'sales', 'status' => 'active']);
        $this->salesTwo = User::factory()->create(['role' => 'sales', 'status' => 'active']);
        $this->seed(AccessControlSeeder::class);
        AccessControl::forget();
    }

    public function test_sales_user_only_sees_assigned_contacts(): void
    {
        $mine = CrmContact::query()->create([
            'name' => 'عميل موظف أول',
            'phone' => '+966500000001',
            'owner_id' => $this->salesOne->id,
            'status' => 'new',
            'priority' => 'medium',
        ]);
        CrmContact::query()->create([
            'name' => 'عميل موظف آخر',
            'phone' => '+966500000002',
            'owner_id' => $this->salesTwo->id,
            'status' => 'new',
            'priority' => 'medium',
        ]);

        $this->actingAs($this->salesOne)
            ->get(route('admin.crm'))
            ->assertOk()
            ->assertSee($mine->name)
            ->assertDontSee('عميل موظف آخر');

        $this->actingAs($this->salesOne)
            ->get(route('admin.crm.contacts.show', $mine))
            ->assertOk();

        $other = CrmContact::query()->where('owner_id', $this->salesTwo->id)->firstOrFail();
        $this->actingAs($this->salesOne)
            ->get(route('admin.crm.contacts.show', $other))
            ->assertForbidden();
    }

    public function test_program_rule_takes_precedence_over_global_rule(): void
    {
        $program = AcademicProgram::query()->create([
            'name_ar' => 'برنامج إدارة الأعمال',
            'name_en' => 'Business',
            'code' => 'BUS',
            'status' => 'active',
            'type' => 'diploma',
        ]);
        CrmAssignmentRule::query()->create([
            'program_id' => null,
            'sales_user_id' => $this->salesOne->id,
            'priority' => 1,
            'created_by' => $this->admin->id,
        ]);
        CrmAssignmentRule::query()->create([
            'program_id' => $program->id,
            'sales_user_id' => $this->salesTwo->id,
            'priority' => 100,
            'created_by' => $this->admin->id,
        ]);
        $contact = CrmContact::query()->create([
            'program_id' => $program->id,
            'name' => 'عميل البرنامج',
            'phone' => '+966500000003',
            'status' => 'new',
            'priority' => 'medium',
        ]);

        app(CrmAssignmentService::class)->autoAssign($contact, $this->admin);

        $this->assertSame($this->salesTwo->id, $contact->refresh()->owner_id);
        $this->assertDatabaseHas('crm_activities', [
            'contact_id' => $contact->id,
            'type' => 'assignment',
        ]);
    }

    public function test_csv_import_deduplicates_and_auto_assigns_contacts(): void
    {
        CrmAssignmentRule::query()->create([
            'program_id' => null,
            'sales_user_id' => $this->salesOne->id,
            'priority' => 1,
            'created_by' => $this->admin->id,
        ]);
        CrmContact::query()->create([
            'name' => 'الاسم القديم',
            'email' => 'same@example.com',
            'status' => 'new',
            'priority' => 'medium',
        ]);
        $file = UploadedFile::fake()->createWithContent(
            'leads.csv',
            "name,email,phone,priority,notes\nالاسم المحدث,same@example.com,+966500000010,high,محدث\nعميل جديد,new@example.com,+966500000011,urgent,جديد\n"
        );

        $import = app(CrmImportService::class)->import($file, $this->admin, ['auto_assign' => true]);

        $this->assertSame('completed', $import->status);
        $this->assertSame(1, $import->created_rows);
        $this->assertSame(1, $import->updated_rows);
        $this->assertSame(2, CrmContact::query()->count());
        $this->assertDatabaseHas('crm_contacts', [
            'email' => 'same@example.com',
            'name' => 'الاسم المحدث',
            'priority' => 'high',
            'owner_id' => $this->salesOne->id,
        ]);
    }

    public function test_sales_can_log_activity_but_cannot_open_import_or_rules(): void
    {
        $contact = CrmContact::query()->create([
            'name' => 'عميل للمتابعة',
            'phone' => '+966500000020',
            'owner_id' => $this->salesOne->id,
            'status' => 'new',
            'priority' => 'medium',
        ]);

        Livewire::actingAs($this->salesOne)
            ->test('admin.crm-contact-page', ['contact' => $contact])
            ->set('activityType', 'call')
            ->set('activityOutcome', 'answered')
            ->set('activityContent', 'تم التواصل والعميل مهتم.')
            ->set('activityNextFollowUp', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('addActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('crm_activities', [
            'contact_id' => $contact->id,
            'user_id' => $this->salesOne->id,
            'type' => 'call',
        ]);
        $this->assertSame('contacted', $contact->refresh()->status);

        $this->actingAs($this->salesOne)->get(route('admin.crm.import'))->assertForbidden();
        $this->actingAs($this->salesOne)->get(route('admin.crm.rules'))->assertForbidden();
        $this->actingAs($this->salesOne)->get(route('admin.crm.settings'))->assertForbidden();
        $this->actingAs($this->salesOne)->get(route('admin.crm.audit'))->assertForbidden();
    }
}
