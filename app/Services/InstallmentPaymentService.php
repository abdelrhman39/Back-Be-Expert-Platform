<?php

namespace App\Services;

use App\Models\AcademicStudent;
use App\Models\InstallmentPayment;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Models\User;
use App\Support\AcademicStudentOptions;
use App\Support\NotificationTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InstallmentPaymentService
{
    public function __construct(
        private readonly InstallmentContractService $contracts,
        private readonly AuditLogService $audit,
    ) {}

    public function studentCanPay(User $user, InstallmentSchedule $schedule): bool
    {
        $schedule->loadMissing('contract');

        $contract = $schedule->contract;

        return $contract?->user_id === $user->id
            && $schedule->isPayable()
            && $contract->isStudentSigned()
            && in_array($contract->status, ['active', 'suspended'], true);
    }

    public function createPaymentOrder(InstallmentSchedule $schedule, User $user, string $paymentMethod = 'mada'): Order
    {
        if (! $this->studentCanPay($user, $schedule)) {
            throw ValidationException::withMessages(['schedule' => 'لا يمكن سداد هذا القسط حالياً.']);
        }

        return DB::transaction(function () use ($schedule, $user, $paymentMethod) {
            $lockedSchedule = InstallmentSchedule::query()->lockForUpdate()->findOrFail($schedule->id);

            if (! $this->studentCanPay($user, $lockedSchedule)) {
                throw ValidationException::withMessages(['schedule' => 'لا يمكن سداد هذا القسط حالياً.']);
            }

            $existing = Order::query()
                ->where('installment_schedule_id', $lockedSchedule->id)
                ->where('user_id', $user->id)
                ->where('status', 'pending_payment')
                ->first();

            if ($existing) {
                if ($existing->payment_method !== $paymentMethod) {
                    $existing->update(['payment_method' => $paymentMethod]);
                }

                return $existing->fresh();
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'reference' => $this->generateOrderReference($lockedSchedule),
                'total' => $lockedSchedule->totalDue(),
                'currency' => $lockedSchedule->contract?->currency ?? 'SAR',
                'status' => 'pending_payment',
                'payment_method' => $paymentMethod,
                'installment_schedule_id' => $lockedSchedule->id,
            ]);

            $this->attachCheckoutItems($order, $lockedSchedule);

            return $order;
        });
    }

    protected function attachCheckoutItems(Order $order, InstallmentSchedule $schedule): void
    {
        // Enrollment should activate after the first installment only.
        if ((int) $schedule->sequence !== 1) {
            return;
        }

        $schedule->loadMissing('contract');
        $items = $schedule->contract?->checkout_items;

        if (! is_array($items) || $items === []) {
            return;
        }

        if ($order->items()->exists()) {
            return;
        }

        foreach ($items as $item) {
            $order->items()->create([
                'course_id' => $item['course_id'] ?? null,
                'training_id' => $item['training_id'] ?? null,
                'delivery_type' => $item['delivery_type'] ?? 'online',
                'price' => $item['price'] ?? 0,
                'course_title' => $item['course_title'] ?? null,
                'course_image' => $item['course_image'] ?? null,
            ]);
        }
    }

    public function markSchedulePaid(
        InstallmentSchedule $schedule,
        float $amount,
        string $gateway,
        ?string $gatewayRef,
        ?Order $order = null,
        ?User $recorder = null,
    ): InstallmentSchedule {
        return DB::transaction(function () use ($schedule, $amount, $gateway, $gatewayRef, $order, $recorder) {
            $lockedSchedule = InstallmentSchedule::query()
                ->with('contract')
                ->lockForUpdate()
                ->findOrFail($schedule->id);

            if ($lockedSchedule->status === 'paid') {
                return $lockedSchedule;
            }

            if ($gatewayRef && InstallmentPayment::query()
                ->where('gateway', $gateway)
                ->where('gateway_ref', $gatewayRef)
                ->where('schedule_id', '!=', $lockedSchedule->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'مرجع الدفع مستخدم مسبقاً لقسط آخر.',
                ]);
            }

            $lockedSchedule->update([
                'status' => 'paid',
                'paid_at' => now(),
                'order_id' => $order?->id ?? $lockedSchedule->order_id,
            ]);

            $paymentAttributes = [
                'schedule_id' => $lockedSchedule->id,
                'order_id' => $order?->id,
                'gateway' => $gateway,
                'gateway_ref' => $gatewayRef,
                'amount' => $amount,
                'status' => 'success',
                'paid_at' => now(),
                'recorded_by' => $recorder?->id,
            ];

            if ($order) {
                InstallmentPayment::query()->updateOrCreate(
                    ['order_id' => $order->id],
                    $paymentAttributes,
                );
            } else {
                InstallmentPayment::query()->create($paymentAttributes);
            }

            if ($order && $order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'gateway' => $gateway,
                    'gateway_payment_id' => $gatewayRef,
                    'payment_ref' => $gatewayRef,
                    'paid_at' => now(),
                ]);
            }

            $contract = $this->contracts->refreshContractBalances($lockedSchedule->contract);
            app(InstallmentOverdueService::class)->restoreClearedContracts();
            app(InstallmentDunningService::class)->handleSchedulePaid($lockedSchedule->fresh(['contract.user', 'contract.student', 'contract.schedules']));

            if ($order && $order->items()->exists()) {
                app(EnrollmentService::class)->syncFromOrder($order->fresh(['items']));
            }

            $this->activateAcademicStudentIfNeeded($contract, $lockedSchedule);

            if ($lockedSchedule->sequence === 1) {
                app(AcademicEnrollmentLifecycleService::class)
                    ->syncCrmPaidFromInstallment($contract->fresh(), $lockedSchedule->fresh(), $recorder);
            }

            $this->audit->log(
                action: 'installment_schedule.paid',
                descriptionAr: 'سداد قسط #'.$lockedSchedule->sequence.' — '.$contract->contract_no,
                group: 'finance',
                actor: $recorder ?? $contract->user,
                subject: $lockedSchedule,
                subjectLabel: $contract->contract_no,
                newValues: ['amount' => $amount, 'gateway' => $gateway],
            );

            $this->notifyPaid($lockedSchedule->fresh(), $contract->fresh());

            return $lockedSchedule->fresh();
        });
    }

    public function recordManualPayment(
        InstallmentSchedule $schedule,
        User $admin,
        ?string $notes = null,
        ?string $gatewayRef = null,
    ): InstallmentSchedule {
        if (! $schedule->isPayable()) {
            throw ValidationException::withMessages(['schedule' => 'القسط غير قابل للسداد.']);
        }

        if ($notes) {
            $schedule->update(['admin_notes' => $notes]);
        }

        return $this->markSchedulePaid(
            schedule: $schedule,
            amount: $schedule->totalDue(),
            gateway: 'manual',
            gatewayRef: $gatewayRef ?: 'MANUAL-'.Str::upper(Str::random(12)),
            order: null,
            recorder: $admin,
        );
    }

    public function cancelPendingPaymentOrder(InstallmentSchedule $schedule, User $admin): int
    {
        $cancelled = Order::query()
            ->where('installment_schedule_id', $schedule->id)
            ->where('status', 'pending_payment')
            ->update(['status' => 'cancelled']);

        if ($cancelled > 0) {
            $this->audit->log(
                action: 'installment_payment_link.cancelled',
                descriptionAr: 'إلغاء رابط سداد القسط #'.$schedule->sequence,
                group: 'finance',
                actor: $admin,
                subject: $schedule,
                subjectLabel: $schedule->contract?->contract_no,
            );
        }

        return $cancelled;
    }

    public function processPaidOrder(Order $order, string $gateway, string $gatewayPaymentId): void
    {
        if (! $order->installment_schedule_id) {
            return;
        }

        $schedule = InstallmentSchedule::query()->find($order->installment_schedule_id);

        if (! $schedule) {
            return;
        }

        $this->markSchedulePaid(
            schedule: $schedule,
            amount: (float) $order->total,
            gateway: $gateway,
            gatewayRef: $gatewayPaymentId,
            order: $order,
            recorder: $order->user,
        );
    }

    protected function notifyPaid(InstallmentSchedule $schedule, $contract): void
    {
        $user = $contract->user;

        if (! $user) {
            return;
        }

        $locale = $user->locale ?: 'ar';

        app(NotificationService::class)->send(
            user: $user,
            type: NotificationTypes::INSTALLMENT_PAID,
            title: 'تم استلام قسطك — '.$schedule->label,
            body: 'تم تسجيل سداد بمبلغ '.number_format((float) $schedule->amount, 2).' ر.س. المتبقي: '.number_format((float) $contract->remaining_balance, 2).' ر.س.',
            actionUrl: route('installments.show', ['locale' => $locale, 'contract' => $contract->id]),
            icon: 'fa-credit-card',
            subject: $schedule,
        );

        if ($contract->status === 'completed') {
            app(NotificationService::class)->send(
                user: $user,
                type: NotificationTypes::INSTALLMENT_COMPLETED,
                title: 'اكتمل سداد عقد التقسيط',
                body: 'تم سداد جميع أقساط «'.$contract->title.'» بنجاح.',
                actionUrl: route('installments.show', ['locale' => $locale, 'contract' => $contract->id]),
                icon: 'fa-circle-check',
                subject: $contract,
            );
        }
    }

    protected function activateAcademicStudentIfNeeded($contract, InstallmentSchedule $schedule): void
    {
        if ($schedule->sequence !== 1 || ! $contract->academic_student_id) {
            return;
        }

        $student = AcademicStudent::query()->find($contract->academic_student_id);

        if ($student && $student->academic_status === 'pending') {
            $student->update([
                'academic_status' => 'studying',
                'study_status' => AcademicStudentOptions::academicStatusLabel('studying'),
            ]);
        }
    }

    protected function generateOrderReference(InstallmentSchedule $schedule): string
    {
        do {
            $reference = 'IX-'.$schedule->contract_id.'-'.$schedule->sequence.'-'.Str::upper(Str::random(4));
        } while (Order::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
