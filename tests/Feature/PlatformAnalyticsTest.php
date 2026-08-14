<?php

namespace Tests\Feature;

use App\Models\PlatformAnalyticsEvent;
use App\Models\User;
use App\Services\PlatformAnalyticsRecorder;
use App\Services\PlatformAnalyticsService;
use App\Services\AuthService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_html_views_are_tracked_privately_with_location_and_visit_identity(): void
    {
        config(['analytics.geo_provider' => 'vercel']);

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) AppleWebKit Safari/605.1',
            'X-Vercel-IP-Country' => 'SA',
            'X-Vercel-IP-Country-Region' => '01',
            'X-Vercel-IP-City' => 'Riyadh',
        ];

        $this->withHeaders($headers)->get(route('login', ['locale' => 'ar']))->assertOk();
        $this->withHeaders($headers)->get(route('register', ['locale' => 'ar']))->assertOk();

        $events = PlatformAnalyticsEvent::query()->where('event_type', 'page_view')->get();

        $this->assertCount(2, $events);
        $this->assertSame(1, $events->pluck('visit_id')->unique()->count());
        $this->assertSame('SA', $events->first()->country_code);
        $this->assertSame('01', $events->first()->region);
        $this->assertSame('Riyadh', $events->first()->city);
        $this->assertSame('mobile', $events->first()->device_type);
        $this->assertNotEmpty($events->first()->visitor_hash);
        $this->assertFalse(Schema::hasColumn('platform_analytics_events', 'ip_address'));
    }

    public function test_bots_and_admin_page_views_are_not_counted(): void
    {
        $this->withHeader('User-Agent', 'Googlebot/2.1')
            ->get(route('login', ['locale' => 'ar']))
            ->assertOk();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertDatabaseCount('platform_analytics_events', 0);
    }

    public function test_dashboard_aggregates_traffic_logins_registrations_and_locations(): void
    {
        config(['analytics.geo_provider' => 'vercel']);

        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'created_at' => now(),
        ]);
        $request = Request::create('/ar/login', 'POST', server: [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 Windows Chrome/120.0',
            'HTTP_X_VERCEL_IP_COUNTRY' => 'SA',
            'HTTP_X_VERCEL_IP_CITY' => 'Riyadh',
            'REMOTE_ADDR' => '203.0.113.10',
        ]);

        app(PlatformAnalyticsRecorder::class)->recordLogin($student, $request, 'portal');
        PlatformAnalyticsEvent::query()->create([
            'event_type' => 'page_view',
            'visit_id' => 'visit-test',
            'visitor_hash' => hash('sha256', 'visitor-test'),
            'path' => '/ar',
            'country_code' => 'SA',
            'country_name' => 'SA',
            'region' => 'الرياض',
            'city' => 'Riyadh',
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'operating_system' => 'Windows',
            'occurred_on' => today(),
            'occurred_at' => now(),
        ]);

        $analytics = app(PlatformAnalyticsService::class)->dashboard(30);

        $this->assertSame(1, $analytics['kpis']['page_views']['value']);
        $this->assertSame(1, $analytics['kpis']['visits']['value']);
        $this->assertSame(1, $analytics['kpis']['logins']['value']);
        $this->assertSame(1, $analytics['kpis']['registrations']['value']);
        $this->assertSame('SA', $analytics['countries'][0]['code']);
        $this->assertSame('Riyadh', $analytics['cities'][0]['label']);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)
            ->get(route('admin.platform-analytics'))
            ->assertOk()
            ->assertSee('مؤشرات المنصة')
            ->assertSee('مشاهدات الصفحات')
            ->assertSee('الدول الأكثر زيارة');

        $this->actingAs($admin)
            ->get(route('admin.system-settings.section', ['section' => 'analytics']))
            ->assertOk()
            ->assertSee('مزوّد بيانات الموقع الجغرافي')
            ->assertSee('مدة الاحتفاظ بالأحداث الخام');
    }

    public function test_portal_authentication_and_registration_record_exact_events(): void
    {
        $existing = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email' => 'analytics-login@example.test',
            'password' => 'password123',
        ]);

        $this->get(route('login', ['locale' => 'ar']))->assertOk();
        PlatformAnalyticsEvent::query()->delete();

        app(AuthService::class)->attempt('email', [
            'email' => $existing->email,
            'password' => 'password123',
        ], false);

        $this->assertSame(
            1,
            PlatformAnalyticsEvent::query()->where('event_type', 'login')->count(),
        );

        app(RegistrationService::class)->register([
            'name_ar' => 'طالب مؤشرات جديد',
            'national_id' => '1234567890',
            'phone' => '0501234567',
            'email' => 'analytics-registration@example.test',
            'password' => 'password123',
        ]);

        $this->assertSame(
            1,
            PlatformAnalyticsEvent::query()->where('event_type', 'registration')->count(),
        );

        Auth::guard('portal')->logout();
    }
}
