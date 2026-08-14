<?php

use App\Models\CatalogCourse;
use App\Services\RegistrationApplicationService;
use App\Support\RegistrationApplicationOptions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app-inner')]
class extends Component
{
    use WithFileUploads;

    public string $type = 'client';

    /** @var array<string, mixed> */
    public array $formData = [];

    /** @var array<string, mixed> */
    public array $uploads = [];

    public bool $terms = false;

    public ?string $courseName = null;

    public ?int $courseId = null;

    public ?CatalogCourse $linkedCourse = null;

    public ?string $submittedReference = null;

    public function mount(string $type): void
    {
        abort_unless(array_key_exists($type, RegistrationApplicationOptions::types()), 404);

        $this->type = $type;
        $this->courseName = request()->query('course');
        $this->courseId = request()->integer('course_id') ?: null;

        foreach (RegistrationApplicationOptions::fieldsFor($type) as $field) {
            if ($field['type'] !== 'file') {
                $this->formData[$field['key']] ??= '';
            }
        }

        if ($this->courseId) {
            $this->linkedCourse = CatalogCourse::query()
                ->whereKey($this->courseId)
                ->where('status', 'published')
                ->first();
        }

        $this->prefillProgramContext();

        $user = auth()->user();

        if ($user) {
            $this->prefillFromUser($user);
        }
    }

    public function rendering($view): void
    {
        $meta = RegistrationApplicationOptions::types()[$this->type] ?? [];
        $pageTitle = $meta['page_title'] ?? 'تقديم طلب';

        $view->title($pageTitle.' | مركز التعلم المستمر');

        if (filled($meta['meta_description'] ?? null)) {
            $view->layoutData(['metaDescription' => $meta['meta_description']]);
        }
    }

    protected function prefillProgramContext(): void
    {
        if ($this->linkedCourse) {
            $this->courseName = $this->linkedCourse->displayTitle();
            $this->formData['item_type'] = 'course';

            if (blank($this->formData['interested_programs'] ?? null)) {
                $this->formData['interested_programs'] = $this->linkedCourse->displayTitle();
            }

            return;
        }

        if ($this->courseName) {
            $this->formData['item_type'] = 'course';

            if (blank($this->formData['interested_programs'] ?? null)) {
                $this->formData['interested_programs'] = $this->courseName;
            }
        }
    }

    protected function prefillFromUser(\App\Models\User $user): void
    {
        $map = [
            'name' => $user->displayName(),
            'email' => $user->email,
            'phone' => $user->phone,
            'f_name' => explode(' ', $user->displayName(), 2)[0] ?? '',
            'l_name' => explode(' ', $user->displayName(), 2)[1] ?? '',
            'ssn' => $user->national_id,
        ];

        foreach ($map as $key => $value) {
            if (array_key_exists($key, $this->formData) && blank($this->formData[$key]) && filled($value)) {
                $this->formData[$key] = $value;
            }
        }
    }

    public function submit(RegistrationApplicationService $service): void
    {
        $this->validate(
            RegistrationApplicationOptions::validationRules($this->type),
            [],
            RegistrationApplicationOptions::attributeNames($this->type),
        );

        $files = [];

        foreach (RegistrationApplicationOptions::fieldsFor($this->type) as $field) {
            if ($field['type'] === 'file' && isset($this->uploads[$field['key']])) {
                $files[$field['key']] = $this->uploads[$field['key']];
            }
        }

        $application = $service->submit(
            $this->type,
            $this->formData,
            $files,
            auth()->user(),
            $this->courseName,
            $this->linkedCourse?->id ?? $this->courseId,
        );

        $this->submittedReference = $application->application_no;
    }
};
?>

@php
    $meta = RegistrationApplicationOptions::types()[$type];
    $fields = RegistrationApplicationOptions::fieldsFor($type);
    $locale = app()->getLocale();
    $sections = $meta['sections'] ?? null;
    $groupedFields = $sections
        ? collect($fields)->groupBy(fn ($field) => $field['section'] ?? 'default')
        : collect(['default' => $fields]);
@endphp

