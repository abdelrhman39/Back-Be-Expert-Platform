<?php

use App\Models\AcademicStudent;
use App\Services\EnrollmentService;
use App\Support\AcademicProgramOptions;
use App\Support\AcademicStudentOptions;
use App\Support\CatalogEnrollmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('قائمة التعلم | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function enrollments()
    {
        return app(EnrollmentService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function academicStudent(): ?AcademicStudent
    {
        return auth()->user()?->academicStudent()
            ->with([
                'batch.program',
                'section.course',
            ])
            ->first();
    }

    #[Computed]
    public function hasAcademicProgram(): bool
    {
        return (bool) $this->academicStudent?->batch?->program;
    }

    #[Computed]
    public function hasLearningItems(): bool
    {
        return $this->hasAcademicProgram || $this->enrollments->isNotEmpty();
    }

    #[Computed]
    public function stats(): array
    {
        $catalog = app(EnrollmentService::class)->statsForUser(auth()->user());
        $academicCount = $this->hasAcademicProgram ? 1 : 0;
        $academicActive = $this->hasAcademicProgram
            && in_array($this->academicStudent?->academic_status, ['studying', 'eligible', 'expected'], true)
            ? 1
            : 0;
        $academicCompleted = $this->hasAcademicProgram
            && $this->academicStudent?->academic_status === 'graduated'
            ? 1
            : 0;

        $total = $catalog['total'] + $academicCount;
        $active = $catalog['active'] + $academicActive;
        $completed = $catalog['completed'] + $academicCompleted;

        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed,
            'avg_progress' => $catalog['total'] > 0
                ? $catalog['avg_progress']
                : ($academicCount > 0 ? null : 0.0),
            'academic' => $academicCount,
            'catalog' => $catalog['total'],
        ];
    }
};
?>

@php
    $locale = app()->getLocale();
    $student = $this->academicStudent;
    $program = $student?->batch?->program;
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'learning-list', 'portalTitle' => 'قائمة التعلم'])

