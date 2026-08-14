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
    $registration = app(AcademicRegistrationService::class);
    $batches = $academicFlow ? $registration->openBatches() : collect();
    $plans = $academicFlow ? app(InstallmentAcademicRegistrationService::class)->availablePlans() : collect();
    $selectedBatch = $batchId ? AcademicBatch::query()->with('program')->find($batchId) : null;
    $totalSteps = $academicFlow ? 3 : 1;
@endphp

<div class="portal-bg-pattern py-4 py-md-5">
    <div class="container portal-auth-wide">
        <div class="portal-card card border-0 p-4 p-md-5">
            <h1 class="h4 fw-bold mb-2">
                @if ($academicFlow)
                    التسجيل في البرامج المعتمدة
                @else
                    إنشاء حساب جديد
                @endif
            </h1>
            <p class="text-muted small mb-4">
                @if ($academicFlow)
                    أكمل بياناتك، اختر البرنامج وخطة التقسيط، ثم وقّع العقد وسدّد الدفعة الأولى.
                @else
                    أنشئ حسابك للوصول إلى الدورات وطلبات الشراء.
                @endif
            </p>

            @if ($academicFlow)
                <div class="portal-reg-steps mb-4">
                    @foreach ([1 => 'بياناتك', 2 => 'البرنامج', 3 => 'التقسيط'] as $n => $label)
                        <span @class(['portal-reg-step', 'is-active' => $step === $n, 'is-done' => $step > $n])>
                            <span class="portal-reg-step__num">{{ $n }}</span>
                            {{ $label }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if ($flowError)
                <div class="alert alert-warning">{{ $flowError }}</div>
            @endif

            @if ($step === 1)
                <h2 class="h6 fw-bold border-bottom pb-2 mb-4">المعلومات الأساسية</h2>

                @if (auth()->check())
                <form wire:submit="continueProfile" novalidate>
                @else
                <form wire:submit="createAccount" novalidate>
                @endif
                    <div class="mb-3">
                        <label class="form-label" for="reg-name">الاسم بالكامل (عربي) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg @error('nameAr') is-invalid @enderror" id="reg-name" wire:model="nameAr" autocomplete="name" @disabled(auth()->check())>
                        @error('nameAr')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="reg-nid">رقم الهوية <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg @error('nationalId') is-invalid @enderror" id="reg-nid" wire:model="nationalId" inputmode="numeric" @disabled(auth()->check())>
                            @error('nationalId')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="reg-mobile">رقم الجوال <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">🇸🇦 +966</span>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="reg-mobile" wire:model="phone" inputmode="numeric" placeholder="5xxxxxxxx" @disabled(auth()->check())>
                            </div>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label" for="reg-email">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="reg-email" wire:model="email" dir="ltr" @disabled(auth()->check())>
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    @if ($academicFlow)
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="reg-nat">الجنسية <span class="text-danger">*</span></label>
                                <select id="reg-nat" class="form-select form-select-lg @error('nationality') is-invalid @enderror" wire:model="nationality">
                                    <option value="">اختر الجنسية</option>
                                    @foreach (AcademicRegistrationOptions::nationalities() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('nationality')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reg-city">المدينة <span class="text-danger">*</span></label>
                                <select id="reg-city" class="form-select form-select-lg @error('city') is-invalid @enderror" wire:model="city">
                                    <option value="">اختر المدينة</option>
                                    @foreach (AcademicRegistrationOptions::cities() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('city')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <span class="form-label d-block">الجنس <span class="text-danger">*</span></span>
                            <div class="d-flex gap-4 flex-wrap">
                                @foreach (['ذكر', 'أنثى'] as $g)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="gender" id="g-{{ $g }}" value="{{ $g }}">
                                        <label class="form-check-label" for="g-{{ $g }}">{{ $g }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('gender')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <span class="form-label d-block">الحالة الوظيفية <span class="text-danger">*</span></span>
                            <div class="d-flex gap-4 flex-wrap">
                                @foreach (AcademicRegistrationOptions::employmentStatuses() as $value => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="employmentStatus" id="emp-{{ $value }}" value="{{ $value }}">
                                        <label class="form-check-label" for="emp-{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('employmentStatus')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <span class="form-label d-block">فترة الدراسة <span class="text-danger">*</span></span>
                            @foreach (AcademicRegistrationOptions::studyPeriods() as $value => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model="studyPeriod" id="study-{{ $value }}" value="{{ $value }}">
                                    <label class="form-check-label" for="study-{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @unless(auth()->check())
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="reg-pass">كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="reg-pass" wire:model="password" autocomplete="new-password">
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="reg-pass2">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg" id="reg-pass2" wire:model="passwordConfirmation" autocomplete="new-password">
                            </div>
                        </div>
                    @endunless

                    <div class="form-check mb-4">
                        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" wire:model="terms">
                        <label class="form-check-label small" for="terms">أوافق على الشروط والأحكام وسياسة الخصوصية.</label>
                        @error('terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                        @if (auth()->check())
                            <span wire:loading.remove wire:target="continueProfile">متابعة — اختيار البرنامج</span>
                            <span wire:loading wire:target="continueProfile">جاري المعالجة…</span>
                        @else
                            <span wire:loading.remove wire:target="createAccount">
                                {{ $academicFlow ? 'متابعة — اختيار البرنامج' : 'إنشاء الحساب' }}
                            </span>
                            <span wire:loading wire:target="createAccount">جاري المعالجة…</span>
                        @endif
                    </button>
                </form>
            @elseif ($step === 2)
                <h2 class="h6 fw-bold border-bottom pb-2 mb-4">اختر الدفعة الدراسية</h2>

                @if ($batches->isEmpty())
                    <p class="text-muted">لا توجد دفعات مفتوحة للتسجيل حالياً.</p>
                @else
                    <div class="portal-inst-batch-grid mb-3">
                        @foreach ($batches as $batch)
                            <button type="button" class="portal-inst-batch-card" wire:click="selectBatch({{ $batch->id }})">
                                <strong>{{ $batch->program?->name_ar ?? $batch->name }}</strong>
                                <span>{{ $batch->name }}</span>
                                <span class="portal-inst-batch-card__fee">{{ number_format((float) $batch->tuition_amount, 0) }} ر.س</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if (auth()->check())
                    <button type="button" class="btn btn-link" wire:click="back">رجوع</button>
                @endif
            @else
                <h2 class="h6 fw-bold border-bottom pb-2 mb-4">خطة التقسيط والتأكيد</h2>

                @if ($selectedBatch)
                    <p class="text-muted small mb-3">
                        {{ $selectedBatch->program?->name_ar }} — {{ $selectedBatch->name }}
                        · {{ number_format((float) $selectedBatch->tuition_amount, 2) }} ر.س
                    </p>
                @endif

                <div class="portal-inst-plan-list mb-4">
                    @foreach ($plans as $plan)
                        <label class="portal-inst-plan-option {{ (int) $installmentPlanId === $plan->id ? 'is-selected' : '' }}">
                            <input type="radio" wire:model.live="installmentPlanId" value="{{ $plan->id }}">
                            <span>
                                <strong>{{ $plan->name_ar }}</strong>
                                <small>{{ $plan->description_ar }} — {{ $plan->items->count() }} دفعات</small>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('installmentPlanId')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                <div class="alert alert-info small">بعد التأكيد ستوقّع على عقد التقسيط إلكترونياً ثم تسدّد الدفعة الأولى.</div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="back">رجوع</button>
                    <button type="button" class="btn btn-primary flex-grow-1" wire:click="submitEnrollment" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitEnrollment">تأكيد التسجيل والمتابعة</span>
                        <span wire:loading wire:target="submitEnrollment">جاري المعالجة…</span>
                    </button>
                </div>
            @endif

            <p class="text-center mt-4 mb-0 small text-muted">
                لديك حساب بالفعل؟
                <a href="{{ route('login', ['locale' => $locale]) }}" class="fw-semibold text-primary">تسجيل الدخول</a>
            </p>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ static_asset('css/portal-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-dashboard.css') }}">
    <style>
        .portal-auth-wide { max-width: 720px; }
        .portal-reg-steps { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .portal-reg-step { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: #94a3b8; padding: 0.35rem 0.65rem; border-radius: 999px; background: #f8fafc; }
        .portal-reg-step.is-active { color: #0d9488; background: #f0fdfa; font-weight: 600; }
        .portal-reg-step.is-done { color: #64748b; }
        .portal-reg-step__num { width: 1.25rem; height: 1.25rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: #e2e8f0; font-size: 0.7rem; }
        .portal-reg-step.is-active .portal-reg-step__num { background: #0d9488; color: #fff; }
    </style>
@endpush

@push('scripts')
    <script src="{{ static_asset('assets/portal-shell.js') }}" defer></script>
@endpush
