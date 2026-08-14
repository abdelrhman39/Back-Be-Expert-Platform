<?php

use App\Models\InstallmentPlanTemplate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('خطط التقسيط | لوحة التحكم')]
class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.view'), 403);
    }

    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $plan = InstallmentPlanTemplate::query()->findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-plans'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'خطط التقسيط'],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--split">
        <div>
            <h2>قوالب خطط التقسيط</h2>
            <p class="admin-crud-card__meta">عرّف نسب الأقساط واربطها بالبرامج/الدبلومات — يمكن ربط أكثر من خطة لنفس البرنامج.</p>
        </div>
        @canAdmin('installments.manage')
            <a href="{{ route('admin.installment-plans.create') }}" class="admin-btn-primary admin-btn-primary--sm">خطة جديدة</a>
        @endcanAdmin
    </div>

    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>الخطة</th>
                    <th>الربط</th>
                    <th>الأقساط</th>
                    <th>النسبة الكلية</th>
                    <th>دفعة أولى min</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse (InstallmentPlanTemplate::query()->withCount(['items', 'academicPrograms', 'catalogCourses'])->orderBy('name_ar')->get() as $plan)
                    <tr wire:key="plan-{{ $plan->id }}">
                        <td>
                            <strong>{{ $plan->name_ar }}</strong>
                            <div class="admin-crud-card__meta">{{ $plan->slug }}</div>
                        </td>
                        <td>
                            @if ($plan->academic_programs_count === 0 && $plan->catalog_courses_count === 0)
                                <span class="admin-badge admin-badge--muted">عامة</span>
                            @else
                                <div class="admin-crud-card__meta">
                                    @if ($plan->academic_programs_count)
                                        {{ $plan->academic_programs_count }} برنامج
                                    @endif
                                    @if ($plan->catalog_courses_count)
                                        {{ $plan->academic_programs_count ? ' · ' : '' }}{{ $plan->catalog_courses_count }} دبلوم/دورة
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>{{ $plan->items_count }}</td>
                        <td>{{ number_format($plan->totalPercent(), 1) }}%</td>
                        <td>{{ number_format($plan->min_down_payment_percent, 0) }}%</td>
                        <td><span class="admin-badge {{ $plan->is_active ? 'admin-badge--success' : 'admin-badge--muted' }}">{{ $plan->is_active ? 'نشطة' : 'معطّلة' }}</span></td>
                        <td class="admin-table-actions">
                            <a href="{{ route('admin.installment-plans.edit', $plan) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                            @canAdmin('installments.manage')
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleActive({{ $plan->id }})">{{ $plan->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                            @endcanAdmin
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">لا توجد خطط بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('partials.admin.shell-end')
