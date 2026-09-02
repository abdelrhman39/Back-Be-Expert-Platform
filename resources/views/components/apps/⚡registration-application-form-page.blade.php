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
        $pageTitle = RegistrationApplicationOptions::pageTitle($this->type);
        $metaDescription = RegistrationApplicationOptions::metaDescription($this->type);

        $view->title($pageTitle.' | '.platform_name());

        if (filled($metaDescription)) {
            $view->layoutData(['metaDescription' => $metaDescription]);
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
    $fields = RegistrationApplicationOptions::fieldsFor($type);
    $locale = app()->getLocale();
    $t = fn (string $key) => \App\Support\PublicCopy::apply($key, $locale);
    $typed = fn (string $key) => \App\Support\PublicCopy::applyForType($key, $type, $locale);
    $pageTitle = RegistrationApplicationOptions::pageTitle($type, $locale);
    $pageIntro = RegistrationApplicationOptions::pageIntro($type, $locale);
    $sections = RegistrationApplicationOptions::localizedSections($type, $locale);
    $groupedFields = $sections
        ? collect($fields)->groupBy(fn ($field) => $field['section'] ?? 'default')
        : collect(['default' => $fields]);
    $paths = [
        ['type' => 'client', 'label' => $t('path_client'), 'url' => route('apply.form', ['locale' => $locale, 'type' => 'client'])],
        ['type' => 'company', 'label' => $t('path_company'), 'url' => route('apply.form', ['locale' => $locale, 'type' => 'company'])],
        ['type' => 'instructor', 'label' => $t('path_instructor'), 'url' => route('apply.form', ['locale' => $locale, 'type' => 'instructor'])],
        ['type' => 'cooperative', 'label' => $t('path_cooperative'), 'url' => route('apply.form', ['locale' => $locale, 'type' => 'cooperative'])],
    ];
@endphp

<div class="apply-page">
    <header class="apply-hero">
        <div class="container">
            <nav aria-label="breadcrumb" class="apply-hero__crumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => $locale]) }}">{{ $t('home') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
                </ol>
            </nav>
            <p class="apply-hero__eyebrow">{{ platform_name() }}</p>
            <h1 class="apply-hero__title">{{ $pageTitle }}</h1>
            @if (filled($pageIntro))
                <p class="apply-hero__lead">{{ $pageIntro }}</p>
            @endif
            <div class="apply-paths" role="tablist" aria-label="{{ $t('paths_title') }}">
                @foreach ($paths as $path)
                    <a href="{{ $path['url'] }}" @class(['apply-paths__item', 'is-active' => $type === $path['type']])>
                        {{ $path['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('register', ['locale' => $locale]) }}" class="apply-paths__item">{{ $t('path_academic') }}</a>
            </div>
        </div>
    </header>

    <div class="apply-form-page">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="apply-form-card">
                        @if ($submittedReference)
                            <div class="apply-success-card">
                                <div class="apply-success-card__icon" aria-hidden="true"><i class="fa-solid fa-check"></i></div>
                                <h2 class="h4 fw-bold mb-2">{{ $t('success_title') }}</h2>
                                <p class="text-muted mb-2">{{ $t('success_keep') }}</p>
                                <div class="apply-success-card__ref" dir="ltr">{{ $submittedReference }}</div>
                                <p class="text-muted mb-4">{{ $t('success_lead') }}</p>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <a href="{{ route('apply.track', ['locale' => $locale, 'application' => $submittedReference]) }}" class="btn btn-primary apply-submit">{{ $t('track') }}</a>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-outline-primary">{{ $t('browse') }}</a>
                                    <a href="{{ route('home', ['locale' => $locale]) }}" class="btn btn-outline-secondary">{{ $t('home_link') }}</a>
                                </div>
                            </div>
                        @else
                            @if ($linkedCourse)
                                <div class="apply-course-card">
                                    <img src="{{ $linkedCourse->posterUrl() }}" alt="" class="apply-course-card__img">
                                    <div>
                                        <p class="apply-course-card__meta mb-1">{{ $t('course_request') }}</p>
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
                                        <p class="apply-course-card__meta mb-1">{{ $t('course_wanted') }}</p>
                                        <h3 class="apply-course-card__title">{{ $courseName }}</h3>
                                    </div>
                                </div>
                            @endif

                            <form wire:submit="submit" enctype="multipart/form-data" novalidate>
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
                                                        @include('partials.apps.registration-form-field', [
                                                            'field' => RegistrationApplicationOptions::localizeField($field, $locale),
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="row">
                                        @foreach ($fields as $field)
                                            @include('partials.apps.registration-form-field', [
                                                'field' => RegistrationApplicationOptions::localizeField($field, $locale),
                                            ])
                                        @endforeach
                                    </div>
                                @endif

                                <div class="apply-form-terms">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input" id="terms" wire:model="terms">
                                        <label class="form-check-label" for="terms">
                                            {{ $t('terms_prefix') }}
                                            <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'terms-and-conditions']) }}" target="_blank" rel="noopener">{{ $t('terms') }}</a>
                                            {{ $t('and') }}
                                            <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'privacy-policy']) }}" target="_blank" rel="noopener">{{ $t('privacy') }}</a>
                                        </label>
                                    </div>
                                    @error('terms') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                                </div>

                                <div class="apply-form-actions">
                                    <button type="submit" class="btn btn-primary apply-submit" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="submit">{{ $t('submit') }}</span>
                                        <span wire:loading wire:target="submit">
                                            <span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>
                                            {{ $t('sending') }}
                                        </span>
                                    </button>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-link text-muted">{{ $t('cancel') }}</a>
                                </div>
                                <p class="apply-secure"><i class="fa-solid fa-shield-halved"></i> {{ $typed('secure') }}</p>
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
    <link rel="stylesheet" href="{{ asset('css/apply-form.css') }}?v=3">
@endpush