<div>
    <div class="breadcrumb-bar">
        <div class="breadcrumb-img">
            <div class="breadcrumb-left">
                <img src="{{ static_asset('assets/banner-bg-03.png') }}" alt="">
            </div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 col-12">
                    <nav aria-label="breadcrumb" class="page-breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $meta['page_title'] }}</li>
                        </ol>
                    </nav>
                    <h1 class="breadcrumb-title">{{ $meta['page_title'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="apply-form-page">
        <div class="container">
            @if (! empty($meta['page_intro']))
                <div class="apply-form-intro">
                    <p>{{ $meta['page_intro'] }}</p>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="apply-form-card">
                        @if ($submittedReference)
                            <div class="apply-success-card">
                                <div class="apply-success-card__icon" aria-hidden="true">✓</div>
                                <h2 class="h4 fw-bold mb-2">تم إرسال طلبك بنجاح</h2>
                                <p class="text-muted mb-2">احتفظ برقم الطلب للمتابعة:</p>
                                <div class="apply-success-card__ref" dir="ltr">{{ $submittedReference }}</div>
                                <p class="text-muted mb-4">سيتواصل معك فريق المنصة بعد مراجعة الطلب خلال أوقات العمل الرسمية.</p>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <a href="{{ route('apply.track', ['locale' => $locale, 'application' => $submittedReference]) }}" class="btn btn-primary">متابعة الطلب</a>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-outline-primary">تصفّح البرامج</a>
                                    <a href="{{ route('home', ['locale' => $locale]) }}" class="btn btn-outline-secondary">العودة للرئيسية</a>
                                </div>
                            </div>
                        @else
                            @if ($linkedCourse)
                                <div class="apply-course-card">
                                    <img src="{{ $linkedCourse->posterUrl() }}" alt="" class="apply-course-card__img">
                                    <div>
                                        <p class="apply-course-card__meta mb-1">طلب تسجيل للبرنامج</p>
                                        <h3 class="apply-course-card__title">{{ $linkedCourse->displayTitle() }}</h3>
                                        @if ($linkedCourse->displayPrice())
                                            <p class="apply-course-card__meta">{{ $linkedCourse->displayPrice() }}</p>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($courseName)
                                <div class="apply-course-card">
                                    <img src="{{ default_poster_url() }}" alt="" class="apply-course-card__img">
                                    <div>
                                        <p class="apply-course-card__meta mb-1">البرنامج المطلوب</p>
                                        <h3 class="apply-course-card__title">{{ $courseName }}</h3>
                                    </div>
                                </div>
                            @endif

                            <form wire:submit="submit" enctype="multipart/form-data">
                                @if ($sections)
                                    @foreach ($sections as $sectionKey => $sectionTitle)
                                        @php $sectionFields = $groupedFields->get($sectionKey, collect()); @endphp
                                        @if ($sectionFields->isNotEmpty())
                                            <div class="apply-form-section">
                                                <div class="apply-form-section__head">
                                                    <span class="apply-form-section__num">{{ $loop->iteration }}</span>
                                                    <h2 class="apply-form-section__title">{{ $sectionTitle }}</h2>
                                                </div>
                                                <div class="row">
                                                    @foreach ($sectionFields as $field)
                                                        @include('partials.apps.registration-form-field', ['field' => $field])
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="row">
                                        @foreach ($fields as $field)
                                            @include('partials.apps.registration-form-field', ['field' => $field])
                                        @endforeach
                                    </div>
                                @endif

                                <div class="apply-form-terms">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input" id="terms" wire:model="terms">
                                        <label class="form-check-label" for="terms">
                                            أوافق على
                                            <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'terms-and-conditions']) }}" target="_blank" rel="noopener">الشروط والأحكام</a>
                                            و
                                            <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'privacy-policy']) }}" target="_blank" rel="noopener">سياسة الخصوصية</a>
                                        </label>
                                    </div>
                                    @error('terms') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                </div>

                                <div class="apply-form-actions">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="submit">إرسال الطلب</span>
                                        <span wire:loading wire:target="submit">جاري الإرسال…</span>
                                    </button>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-link text-muted">إلغاء والعودة للبرامج</a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4">
                    @include('partials.apps.apply-sidebar')
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/apply-form.css') }}">
@endpush
