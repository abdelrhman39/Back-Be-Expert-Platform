<?php

use App\Models\CatalogCourse;
use App\Services\CatalogCourseService;
use App\Services\CourseEnrollmentService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
class extends Component
{
    public CatalogCourse $course;

    public bool $compact = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $deliveryType = 'online';

    public function mount(CatalogCourse $course, bool $compact = false): void
    {
        abort_unless($course->status === 'published', 404);

        $this->course = $course;
        $this->compact = $compact;
        $this->deliveryType = $this->defaultDeliveryType();

        if ($user = portal_user() ?? auth()->user()) {
            $this->name = $user->displayName();
            $this->email = (string) $user->email;
            $this->phone = (string) ($user->phone ?? '');
        }
    }

    #[Computed]
    public function schedule(): array
    {
        return app(CatalogCourseService::class)->trainingSchedule($this->course);
    }

    public function hasOnlinePrice(): bool
    {
        return $this->course->allowsOnline() && $this->course->price_online !== null;
    }

    public function hasOnsitePrice(): bool
    {
        return $this->course->allowsOnsite() && $this->course->price_onsite !== null;
    }

    public function showDeliveryChoice(): bool
    {
        return $this->course->offersDeliveryChoice();
    }

    public function displayPrice(): ?float
    {
        return app(CourseEnrollmentService::class)->resolvePrice(
            $this->course,
            app(CourseEnrollmentService::class)->resolveDeliveryType($this->course, $this->deliveryType),
        );
    }

    public function enroll(CourseEnrollmentService $enrollment): void
    {
        $allowed = $this->course->availableDeliveryTypes();
        $rules = ['required', 'in:online,onsite,offline'];
        if ($allowed !== []) {
            $rules = ['required', 'in:'.implode(',', $allowed)];
        }

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'deliveryType' => $rules,
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الجوال',
            'deliveryType' => 'نوع التدريب',
        ]);

        $enrollment->enroll(
            $this->course,
            $this->name,
            $this->email,
            $this->phone,
            $this->deliveryType,
        );

        $this->redirect(route('checkout', ['locale' => app()->getLocale()]), navigate: true);
    }

    protected function defaultDeliveryType(): string
    {
        $available = $this->course->availableDeliveryTypes();

        if ($available !== []) {
            return $available[0];
        }

        return $this->course->allowsOnsite() ? 'onsite' : 'online';
    }
};
?>

<div>
    @if (! $compact)
        @include('partials.catalog.course-enroll-breadcrumb', ['course' => $course])
    @endif

    <div @class(['course-enroll-wrap', 'course-enroll-wrap--compact' => $compact])>
        @include('partials.catalog.course-enroll-form', [
            'course' => $course,
            'compact' => $compact,
            'schedule' => $this->schedule,
        ])
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/course-enroll.css') }}">
@endpush
