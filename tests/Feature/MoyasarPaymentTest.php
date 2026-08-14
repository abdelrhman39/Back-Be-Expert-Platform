<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\InstallmentPaymentService;
use App\Services\MoyasarService;
use App\Services\OrderPaymentService;
use App\Support\MoyasarSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class MoyasarPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_moyasar_verification_requires_exact_order_metadata(): void
    {
        config([
            'moyasar.secret_key' => 'sk_test_example',
            'moyasar.currency' => 'SAR',
        ]);

        $order = new Order([
            'reference' => 'BX-TEST-001',
            'total' => 125.50,
            'currency' => 'SAR',
        ]);
        $order->id = 41;

        Http::fake([
            'api.moyasar.com/v1/payments/payment_ok' => Http::response([
                'id' => 'payment_ok',
                'status' => 'paid',
                'amount' => 12550,
                'currency' => 'SAR',
                'metadata' => [
                    'order_id' => 41,
                    'order_reference' => 'BX-TEST-001',
                ],
            ]),
            'api.moyasar.com/v1/payments/payment_missing_metadata' => Http::response([
                'id' => 'payment_missing_metadata',
                'status' => 'paid',
                'amount' => 12550,
                'currency' => 'SAR',
                'metadata' => [],
            ]),
        ]);

        $service = app(MoyasarService::class);

        $this->assertTrue($service->verifyPaymentForOrder('payment_ok', $order));
        $this->assertFalse($service->verifyPaymentForOrder('payment_missing_metadata', $order));
    }

    public function test_moyasar_secrets_are_encrypted_at_rest(): void
    {
        MoyasarSettings::setSecretKey('sk_test_secret');
        MoyasarSettings::setWebhookSecret('webhook-secret');

        $storedSecret = PaymentSetting::query()
            ->where('key', MoyasarSettings::SECRET_KEY)
            ->value('value');

        $this->assertNotSame('sk_test_secret', $storedSecret);
        $this->assertStringStartsWith('encrypted:', $storedSecret);
        $this->assertSame('sk_test_secret', MoyasarSettings::secretKey());
        $this->assertSame('webhook-secret', MoyasarSettings::webhookSecret());
    }

    public function test_marking_a_paid_order_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'reference' => 'BX-IDEMPOTENT',
            'total' => 100,
            'currency' => 'SAR',
            'status' => 'pending_payment',
            'payment_method' => 'mada',
        ]);

        $enrollments = Mockery::mock(EnrollmentService::class);
        $enrollments->shouldReceive('syncFromOrder')->once();
        $service = new OrderPaymentService($enrollments);

        $service->markAsPaid($order, 'moyasar', 'payment_unique');
        $service->markAsPaid($order->fresh(), 'moyasar', 'payment_unique');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'gateway_payment_id' => 'payment_unique',
        ]);
    }

    public function test_installment_processing_runs_after_the_order_is_marked_paid(): void
    {
        $user = User::factory()->create();
        $contractId = DB::table('installment_contracts')->insertGetId([
            'contract_no' => 'IC-TEST-001',
            'user_id' => $user->id,
            'title' => 'Test contract',
            'total_amount' => 100,
            'remaining_balance' => 100,
            'currency' => 'SAR',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $scheduleId = DB::table('installment_schedules')->insertGetId([
            'contract_id' => $contractId,
            'sequence' => 1,
            'amount' => 100,
            'due_date' => today(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'reference' => 'IX-TEST-001',
            'total' => 100,
            'currency' => 'SAR',
            'status' => 'pending_payment',
            'payment_method' => 'mada',
            'installment_schedule_id' => $scheduleId,
        ]);

        $installments = Mockery::mock(InstallmentPaymentService::class);
        $installments->shouldReceive('processPaidOrder')
            ->once()
            ->withArgs(fn (Order $processedOrder, string $gateway, string $paymentId) => $processedOrder->status === 'paid'
                && $gateway === 'moyasar'
                && $paymentId === 'payment_installment');
        $this->app->instance(InstallmentPaymentService::class, $installments);

        $enrollments = Mockery::mock(EnrollmentService::class);
        $enrollments->shouldNotReceive('syncFromOrder');

        (new OrderPaymentService($enrollments))
            ->markAsPaid($order, 'moyasar', 'payment_installment');
    }
}
