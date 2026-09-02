<?php

use App\Models\AcademicBatch;
use App\Services\AcademicRegistrationService;
use App\Services\InstallmentAcademicRegistrationService;
use App\Services\MoyasarService;
use App\Services\RegistrationService;
use App\Support\AcademicRegistrationOptions;
use App\Support\InstallmentSettings;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('التسجيل في البرامج | مركز التعلم المستمر')]
class extends Component
{
    public int $step = 1;

    public bool $academicFlow = false;

    public string $nameAr = '';

    public string $nationalId = '';

    public string $phone = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public bool $terms = false;

    public string $nationality = '';

    public string $city = '';

    public string $gender = '';

    public string $employmentStatus = '';

    public string $studyPeriod = 'evening';

    public ?int $batchId = null;

    public ?int $installmentPlanId = null;

    public ?string $flowError = null;

    public function mount(AcademicRegistrationService $registration): void
    {
        $this->academicFlow = InstallmentSettings::academicRegistrationEnabled();
        $isAcademicRoute = request()->routeIs('academic-registration');

        if (! $this->academicFlow) {
            return;
        }

        $batchFromQuery = (int) request()->query('batch', 0);
        if ($batchFromQuery > 0 && $registration->openBatches()->contains('id', $batchFromQuery)) {
            $this->batchId = $batchFromQuery;
        }

        if (auth()->check()) {
            if (! $registration->userCanRegister(auth()->user())) {
                $this->redirect(route('profile', ['locale' => app()->getLocale()]), navigate: true);

                return;
            }

            $this->prefillFromUser(auth()->user());
            if ($batchFromQuery > 0 && $this->batchId) {
                // Keep query batch over any existing student batch when opening registration.
                $this->batchId = $batchFromQuery;
            }
            $this->step = $this->profileComplete() ? 2 : 1;

            return;
        }

        if ($isAcademicRoute) {
            $this->step = 1;
        }
    }

    protected function prefillFromUser(\App\Models\User $user): void
    {
        $this->nameAr = $user->name_ar ?: $user->name;
        $this->nationalId = $user->national_id ?? '';
        $this->phone = $user->phone ? ltrim(str_replace('+966', '', $user->phone), '0') : '';
        $this->email = $user->email;

        $student = $user->academicStudent;

        if ($student) {
            $this->gender = $student->gender ?? '';
            $this->city = $student->city ?? '';
            $this->nationality = $student->nationality ?? '';
            $this->employmentStatus = $student->employment_status ?? '';
            $this->studyPeriod = $student->study_period ?? 'evening';
            $this->batchId = $student->batch_id;
        }
    }

