<?php

namespace App\Services;

use App\Models\CatalogCourse;
use App\Models\InstallmentContract;
use App\Models\InstallmentPlanTemplate;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Models\User;
use App\Support\InstallmentSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentCheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly InstallmentContractService $contracts,
        private readonly InstallmentPaymentService $payments,
    ) {}

    /** @return array{contract: InstallmentContract, schedule: InstallmentSchedule, order: ?Order} */
    public function startFromCart(User $user, int $templateId, string $paymentMethod): array
    {
        if (! InstallmentSettings::checkoutEnabled()) {
            throw ValidationException::withMessages(['paymentMethod' => 'تقسيط المنصة غير مفعّل حالياً.']);
        }

        $items = $this->cart->refreshPrices();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'السلة فارغة.']);
        }

        $total = (float) $items->sum('price_snapshot');

        if ($total <= 0) {
            throw ValidationException::withMessages(['cart' => 'لا يمكن التقسيط لطلب مجاني.']);
        }

        $plans = $this->availablePlans($items);
        $template = $plans->firstWhere('id', $templateId);

        if (! $template) {
            throw ValidationException::withMessages([
                'installmentPlanId' => 'خطة التقسيط المحددة غير متاحة لعناصر السلة.',
            ]);
        }

        $student = $user->academicStudent;
        $title = $items->pluck('course_title')->filter()->take(2)->implode(' · ');

        if ($title === '') {
            $title = 'طلب تقسيط — '.$template->name_ar;
        }

        $checkoutItems = $items->map(fn ($item) => [
            'course_id' => $item->course_id,
            'training_id' => $item->training_id,
            'delivery_type' => $item->delivery_type,
            'price' => (float) $item->price_snapshot,
            'course_title' => $item->course_title,
            'course_image' => $item->course_image,
        ])->values()->all();

        return DB::transaction(function () use ($user, $template, $total, $student, $title, $paymentMethod, $checkoutItems) {
            $contract = $this->contracts->createFromTemplate(
                studentUser: $user,
                template: $template,
                totalAmount: (float) $total,
                academicStudent: $student,
                startsAt: now()->startOfDay(),
                creator: $user,
                title: $title,
                adminNotes: 'أُنشئ من صفحة الدفع',
            );

            if ($checkoutItems !== []) {
                $contract->update(['checkout_items' => $checkoutItems]);
            }

            $this->cart->clear();

            $firstSchedule = $contract->schedules()->orderBy('sequence')->first();

            if (! $firstSchedule) {
                throw ValidationException::withMessages(['template' => 'فشل إنشاء جدول الأقساط.']);
            }

            $order = null;

            if ($contract->isStudentSigned() && $firstSchedule->isPayable()) {
                $order = $this->payments->createPaymentOrder($firstSchedule, $user, $paymentMethod);
            }

            return [
                'contract' => $contract->fresh(['schedules']),
                'schedule' => $firstSchedule,
                'order' => $order,
            ];
        });
    }

    /**
     * @param  Collection<int, mixed>|null  $cartItems
     * @return Collection<int, InstallmentPlanTemplate>
     */
    public function availablePlans(?Collection $cartItems = null): Collection
    {
        $plans = InstallmentPlanTemplate::query()
            ->where('is_active', true)
            ->with(['items', 'academicPrograms', 'catalogCourses'])
            ->orderBy('name_ar')
            ->get()
            ->filter(fn (InstallmentPlanTemplate $t) => abs($t->totalPercent() - 100) < 0.05)
            ->values();

        if ($cartItems === null || $cartItems->isEmpty()) {
            return $plans;
        }

        [$courseIds, $programIds] = $this->cartScopeIds($cartItems);

        if ($courseIds->isEmpty() && $programIds->isEmpty()) {
            return $plans->filter(fn (InstallmentPlanTemplate $t) => $t->isGlobalScope())->values();
        }

        return $plans
            ->filter(fn (InstallmentPlanTemplate $t) => $t->appliesToCart($courseIds, $programIds))
            ->values();
    }

    /**
     * @param  Collection<int, mixed>  $cartItems
     * @return array{0: Collection<int, int>, 1: Collection<int, int>}
     */
    public function cartScopeIds(Collection $cartItems): array
    {
        $courseIds = $cartItems
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $programIds = CatalogCourse::query()
            ->whereIn('id', $courseIds)
            ->whereNotNull('academic_program_id')
            ->pluck('academic_program_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        return [$courseIds, $programIds];
    }
}
