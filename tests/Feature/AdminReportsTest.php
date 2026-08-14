<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\Reports\AdminReportService;
use App\Support\Reports\ReportFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_reports_hub(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('مركز التقارير')
            ->assertSee('نظرة عامة');
    }

    public function test_user_without_reports_permission_is_forbidden(): void
    {
        $sales = User::factory()->create(['role' => 'sales', 'status' => 'active']);

        $this->actingAs($sales)
            ->get(route('admin.reports'))
            ->assertForbidden();
    }

    public function test_report_service_builds_overview_and_finance_areas(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Order::query()->create([
            'user_id' => $admin->id,
            'reference' => 'ORD-REP-1',
            'total' => 1500,
            'currency' => 'SAR',
            'status' => 'paid',
            'payment_method' => 'moyasar',
            'paid_at' => now()->subDay(),
        ]);

        $filter = ReportFilter::fromInputs('30d');
        $service = app(AdminReportService::class);

        $overview = $service->build('overview', $filter);
        $finance = $service->build('finance', $filter);

        $this->assertNotEmpty($overview['kpis']);
        $this->assertNotEmpty($finance['kpis']);
        $this->assertGreaterThanOrEqual(1, (int) collect($finance['kpis'])->firstWhere('label', 'طلبات مدفوعة')['value']);
    }

    public function test_livewire_can_switch_area_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test('admin.reports-page')
            ->assertSee('مركز التقارير')
            ->call('setArea', 'finance')
            ->assertSet('area', 'finance')
            ->call('setPreset', '7d')
            ->assertSet('preset', '7d')
            ->call('export')
            ->assertFileDownloaded();
    }

    public function test_area_visibility_respects_domain_permissions(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
        $areas = collect(app(AdminReportService::class)->areasFor($staff))->pluck('id');

        $this->assertTrue($areas->contains('overview'));
        $this->assertTrue($areas->contains('students'));
        $this->assertFalse($areas->contains('finance'));
        $this->assertFalse($areas->contains('installments'));
    }
}
