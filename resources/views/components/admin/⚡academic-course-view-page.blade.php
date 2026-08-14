<?php

use App\Models\AcademicCourse;
use App\Support\AcademicCourseOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تفاصيل المقرر | لوحة التحكم')]
class extends Component
{
    public AcademicCourse $course;

    public function mount(AcademicCourse $course): void
    {
        $this->course = $course->load(['program', 'level']);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.academic-courses'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.academic-courses'), 'label' => 'المقررات الدراسية'],
        ['label' => $course->name_ar],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-course-view-card">
    <div class="admin-course-view-head">
        <h1 class="admin-course-view-title">{{ $course->name_ar }}</h1>
        <div class="admin-row-actions">
            <a href="{{ route('admin.academic-courses.edit', $course) }}" class="admin-btn-primary admin-btn-primary--sm">تعديل</a>
            @if ($course->program)
                <a href="{{ route('admin.programs.show', ['program' => $course->program, 'tab' => 'courses']) }}" class="admin-btn-secondary admin-btn-secondary--sm">← رجوع للبرنامج</a>
            @else
                <a href="{{ route('admin.academic-courses') }}" class="admin-btn-secondary admin-btn-secondary--sm">← رجوع للمقررات</a>
            @endif
        </div>
    </div>

    <section class="admin-course-block">
        <h2 class="admin-course-block__title">
            <span class="admin-course-block__title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
            </span>
            المعلومات الأساسية
        </h2>
        <div class="admin-info-grid admin-info-grid--3">
            @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'اسم المقرر بالعربية', 'value' => $course->name_ar])
            @include('partials.admin.info-card', ['icon' => 'globe', 'label' => 'اسم المقرر بالإنجليزية', 'value' => $course->name_en ?: '—'])
            @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'رمز المقرر بالعربية', 'value' => $course->symbol_ar ?: '—'])
            @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'رمز المقرر بالإنجليزية', 'value' => $course->symbol_en ?: '—'])
            @include('partials.admin.info-card', ['icon' => 'hash', 'label' => 'كود المقرر', 'value' => '<code class="admin-code">'.e($course->code).'</code>'])
            @include('partials.admin.info-card', ['icon' => 'clock', 'label' => 'عدد الساعات', 'value' => (string) $course->credit_hours])
        </div>
    </section>

    <section class="admin-course-block">
        <h2 class="admin-course-block__title">
            <span class="admin-course-block__title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </span>
            معلومات البرنامج
        </h2>
        <div class="admin-info-grid admin-info-grid--3">
            @if ($course->program)
                @include('partials.admin.info-card', [
                    'icon' => 'book',
                    'label' => 'البرنامج',
                    'value' => $course->program->name_ar.' <span class="admin-tag admin-tag--warn">'.e($course->program->displayDuration()).'</span>',
                    'wide' => true,
                ])
            @endif
            @include('partials.admin.info-card', ['icon' => 'layers', 'label' => 'المستوى الدراسي', 'value' => $course->displayLevel()])
            @include('partials.admin.info-card', ['icon' => 'users', 'label' => 'الفئة المستهدفة', 'value' => $course->target_group ?: 'لم يتم التحديد بعد'])
            @include('partials.admin.info-card', [
                'icon' => 'flag',
                'label' => 'الحالة',
                'value' => $course->status === 'active'
                    ? '<span class="admin-badge admin-badge--success">'.AcademicCourseOptions::statusLabel($course->status).'</span>'
                    : '<span class="admin-badge admin-badge--danger">'.AcademicCourseOptions::statusLabel($course->status).'</span>',
            ])
        </div>
    </section>

    <section class="admin-course-block">
        <h2 class="admin-course-block__title">
            <span class="admin-course-block__title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>
            </span>
            صورة المقرر
        </h2>
        @if ($course->resolved_image_url)
            <div class="admin-course-image">
                <img src="{{ $course->resolved_image_url }}" alt="صورة مقرر {{ $course->name_ar }}" loading="lazy">
            </div>
        @else
            <div class="admin-course-image-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>
                <p>لم يتم رفع صورة للمقرر بعد</p>
                <a href="{{ route('admin.academic-courses.edit', $course) }}" class="admin-btn-secondary admin-btn-secondary--sm">إضافة صورة</a>
            </div>
        @endif
    </section>

    @if ($course->summary)
        <section class="admin-course-block">
            <h2 class="admin-course-block__title">
                <span class="admin-course-block__title-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                </span>
                ملاحظات
            </h2>
            <p class="admin-detail-text">{{ $course->summary }}</p>
        </section>
    @endif

    <section class="admin-course-block">
        <h2 class="admin-course-block__title">
            <span class="admin-course-block__title-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            بيانات الإدخال
        </h2>
        <div class="admin-info-grid admin-info-grid--2">
            @include('partials.admin.info-card', ['icon' => 'user', 'label' => 'أُضيف بواسطة', 'value' => $course->added_by ?: '—'])
            @include('partials.admin.info-card', ['icon' => 'calendar', 'label' => 'تاريخ الإضافة', 'value' => $course->created_at?->format('Y-m-d H:i') ?? '—'])
        </div>
    </section>
</section>

@push('styles')
<style>
    .admin-row-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .admin-course-image {
        display: flex;
        justify-content: center;
        padding: 0.75rem;
        border: 1px solid var(--sa-border);
        border-radius: var(--radius-lg);
        background: var(--sa-mist);
    }
    .admin-course-image img {
        max-width: 100%;
        max-height: 320px;
        width: auto;
        height: auto;
        border-radius: var(--radius-md);
        object-fit: contain;
    }
</style>
@endpush

@include('partials.admin.shell-end')
