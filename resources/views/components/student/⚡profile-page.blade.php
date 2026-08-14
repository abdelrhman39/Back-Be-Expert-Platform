<?php

use App\Models\AttendanceRecord;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CertificateService;
use App\Services\EnrollmentService;
use App\Services\WishlistService;
use App\Support\CertificateAccessSettings;
use App\Support\AcademicStudentOptions;
use App\Support\AttendanceOptions;
use App\Support\OrderOptions;
use App\Support\TeamsSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('حسابي الشخصي | منصة مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function user()
    {
        return auth()->user()?->load([
            'academicStudent.batch.program',
            'academicStudent.section.course',
            'orders',
            'microsoftTeamsConnection',
        ]);
    }

    #[Computed]
    public function catalogEnrollments()
    {
        $user = $this->user;

        if (! $user) {
            return collect();
        }

        return app(EnrollmentService::class)->forUser($user)->take(4);
    }

    #[Computed]
    public function hasLearningPrograms(): bool
    {
        $student = $this->user?->academicStudent;

        return (bool) ($student?->batch?->program) || $this->catalogEnrollments->isNotEmpty();
    }

    #[Computed]
    public function stats(): array
    {
        $user = $this->user;

        return [
            'cart' => app(CartService::class)->count(),
            'wishlist' => app(WishlistService::class)->count(),
            'orders' => $user?->orders->count() ?? 0,
            'paid_orders' => $user?->orders->where('status', 'paid')->count() ?? 0,
            'certificates' => $user
                ? app(CertificateService::class)->forUser($user)->count()
                : 0,
            'certificates_portal' => CertificateAccessSettings::portalEnabled(),
        ];
    }

    #[Computed]
    public function missingFields(): array
    {
        $user = $this->user;
        $missing = [];

        if (! $user?->national_id) {
            $missing[] = 'رقم الهوية الوطنية';
        }
        if (! $user?->phone) {
            $missing[] = 'رقم الجوال';
        }
        if (! $user?->email) {
            $missing[] = 'البريد الإلكتروني';
        }

        if (TeamsSettings::isEnabled() && TeamsSettings::isConfigured() && $user?->academicStudent && ! $user->microsoftTeamsConnection) {
            $missing[] = 'ربط Microsoft Teams';
        }

        return $missing;
    }

    #[Computed]
    public function profileCompletion(): int
    {
        $fields = ['name', 'email', 'phone', 'national_id'];
        $filled = collect($fields)->filter(fn (string $field) => filled($this->user?->{$field}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }

    #[Computed]
    public function greeting(): string
    {
        $hour = (int) now()->format('H');

        if ($hour < 12) {
            return 'صباح الخير';
        }

        if ($hour < 17) {
            return 'مساء الخير';
        }

        return 'مساء النور';
    }

    #[Computed]
    public function totalSpent(): float
    {
        return (float) ($this->user?->orders->where('status', 'paid')->sum('total') ?? 0);
    }

    #[Computed]
    public function pendingOrders()
    {
        return Order::query()
            ->where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->latest()
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function recentOrders()
    {
        return Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function attendanceSummary(): ?array
    {
        $student = $this->user?->academicStudent;

        if (! $student) {
            return null;
        }

        $records = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return AttendanceOptions::summarizeRecords($records);
    }
};
?>

@php
    $locale = app()->getLocale();
    $student = $this->user?->academicStudent;
    $completion = $this->profileCompletion;
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'profile', 'portalTitle' => 'الملف الشخصي'])

<div class="portal-dashboard portal-dashboard--profile">
    <section class="portal-hero portal-hero--v2">
        <div class="portal-hero__banner">
            <div class="portal-hero__banner-content">
                <div class="portal-hero__welcome">
                    <span class="portal-hero__greeting">{{ $this->greeting }}، {{ $this->user?->displayName() }}</span>
                    <p class="portal-hero__tagline">مرحباً بك في لوحة تحكمك — تصفّح برامجك ودوراتك وتابع تقدمك من مكان واحد.</p>
                </div>
                <div class="portal-hero__banner-stats">
                    <div class="portal-banner-stat">
                        <span class="portal-banner-stat__value">{{ $this->stats['paid_orders'] }}</span>
                        <span class="portal-banner-stat__label">طلبات مدفوعة</span>
                    </div>
                    <div class="portal-banner-stat">
                        <span class="portal-banner-stat__value">{{ number_format($this->totalSpent, 0) }}</span>
                        <span class="portal-banner-stat__label">إجمالي الشراء (ر.س)</span>
                    </div>
                    @if ($this->stats['certificates_portal'])
                        <div class="portal-banner-stat">
                            <span class="portal-banner-stat__value">{{ $this->stats['certificates'] }}</span>
                            <span class="portal-banner-stat__label">شهادات</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="portal-hero__orbs" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
        </div>

        <div class="portal-hero__body">
            <div class="portal-hero__profile-row">
                <span class="portal-hero__avatar">{{ $this->user?->initials() }}</span>
                <div class="portal-hero__identity">
                    <h1 class="portal-hero__name">{{ $this->user?->displayName() }}</h1>
                    <div class="portal-hero__badges">
                        <span class="portal-badge portal-badge--role"><i class="fa-solid fa-user-graduate"></i> متدرب</span>
                        @if ($student)
                            <span class="portal-badge portal-badge--status">
                                {{ $student->study_status ?: AcademicStudentOptions::academicStatusLabel($student->academic_status) }}
                            </span>
                        @endif
                        @if ($student?->academic_id)
                            <span class="portal-badge portal-badge--muted" dir="ltr">{{ $student->academic_id }}</span>
                        @endif
                    </div>
                    <div class="portal-hero__chips">
                        @if ($this->user?->email)
                            <span class="portal-chip"><i class="fa fa-envelope"></i> {{ $this->user->email }}</span>
                        @endif
                        @if ($this->user?->phone)
                            <span class="portal-chip" dir="ltr"><i class="fa fa-phone"></i> {{ $this->user->phone }}</span>
                        @endif
                        @if ($this->user?->national_id)
                            <span class="portal-chip" dir="ltr"><i class="fa fa-id-card"></i> {{ $this->user->national_id }}</span>
                        @endif
                        <span class="portal-chip"><i class="fa fa-calendar"></i> عضو منذ {{ $this->user?->created_at?->translatedFormat('M Y') ?? '—' }}</span>
                    </div>
                </div>
                <div class="portal-hero__actions">
                    <a href="{{ route('settings', ['locale' => $locale]) }}" class="btn btn-primary">
                        <i class="fa fa-pen"></i> تعديل البيانات
                    </a>
                    <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-outline-primary">قائمة التعلم</a>
                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-light border">تصفح الدورات</a>
                </div>
            </div>
        </div>
    </section>

    @if ($this->missingFields !== [])
        <div class="portal-alert portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-exclamation"></i></span>
            <div class="portal-alert__content">
                <strong>أكمل ملفك الشخصي</strong>
                <span> — اكتمال الملف {{ $completion }}%</span>
            </div>
            <a href="{{ route('settings', ['locale' => $locale]) }}" class="portal-alert__action">إكمال الآن</a>
        </div>
    @endif

    <div class="portal-kpi-strip">
        <a href="{{ route('cart', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--cart">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-cart-shopping"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['cart'] }}</span>
                <span class="portal-kpi-v2__label">سلة التسوق</span>
            </span>
        </a>
        <a href="{{ route('wishlist', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--heart">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-heart"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['wishlist'] }}</span>
                <span class="portal-kpi-v2__label">المفضلة</span>
            </span>
        </a>
        <a href="{{ route('my-orders', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--orders">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['orders'] }}</span>
                <span class="portal-kpi-v2__label">طلبات الشراء</span>
            </span>
        </a>
        <div class="portal-kpi-v2 portal-kpi-v2--paid">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-circle-check"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['paid_orders'] }}</span>
                <span class="portal-kpi-v2__label">طلبات مدفوعة</span>
            </span>
        </div>
        @if ($this->stats['certificates_portal'])
            <a href="{{ route('certificates', ['locale' => $locale]) }}" class="portal-kpi-v2 portal-kpi-v2--cert">
                <span class="portal-kpi-v2__icon"><i class="fa-solid fa-award"></i></span>
                <span class="portal-kpi-v2__body">
                    <span class="portal-kpi-v2__value">{{ $this->stats['certificates'] }}</span>
                    <span class="portal-kpi-v2__label">الشهادات</span>
                </span>
            </a>
        @endif
    </div>

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            @if ($this->pendingOrders->isNotEmpty())
                <div class="portal-pending-banner">
                    <div class="portal-pending-banner__icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="portal-pending-banner__text">
                        <strong>{{ $this->pendingOrders->count() }} {{ $this->pendingOrders->count() === 1 ? 'طلب' : 'طلبات' }} بانتظار الدفع</strong>
                        <span>أكمل الدفع لبدء التعلم</span>
                    </div>
                    <a href="{{ route('my-orders', ['locale' => $locale]) }}" class="btn btn-sm btn-warning">إتمام الدفع</a>
                </div>
            @endif

            @include('partials.portal.dashboard-programs', [
                'locale' => $locale,
                'student' => $student,
                'program' => $student?->batch?->program,
                'enrollments' => $this->catalogEnrollments,
                'hasPrograms' => $this->hasLearningPrograms,
            ])

            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-receipt"></i> آخر الطلبات</h2>
                    @if ($this->recentOrders->isNotEmpty())
                        <a href="{{ route('my-orders', ['locale' => $locale]) }}" class="portal-panel__link">عرض الكل <i class="fa-solid fa-arrow-left-long"></i></a>
                    @endif
                </div>
                <div class="portal-panel__body">
                    @if ($this->recentOrders->isEmpty())
                        <div class="portal-empty">
                            <div class="portal-empty__icon"><i class="fa-solid fa-bag-shopping"></i></div>
                            <p>لم تقم بأي عملية شراء بعد</p>
                            <span class="portal-empty__hint">ابدأ رحلتك التعليمية بتصفح دوراتنا المتاحة</span>
                            <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm mt-2">تصفح الدورات</a>
                        </div>
                    @else
                        <div class="portal-order-list">
                            @foreach ($this->recentOrders as $order)
                                @php
                                    $statusClass = match ($order->status) {
                                        'paid' => 'portal-status-pill--paid',
                                        'pending_payment' => 'portal-status-pill--pending',
                                        'cancelled' => 'portal-status-pill--cancelled',
                                        default => 'portal-status-pill--default',
                                    };
                                    $statusIcon = match ($order->status) {
                                        'paid' => 'fa-circle-check',
                                        'pending_payment' => 'fa-clock',
                                        'cancelled' => 'fa-circle-xmark',
                                        default => 'fa-circle',
                                    };
                                @endphp
                                <a href="{{ route('my-orders.show', ['locale' => $locale, 'order' => $order->reference]) }}" class="portal-order-item">
                                    <span class="portal-order-item__icon"><i class="fa-solid fa-file-invoice"></i></span>
                                    <span class="portal-order-item__main">
                                        <span class="portal-order-item__ref">{{ $order->reference }}</span>
                                        <span class="portal-order-item__date">{{ $order->created_at?->translatedFormat('d M Y') }}</span>
                                    </span>
                                    <span class="portal-order-item__amount" dir="ltr">{{ number_format((float) $order->total, 2) }} <small>ر.س</small></span>
                                    <span class="portal-status-pill {{ $statusClass }}"><i class="fa-solid {{ $statusIcon }}"></i> {{ OrderOptions::statusLabelForOrder($order) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <aside class="portal-side-col">
            <div class="portal-widget portal-widget--completion">
                <div class="portal-completion-ring" style="--pct: {{ $completion }}">
                    <svg viewBox="0 0 36 36" aria-hidden="true">
                        <path class="portal-completion-ring__bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="portal-completion-ring__fill" stroke-dasharray="{{ $completion }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="portal-completion-ring__pct">{{ $completion }}%</span>
                </div>
                <div class="portal-widget__body">
                    <h3 class="portal-widget__title">اكتمال الملف</h3>
                    <p class="portal-widget__desc">{{ $completion >= 100 ? 'ملفك مكتمل — أحسنت!' : 'أكمل بياناتك للحصول على تجربة أفضل' }}</p>
                    @if ($completion < 100)
                        <a href="{{ route('settings', ['locale' => $locale]) }}" class="portal-widget__link">تحديث البيانات ←</a>
                    @endif
                </div>
            </div>

            @if ($student)
                <div class="portal-widget portal-widget--academic">
                    <div class="portal-widget__head">
                        <span class="portal-widget__head-icon"><i class="fa-solid fa-graduation-cap"></i></span>
                        <h3 class="portal-widget__title">الملف الأكاديمي</h3>
                    </div>
                    <div class="portal-academic-list">
                        @if ($student->batch?->program)
                            <div class="portal-academic-item">
                                <span class="portal-academic-item__label">البرنامج</span>
                                <strong>{{ $student->batch->program->name_ar }}</strong>
                            </div>
                        @endif
                        @if ($student->batch)
                            <div class="portal-academic-item">
                                <span class="portal-academic-item__label">الدفعة</span>
                                <strong>{{ $student->batch->name }}</strong>
                            </div>
                        @endif
                        @if ($student->section)
                            <div class="portal-academic-item">
                                <span class="portal-academic-item__label">الشعبة</span>
                                <strong>{{ $student->section->name }}</strong>
                            </div>
                        @endif
                        <div class="portal-academic-item">
                            <span class="portal-academic-item__label">الحالة</span>
                            <strong>{{ AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</strong>
                        </div>
                    </div>
                    @if ($this->attendanceSummary)
                        <div class="portal-attendance-mini">
                            <div class="portal-attendance-mini__rate">
                                <span class="portal-attendance-mini__pct">{{ $this->attendanceSummary['rate'] }}%</span>
                                <span class="portal-attendance-mini__label">نسبة الحضور</span>
                            </div>
                            <div class="portal-attendance-mini__bar">
                                <span style="width: {{ min(100, $this->attendanceSummary['rate']) }}%"></span>
                            </div>
                            <div class="portal-attendance-mini__counts">
                                <span><i class="fa-solid fa-check"></i> {{ $this->attendanceSummary['present'] + $this->attendanceSummary['late'] }} حاضر</span>
                                <span><i class="fa-solid fa-xmark"></i> {{ $this->attendanceSummary['absent'] }} غائب</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @include('partials.portal.teams-connection')

            <div class="portal-cta-card">
                <div class="portal-cta-card__icon"><i class="fa-solid fa-book-open-reader"></i></div>
                <h3>استمر في التعلم</h3>
                <p>اكتشف دورات وبرامج جديدة تناسب مسارك المهني</p>
                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm w-100">استكشف الدورات</a>
            </div>
        </aside>
    </div>
</div>

@include('partials.portal.shell-end')