    public function continueProfile(): void
    {
        $this->flowError = null;

        $this->validate([
            'nationality' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::nationalities()))],
            'city' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::cities()))],
            'gender' => ['required', 'in:ذكر,أنثى'],
            'employmentStatus' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::employmentStatuses()))],
            'studyPeriod' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::studyPeriods()))],
            'terms' => ['accepted'],
        ], [], [
            'nationality' => 'الجنسية',
            'city' => 'المدينة',
            'gender' => 'الجنس',
            'employmentStatus' => 'الحالة الوظيفية',
            'studyPeriod' => 'فترة الدراسة',
            'terms' => 'الشروط والأحكام',
        ]);

        $this->step = 2;
    }

    public function createAccount(RegistrationService $registration): void
    {
        $this->flowError = null;

        $rules = [
            'nameAr' => ['required', 'string', 'max:255'],
            'nationalId' => ['required', 'digits:10', 'regex:/^[12]\d{9}$/', 'unique:users,national_id'],
            'phone' => ['required', 'string', 'min:9'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'terms' => ['accepted'],
        ];

        if ($this->academicFlow) {
            $rules += [
                'nationality' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::nationalities()))],
                'city' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::cities()))],
                'gender' => ['required', 'in:ذكر,أنثى'],
                'employmentStatus' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::employmentStatuses()))],
                'studyPeriod' => ['required', 'in:'.implode(',', array_keys(AcademicRegistrationOptions::studyPeriods()))],
            ];
        }

        $validated = $this->validate($rules, [], [
            'nameAr' => 'الاسم',
            'nationalId' => 'رقم الهوية',
            'phone' => 'رقم الجوال',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'terms' => 'الشروط والأحكام',
            'nationality' => 'الجنسية',
            'city' => 'المدينة',
            'gender' => 'الجنس',
            'employmentStatus' => 'الحالة الوظيفية',
            'studyPeriod' => 'فترة الدراسة',
        ]);

        $phoneE164 = \App\Support\PhoneNormalizer::toE164($validated['phone']);

        if (\App\Models\User::query()->where('phone', $phoneE164)->exists()) {
            $this->addError('phone', 'رقم الجوال مستخدم مسبقاً.');

            return;
        }

        RegistrationService::validatePasswordNotMatchingIdentity([
            'national_id' => $validated['nationalId'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        $registration->register([
            'name_ar' => $validated['nameAr'],
            'national_id' => $validated['nationalId'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        if ($this->academicFlow) {
            $this->step = 2;

            return;
        }

        $this->redirect(route('profile', ['locale' => app()->getLocale()]), navigate: true);
    }

    public function selectBatch(int $batchId): void
    {
        $this->batchId = $batchId;
        $this->step = 3;
        $this->flowError = null;
    }

    public function back(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submitEnrollment(InstallmentAcademicRegistrationService $installments, MoyasarService $moyasar): void
    {
        $this->flowError = null;

        $this->validate([
            'batchId' => ['required', 'exists:academic_batches,id'],
            'installmentPlanId' => ['required', 'exists:installment_plan_templates,id'],
        ], [], [
            'batchId' => 'الدفعة الدراسية',
            'installmentPlanId' => 'خطة التقسيط',
        ]);

        if (! $moyasar->isConfigured()) {
            // السماح بإكمال التسجيل دون دفع إلكتروني؛ يظهر في CRM لمتابعة التحويل البنكي.
        }

        try {
            $result = $installments->start(
                auth()->user(),
                (int) $this->batchId,
                (int) $this->installmentPlanId,
                $this->registrationProfile(),
            );
        } catch (ValidationException $exception) {
            $this->flowError = collect($exception->errors())->flatten()->first() ?? 'تعذر إكمال التسجيل.';

            return;
        }

        $locale = app()->getLocale();
        $contract = $result['contract'];

        if ($contract->needsStudentSignature()) {
            $this->redirect(route('installments.show', ['locale' => $locale, 'contract' => $contract->id]), navigate: true);

            return;
        }

        if ($result['order'] && $moyasar->isConfigured()) {
            $this->redirect(route('installments.pay', [
                'locale' => $locale,
                'contract' => $contract->id,
                'schedule' => $result['schedule']->id,
                'order' => $result['order']->reference,
            ]), navigate: true);

            return;
        }

        session()->flash(
            'portal_message',
            $moyasar->isConfigured()
                ? 'تم تسجيل طلبك. أكمل السداد من صفحة الأقساط، أو انتظر تواصل فريق المبيعات إن اخترت التحويل البنكي.'
                : 'تم تسجيلك بانتظار تأكيد السداد. سيتواصل معك فريق المبيعات لتأكيد التحويل البنكي ثم تُفعَّل دراستك في البرنامج.'
        );

        $this->redirect(route('installments.show', ['locale' => $locale, 'contract' => $contract->id]), navigate: true);
    }

    protected function profileComplete(): bool
    {
        return filled($this->nationality)
            && filled($this->city)
            && filled($this->gender)
            && filled($this->employmentStatus);
    }

    /** @return array<string, string> */
    protected function registrationProfile(): array
    {
        return array_filter([
            'gender' => $this->gender ?: null,
            'city' => $this->city ?: null,
            'nationality' => $this->nationality ?: null,
            'employment_status' => $this->employmentStatus ?: null,
            'study_period' => $this->studyPeriod ?: null,
        ]);
    }
};
?>

@php
    $locale = app()->getLocale();
    $t = fn (string $key) => \App\Support\PublicCopy::register($key, $locale);
    $registration = app(AcademicRegistrationService::class);
    $batches = $academicFlow ? $registration->openBatches() : collect();
    $plans = $academicFlow ? app(InstallmentAcademicRegistrationService::class)->availablePlans() : collect();
    $selectedBatch = $batchId ? AcademicBatch::query()->with('program')->find($batchId) : null;
    $steps = [
        1 => $t('step_profile'),
        2 => $t('step_program'),
        3 => $t('step_plan'),
    ];
@endphp

<div class="portal-bg-pattern auth-screen py-4 py-lg-5">
    <div class="container auth-container {{ $academicFlow ? 'auth-container--register' : '' }}">
        <div class="auth-card portal-card card border-0 overflow-hidden auth-register">
            <div class="row g-0">

                <div class="col-lg-4 d-none d-lg-block">
                    <div class="auth-side h-100">
                        <div class="auth-side__glow auth-side__glow--1"></div>
                        <div class="auth-side__glow auth-side__glow--2"></div>
                        <div class="auth-side__content">
                            @if (\App\Support\LogoSettings::isVisible(\App\Support\LogoSettings::KEY_PRIMARY))
                                <img src="{{ platform_logo_url(\App\Support\LogoSettings::KEY_PRIMARY) }}" alt="" class="auth-side__logo">
                            @endif
                            <span class="auth-side__eyebrow">{{ $t('eyebrow') }}</span>
                            <h2 class="auth-side__title">{{ $t('side_title') }}</h2>
                            <p class="auth-side__text">{{ $t('side_text') }}</p>
                            <ul class="auth-side__features">
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-graduation-cap"></i></span>
                                    <span>{{ $t('feat_programs') }}</span>
                                </li>
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-certificate"></i></span>
                                    <span>{{ $t('feat_certificate') }}</span>
                                </li>
                                @if ($academicFlow)
                                    <li>
                                        <span class="auth-side__icon"><i class="fa-solid fa-layer-group"></i></span>
                                        <span>{{ $t('feat_installments') }}</span>
                                    </li>
                                @endif
                                <li>
                                    <span class="auth-side__icon"><i class="fa-solid fa-headset"></i></span>
                                    <span>{{ $t('feat_support') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="auth-form-pane p-4 p-md-5">
                        <span class="auth-form-pane__eyebrow">
                            <i class="fa-solid fa-user-plus"></i>
                            {{ $academicFlow ? ($steps[$step] ?? $t('title_academic')) : $t('title_account') }}
                        </span>
                        <h1 class="h4 fw-bold mb-1">
                            {{ $academicFlow ? $t('title_academic') : $t('title_account') }}
                        </h1>
                        <p class="text-muted small mb-4">
                            {{ $academicFlow ? $t('lead_academic') : $t('lead_account') }}
                        </p>

                        @if ($academicFlow)
                            <ol class="auth-stepper" aria-label="{{ $t('lead_academic') }}">
                                @foreach ($steps as $n => $label)
                                    <li @class(['auth-stepper__item', 'is-active' => $step === $n, 'is-done' => $step > $n])>
                                        <span class="auth-stepper__num">{{ $step > $n ? '✓' : $n }}</span>
                                        <span class="auth-stepper__label">{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        @if ($flowError)
                            <div class="alert alert-warning d-flex align-items-center gap-2 py-2 small" role="alert">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $flowError }}</span>
                            </div>
                        @endif

                        @if ($step === 1)
                            @if (auth()->check())
                            <form wire:submit="continueProfile" novalidate>
                            @else
                            <form wire:submit="createAccount" novalidate>
                            @endif
                                <section class="auth-section">
                                    <h2 class="auth-section__title"><i class="fa-regular fa-id-card"></i> {{ $t('section_identity') }}</h2>

                                    <div class="mb-3">
                                        <label class="form-label auth-label" for="reg-name">{{ $t('name') }} <span class="text-danger">*</span></label>
                                        <div class="auth-field input-group input-group-lg">
                                            <span class="input-group-text auth-field__icon"><i class="fa-regular fa-user"></i></span>
                                            <input type="text" class="form-control @error('nameAr') is-invalid @enderror" id="reg-name" wire:model="nameAr" autocomplete="name" @disabled(auth()->check())>
                                        </div>
                                        @error('nameAr')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label auth-label" for="reg-nid">{{ $t('national_id') }} <span class="text-danger">*</span></label>
                                            <div class="auth-field input-group input-group-lg">
                                                <span class="input-group-text auth-field__icon"><i class="fa-regular fa-id-card"></i></span>
                                                <input type="text" class="form-control @error('nationalId') is-invalid @enderror" id="reg-nid" wire:model="nationalId" inputmode="numeric" maxlength="10" autocomplete="off" @disabled(auth()->check())>
                                            </div>
                                            <span class="auth-hint">{{ $t('national_id_hint') }}</span>
                                            @error('nationalId')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label auth-label" for="reg-mobile">{{ $t('phone') }} <span class="text-danger">*</span></label>
                                            <div class="auth-field input-group input-group-lg">
                                                <span class="input-group-text auth-field__prefix" dir="ltr">🇸🇦 +966</span>
                                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="reg-mobile" wire:model="phone" inputmode="numeric" placeholder="5xxxxxxxx" autocomplete="tel" @disabled(auth()->check())>
                                            </div>
                                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </section>

                                <section class="auth-section">
                                    <h2 class="auth-section__title"><i class="fa-regular fa-envelope"></i> {{ $t('section_contact') }}</h2>
                                    <div class="mb-0">
                                        <label class="form-label auth-label" for="reg-email">{{ $t('email') }} <span class="text-danger">*</span></label>
                                        <div class="auth-field input-group input-group-lg">
                                            <span class="input-group-text auth-field__icon"><i class="fa-regular fa-envelope"></i></span>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="reg-email" wire:model="email" dir="ltr" autocomplete="email" placeholder="name@example.com" @disabled(auth()->check())>
                                        </div>
                                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </section>

                                @if ($academicFlow)
                                    <section class="auth-section">
                                        <h2 class="auth-section__title"><i class="fa-solid fa-graduation-cap"></i> {{ $t('section_profile') }}</h2>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label auth-label" for="reg-nat">{{ $t('nationality') }} <span class="text-danger">*</span></label>
                                                <select id="reg-nat" class="form-select form-select-lg @error('nationality') is-invalid @enderror" wire:model="nationality">
                                                    <option value="">{{ $t('choose_nationality') }}</option>
                                                    @foreach (AcademicRegistrationOptions::nationalities($locale) as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('nationality')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label auth-label" for="reg-city">{{ $t('city') }} <span class="text-danger">*</span></label>
                                                <select id="reg-city" class="form-select form-select-lg @error('city') is-invalid @enderror" wire:model="city">
                                                    <option value="">{{ $t('choose_city') }}</option>
                                                    @foreach (AcademicRegistrationOptions::cities($locale) as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error('city')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <span class="form-label auth-label d-block">{{ $t('gender') }} <span class="text-danger">*</span></span>
                                            <div class="auth-choice-grid">
                                                @foreach (AcademicRegistrationOptions::genders($locale) as $value => $label)
                                                    <label class="auth-choice">
                                                        <input type="radio" wire:model="gender" value="{{ $value }}">
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('gender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3">
                                            <span class="form-label auth-label d-block">{{ $t('employment') }} <span class="text-danger">*</span></span>
                                            <div class="auth-choice-grid">
                                                @foreach (AcademicRegistrationOptions::employmentStatuses($locale) as $value => $label)
                                                    <label class="auth-choice">
                                                        <input type="radio" wire:model="employmentStatus" value="{{ $value }}">
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('employmentStatus')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-0">
                                            <span class="form-label auth-label d-block">{{ $t('study_period') }} <span class="text-danger">*</span></span>
                                            <div class="auth-choice-grid">
                                                @foreach (AcademicRegistrationOptions::studyPeriods($locale) as $value => $label)
                                                    <label class="auth-choice">
                                                        <input type="radio" wire:model="studyPeriod" value="{{ $value }}">
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </section>
                                @endif

                                @unless(auth()->check())
                                    <section class="auth-section">
                                        <h2 class="auth-section__title"><i class="fa-solid fa-lock"></i> {{ $t('section_security') }}</h2>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label auth-label" for="reg-pass">{{ $t('password') }} <span class="text-danger">*</span></label>
                                                <div class="auth-field input-group input-group-lg">
                                                    <span class="input-group-text auth-field__icon"><i class="fa-solid fa-lock"></i></span>
                                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="reg-pass" wire:model="password" autocomplete="new-password">
                                                    <button type="button" class="btn auth-field__toggle" data-password-toggle="reg-pass" aria-label="{{ $t('show_password') }}" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                                </div>
                                                <span class="auth-hint">{{ $t('password_hint') }}</span>
                                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label auth-label" for="reg-pass2">{{ $t('password_confirm') }} <span class="text-danger">*</span></label>
                                                <div class="auth-field input-group input-group-lg">
                                                    <span class="input-group-text auth-field__icon"><i class="fa-solid fa-lock"></i></span>
                                                    <input type="password" class="form-control @error('passwordConfirmation') is-invalid @enderror" id="reg-pass2" wire:model="passwordConfirmation" autocomplete="new-password">
                                                    <button type="button" class="btn auth-field__toggle" data-password-toggle="reg-pass2" aria-label="{{ $t('show_password') }}" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
                                                </div>
                                                @error('passwordConfirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </section>
                                @endunless

                                <div class="form-check auth-terms mb-4">
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" wire:model="terms">
                                    <label class="form-check-label small" for="terms">
                                        {{ $t('terms_prefix') }}
                                        <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'terms-and-conditions']) }}" target="_blank" rel="noopener">{{ $t('terms') }}</a>
                                        {{ $t('and') }}
                                        <a href="{{ route('cms.page', ['locale' => $locale, 'slug' => 'privacy-policy']) }}" target="_blank" rel="noopener">{{ $t('privacy') }}</a>.
                                    </label>
                                    @error('terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 auth-submit" wire:loading.attr="disabled">
                                    @if (auth()->check())
                                        <span wire:loading.remove wire:target="continueProfile">{{ $t('continue_program') }}</span>
                                        <span wire:loading wire:target="continueProfile"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>{{ $t('processing') }}</span>
                                    @else
                                        <span wire:loading.remove wire:target="createAccount">
                                            {{ $academicFlow ? $t('continue_program') : $t('create_account') }}
                                        </span>
                                        <span wire:loading wire:target="createAccount"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>{{ $t('processing') }}</span>
                                    @endif
                                </button>
                                <p class="auth-secure"><i class="fa-solid fa-shield-halved"></i> {{ $t('secure_note') }}</p>
                            </form>
                        @elseif ($step === 2)
                            <h2 class="h6 fw-bold mb-1">{{ $t('choose_batch') }}</h2>
                            <p class="text-muted small mb-4">{{ $t('choose_batch_lead') }}</p>

                            @if ($batches->isEmpty())
                                <div class="alert alert-light border small mb-3">
                                    {{ $t('no_batches') }}
                                    <a href="{{ route('contact', ['locale' => $locale]) }}" class="fw-semibold">{{ $t('contact_us') }}</a>
                                </div>
                            @else
                                <div class="auth-batch-grid mb-4">
                                    @foreach ($batches as $batch)
                                        @php
                                            $programName = ($locale === 'en' && filled($batch->program?->name_en))
                                                ? $batch->program->name_en
                                                : ($batch->program?->name_ar ?? $batch->name);
                                            $seats = $batch->availableSeats();
                                        @endphp
                                        <button type="button" @class(['auth-batch-card', 'is-selected' => (int) $batchId === $batch->id]) wire:click="selectBatch({{ $batch->id }})">
                                            <span class="auth-batch-card__program">{{ $programName }}</span>
                                            <span class="auth-batch-card__meta">{{ $batch->name }}@if ($batch->displaySemester()) · {{ $batch->displaySemester() }}@endif</span>
                                            @if ($batch->start_date)
                                                <span class="auth-batch-card__meta">{{ $batch->start_date->translatedFormat('Y/m/d') }}</span>
                                            @endif
                                            <span class="auth-batch-card__fee">{{ number_format((float) $batch->tuition_amount, 0) }} {{ $t('sar') }}</span>
                                            @if ($seats !== null)
                                                <span class="auth-batch-card__seats">{{ $seats }} {{ $t('seats') }}</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            @if (auth()->check())
                                <button type="button" class="btn btn-outline-secondary" wire:click="back">{{ $t('back') }}</button>
                            @endif
                        @else
                            <h2 class="h6 fw-bold mb-1">{{ $t('plan_title') }}</h2>
                            <p class="text-muted small mb-3">{{ $t('plan_lead') }}</p>

                            @if ($selectedBatch)
                                @php
                                    $selectedName = ($locale === 'en' && filled($selectedBatch->program?->name_en))
                                        ? $selectedBatch->program->name_en
                                        : ($selectedBatch->program?->name_ar ?? $selectedBatch->name);
                                @endphp
                                <div class="auth-summary">
                                    <div>
                                        <span>{{ $t('selected_program') }}</span>
                                        <strong>{{ $selectedName }}</strong>
                                        <span>{{ $selectedBatch->name }}</span>
                                    </div>
                                    <div class="text-lg-end">
                                        <span>{{ $t('sar') }}</span>
                                        <strong>{{ number_format((float) $selectedBatch->tuition_amount, 2) }}</strong>
                                    </div>
                                </div>
                            @endif

                            <div class="auth-plan-list mb-4">
                                @foreach ($plans as $plan)
                                    @php
                                        $planName = ($locale === 'en' && filled($plan->name_en)) ? $plan->name_en : $plan->name_ar;
                                        $preview = $selectedBatch
                                            ? $plan->schedulePreview((float) $selectedBatch->tuition_amount)
                                            : [];
                                        $first = collect($preview)->first();
                                    @endphp
                                    <label class="auth-plan {{ (int) $installmentPlanId === $plan->id ? 'is-selected' : '' }}">
                                        <input type="radio" wire:model.live="installmentPlanId" value="{{ $plan->id }}">
                                        <span>
                                            <strong>{{ $planName }}</strong>
                                            <small>
                                                {{ $plan->description_ar }}
                                                — {{ $plan->items->count() }} {{ $t('payments') }}
                                                @if ($first)
                                                    · {{ $t('first_payment') }}: {{ number_format((float) $first['amount'], 0) }} {{ $t('sar') }}
                                                @endif
                                            </small>
                                            @if ($preview !== [])
                                                <ul class="auth-plan__schedule">
                                                    @foreach ($preview as $row)
                                                        <li>{{ $row['label'] }} · {{ number_format((float) $row['amount'], 0) }} {{ $t('sar') }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('installmentPlanId')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                            <div class="alert auth-note small mb-4">{{ $t('plan_note') }}</div>

                            <div class="auth-actions">
                                <button type="button" class="btn btn-outline-secondary" wire:click="back">{{ $t('back') }}</button>
                                <button type="button" class="btn btn-primary flex-grow-1 auth-submit" wire:click="submitEnrollment" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitEnrollment">{{ $t('confirm') }}</span>
                                    <span wire:loading wire:target="submitEnrollment"><span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>{{ $t('processing') }}</span>
                                </button>
                            </div>
                        @endif

                        @if ($step === 1 && ! auth()->check())
                            <div class="auth-alt">
                                <p class="auth-alt__title mb-0">{{ $t('alt_title') }}</p>
                                <p class="auth-alt__lead">{{ $t('alt_lead') }}</p>
                                <div class="auth-alt__grid">
                                    <a class="auth-alt__link" href="{{ route('apply.form', ['locale' => $locale, 'type' => 'client']) }}">{{ $t('alt_client') }}</a>
                                    <a class="auth-alt__link" href="{{ route('apply.form', ['locale' => $locale, 'type' => 'company']) }}">{{ $t('alt_company') }}</a>
                                    <a class="auth-alt__link" href="{{ route('apply.form', ['locale' => $locale, 'type' => 'instructor']) }}">{{ $t('alt_instructor') }}</a>
                                </div>
                            </div>
                        @endif

                        <p class="auth-login-row small text-muted">
                            {{ $t('have_account') }}
                            <a href="{{ route('login', ['locale' => $locale]) }}" class="fw-semibold text-primary text-decoration-none">{{ $t('login_link') }}</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-screen.css') }}?v=1">
@endpush

@push('scripts')
    <script src="{{ static_asset('assets/portal-shell.js') }}" defer></script>
@endpush