<div class="portal-dashboard portal-learning-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">قائمة التعلم</h1>
            <p class="portal-orders-intro__desc">برامجك الأكاديمية ودوراتك المفعّلة — تابع تقدمك من هنا.</p>
        </div>
        <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> دورة جديدة
        </a>
    </div>

    @if ($this->hasLearningItems)
        <div class="portal-kpi-strip portal-kpi-strip--learning">
            <div class="portal-kpi-v2 portal-kpi-v2--orders">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-book"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span>
                    <span class="portal-kpi-v2__label">إجمالي البرامج والدورات</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--paid">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-play"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['active'] }}</span>
                    <span class="portal-kpi-v2__label">نشطة</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cert">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-circle-check"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['completed'] }}</span>
                    <span class="portal-kpi-v2__label">مكتملة</span>
                </span>
            </div>
            <div class="portal-kpi-v2 portal-kpi-v2--cart">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-graduation-cap"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['academic'] }}</span>
                    <span class="portal-kpi-v2__label">برامج أكاديمية</span>
                </span>
            </div>
        </div>
    @endif

    @if (! $this->hasLearningItems)
        <div class="portal-panel">
            <div class="portal-empty portal-empty--lg">
                <div class="portal-empty__icon"><i class="fa-solid fa-book-open"></i></div>
                <p>لا توجد برامج أو دورات في قائمة التعلم بعد</p>
                <span class="portal-empty__hint">ستظهر هنا برامجك الأكاديمية بعد التسجيل، وكذلك الدورات بعد شرائها ودفعها.</span>
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm mt-2">تصفح الدورات</a>
            </div>
        </div>
    @else
        @if ($this->hasAcademicProgram)
            <section class="portal-learning-section" aria-labelledby="academic-learning-heading">
                <header class="portal-learning-section__head">
                    <h2 id="academic-learning-heading">برامجك الأكاديمية</h2>
                    <p>الدبلومات والبرامج المرتبطة بتسجيلك الدراسي.</p>
                </header>

                <div class="portal-learning-grid">
                    <article class="portal-learning-card portal-learning-card--academic">
                        <div class="portal-learning-card__media portal-learning-card__media--academic">
                            @if ($program->poster_image)
                                <img src="{{ static_asset($program->poster_image) }}" alt="" loading="lazy">
                            @else
                                <span class="portal-learning-card__placeholder portal-learning-card__placeholder--academic">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </span>
                            @endif
                            <span class="portal-enrollment-badge portal-enrollment-badge--academic">
                                {{ AcademicProgramOptions::typeLabel($program->type ?: 'diploma') }}
                            </span>
                        </div>
                        <div class="portal-learning-card__body">
                            <h2 class="portal-learning-card__title">{{ $program->name_ar }}</h2>
                            <div class="portal-learning-card__meta">
                                @if ($program->code)
                                    <span><i class="fa-solid fa-hashtag"></i> {{ $program->code }}</span>
                                @endif
                                @if ($student->batch?->name)
                                    <span><i class="fa-solid fa-users"></i> {{ $student->batch->name }}</span>
                                @endif
                                @if ($student->section?->course?->name_ar)
                                    <span><i class="fa-solid fa-book"></i> {{ $student->section->course->name_ar }}</span>
                                @endif
                            </div>
                            <div class="portal-learning-card__status-row">
                                <span class="portal-learning-status">
                                    {{ AcademicStudentOptions::academicStatusLabel($student->academic_status) }}
                                </span>
                            </div>
                            <div class="portal-learning-card__actions">
                                <a href="{{ route('academic-curriculum', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-sitemap"></i> منهج البرنامج
                                </a>
                                <a href="{{ route('sessions', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-chalkboard"></i> حصصي
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        @endif

        @if ($this->enrollments->isNotEmpty())
            <section class="portal-learning-section" aria-labelledby="catalog-learning-heading">
                <header class="portal-learning-section__head">
                    <h2 id="catalog-learning-heading">دوراتك المشتراة</h2>
                    <p>الدورات المفعّلة من متجر المنصة.</p>
                </header>

                <div class="portal-learning-grid">
                    @foreach ($this->enrollments as $enrollment)
                        <article class="portal-learning-card">
                            <div class="portal-learning-card__media">
                                @if ($enrollment->displayImage())
                                    <img src="{{ static_asset($enrollment->displayImage()) }}" alt="" loading="lazy">
                                @else
                                    <span class="portal-learning-card__placeholder"><i class="fa-solid fa-graduation-cap"></i></span>
                                @endif
                                <span @class(['portal-enrollment-badge', CatalogEnrollmentOptions::statusBadgeClass($enrollment->status)])>
                                    {{ CatalogEnrollmentOptions::statusLabel($enrollment->status) }}
                                </span>
                            </div>
                            <div class="portal-learning-card__body">
                                <h2 class="portal-learning-card__title">{{ $enrollment->displayTitle() }}</h2>
                                <div class="portal-learning-card__meta">
                                    <span><i class="fa-solid fa-laptop"></i> {{ CatalogEnrollmentOptions::deliveryLabel($enrollment->delivery_type) }}</span>
                                    <span><i class="fa-regular fa-calendar"></i> {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="portal-learning-card__progress">
                                    <div class="portal-learning-card__progress-head">
                                        <span>التقدم</span>
                                        <strong>{{ $enrollment->progress_percent }}%</strong>
                                    </div>
                                    <div class="portal-attendance-mini__bar">
                                        <span style="width: {{ min(100, $enrollment->progress_percent) }}%"></span>
                                    </div>
                                </div>
                                <div class="portal-learning-card__actions">
                                    <a href="{{ route('learning.player', ['locale' => $locale, 'enrollment' => $enrollment->id]) }}" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-play"></i> متابعة التعلم
                                    </a>
                                    @if ($enrollment->order?->reference)
                                        <a href="{{ route('my-orders.show', ['locale' => $locale, 'order' => $enrollment->order->reference]) }}" class="btn btn-outline-secondary btn-sm">
                                            الطلب
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @elseif ($this->hasAcademicProgram)
            <div class="portal-learning-catalog-hint">
                <p>ليس لديك دورات مشتراة بعد. يمكنك تصفح متجر الدورات وإضافتها إلى قائمة التعلم.</p>
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">تصفح الدورات</a>
            </div>
        @endif
    @endif
</div>

@include('partials.portal.shell-end')
