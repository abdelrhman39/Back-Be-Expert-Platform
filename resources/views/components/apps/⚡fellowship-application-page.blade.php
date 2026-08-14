<?php

use App\Models\Fellowship;
use App\Services\FellowshipFormService;
use App\Services\RegistrationApplicationService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app-inner')]
class extends Component
{
    use WithFileUploads;

    public Fellowship $fellowship;

    /** @var array<string, mixed> */
    public array $formData = [];

    /** @var array<string, mixed> */
    public array $uploads = [];

    public bool $terms = false;

    public ?string $submittedReference = null;

    public function mount(Fellowship $fellowship, FellowshipFormService $forms): void
    {
        abort_unless($fellowship->acceptsApplications(), 404);

        $this->fellowship = $fellowship;

        foreach ($forms->resolveFields($fellowship) as $field) {
            if (($field['type'] ?? '') !== 'file') {
                $this->formData[$field['key']] ??= ($field['type'] ?? '') === 'checkbox' ? false : '';
            }
        }

        $user = auth()->user();

        if ($user) {
            $this->prefillFromUser($user);
        }
    }

    #[Computed]
    public function fields(): array
    {
        return app(FellowshipFormService::class)->resolveFields($this->fellowship);
    }

    public function rendering($view): void
    {
        $view->title(platform_title($this->fellowship->displayTitle().' | تقديم طلب'));
    }

    protected function prefillFromUser(\App\Models\User $user): void
    {
        $map = [
            'name' => $user->displayName(),
            'email' => $user->email,
            'phone' => $user->phone,
            'national_id' => $user->national_id,
        ];

        foreach ($map as $key => $value) {
            if (array_key_exists($key, $this->formData) && blank($this->formData[$key]) && filled($value)) {
                $this->formData[$key] = $value;
            }
        }
    }

    public function submit(RegistrationApplicationService $service, FellowshipFormService $forms): void
    {
        $fields = $forms->resolveFields($this->fellowship);
        $settings = $forms->resolveFileUploadSettings($this->fellowship);

        $this->validate(
            $forms->validationRules($fields, $settings),
            [],
            $forms->attributeNames($fields),
        );

        $application = $service->submit(
            'fellowship',
            $this->formData,
            array_filter($this->uploads),
            auth()->user(),
            $this->fellowship->displayTitle(),
            null,
            $this->fellowship->id,
        );

        $this->submittedReference = $application->application_no;
    }
};
?>

@php $locale = app()->getLocale(); @endphp

<div class="page-content with-wizard">
    <div class="container">
        <div class="breadcrumb-bar">
            <div class="container">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">{{ $fellowship->displayTitle() }}</li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title">تقديم طلب — {{ $fellowship->displayTitle() }}</h2>
            </div>
        </div>

        <section class="popular-section with-wizard py-4">
            @if ($fellowship->displayDescription())
                <div class="alert alert-info mb-4">{{ $fellowship->displayDescription() }}</div>
            @endif

            @if ($submittedReference)
                <div class="blog-form">
                    <div class="alert alert-success">
                        <h4>تم إرسال طلب الزمالة</h4>
                        <p>رقم الطلب: <strong dir="ltr">{{ $submittedReference }}</strong></p>
                        <a href="{{ route('apply.track', ['locale' => $locale, 'application' => $submittedReference]) }}" class="btn btn-primary">متابعة الطلب</a>
                    </div>
                </div>
            @else
                <div class="blog-form">
                    <form wire:submit="submit">
                        <div class="row">
                            @foreach ($this->fields as $field)
                                @include('partials.apps.registration-form-field', ['field' => $field])
                            @endforeach

                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="terms" wire:model="terms">
                                    <label class="form-check-label" for="terms">أوافق على الشروط والأحكام</label>
                                </div>
                                @error('terms') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-lg-6">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">إرسال الطلب</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </section>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/apply-form.css') }}">
@endpush
