<?php

use App\Models\AcademicProgram;
use App\Services\AcademicRequestService;
use App\Support\AcademicRequestOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تقديم طلب | مركز التعلم المستمر')]
class extends Component
{
    public string $type = 'deferral';

    public string $reason = '';

    public string $semester_key = '';

    public string $target_semester_key = '';

    public string $payment_method = 'دفع إلكتروني';

    public ?int $new_program_id = null;

    public ?string $flashMessage = null;

    public function mount(string $type): void
    {
        abort_unless(array_key_exists($type, AcademicRequestOptions::types()), 404);

        $this->type = $type;

        $student = app(AcademicRequestService::class)->resolveStudent(auth()->user());
        abort_unless($student, 403);

        $semesters = AcademicRequestOptions::semesters();
        $this->semester_key = $semesters[0]['key'] ?? '';
        $this->target_semester_key = $semesters[1]['key'] ?? $this->semester_key;
    }

    #[Computed]
    public function student()
    {
        return app(AcademicRequestService::class)->resolveStudent(auth()->user());
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get();
    }

    public function submit(): void
    {
        $rules = [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];

        if ($this->type === 'deferral') {
            $rules['semester_key'] = ['required', 'string'];
            $rules['target_semester_key'] = ['required', 'string', 'different:semester_key'];
        }

        if ($this->type === 'semester_excuse') {
            $rules['semester_key'] = ['required', 'string'];
        }

        if ($this->type === 'program_change') {
            $rules['new_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
        }

        if ($this->type === 'withdrawal') {
            $rules['payment_method'] = ['required', 'string'];
        }

        $this->validate($rules, [], [
            'reason' => 'السبب',
            'semester_key' => 'الفصل الحالي',
            'target_semester_key' => 'الفصل المطلوب التأجيل إليه',
            'new_program_id' => 'البرنامج الجديد',
            'payment_method' => 'طريقة الدفع',
        ]);

        $request = app(AcademicRequestService::class)->submit(auth()->user(), $this->type, [
            'reason' => $this->reason,
            'semester_key' => $this->semester_key,
            'target_semester_key' => $this->target_semester_key,
            'payment_method' => $this->payment_method,
            'new_program_id' => $this->new_program_id,
        ]);

        $this->redirectRoute('user-requests.show', [
            'locale' => app()->getLocale(),
            'academicRequest' => $request,
        ], navigate: true);
    }
};
?>

@php
    $locale = app()->getLocale();
    $student = $this->student;
    $program = $student?->batch?->program;
@endphp

@include('partials.portal.shell-start', [
    'portalActive' => 'user-requests',
    'portalTitle' => AcademicRequestOptions::studentSingularLabel($type),
])

<div class="portal-dashboard portal-user-request-form-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ AcademicRequestOptions::studentSingularLabel($type) }}</h1>
            <p class="portal-orders-intro__desc">{{ AcademicRequestOptions::studentSubmitDescription($type) }}</p>
        </div>
        <a href="{{ route('user-requests', ['locale' => $locale]) }}" class="portal-btn-secondary">← العودة</a>
    </div>

    @if ($student)
        <div class="portal-ur-student-card">
            <div><strong>{{ $student->name_ar }}</strong></div>
            <div class="portal-ur-student-card__meta">
                @if ($student->academic_id)<span>الرقم الأكاديمي: {{ $student->academic_id }}</span>@endif
                @if ($program)<span>{{ $program->name_ar }}</span>@endif
            </div>
        </div>
    @endif

    <form class="portal-ur-form" wire:submit="submit">
        @if (in_array($type, ['deferral', 'semester_excuse'], true))
            <div class="mb-3">
                <label class="form-label">{{ $type === 'deferral' ? 'فصل القبول / الفصل الحالي' : 'الفصل الدراسي' }}</label>
                <select class="form-select" wire:model="semester_key">
                    @foreach (AcademicRequestOptions::semesters() as $sem)
                        <option value="{{ $sem['key'] }}">{{ $sem['label'] }}</option>
                    @endforeach
                </select>
                @error('semester_key')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @endif

        @if ($type === 'deferral')
            <div class="mb-3">
                <label class="form-label">الفصل المطلوب التأجيل إليه</label>
                <select class="form-select" wire:model="target_semester_key">
                    @foreach (AcademicRequestOptions::semesters() as $sem)
                        <option value="{{ $sem['key'] }}">{{ $sem['label'] }}</option>
                    @endforeach
                </select>
                @error('target_semester_key')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @endif

        @if ($type === 'program_change')
            <div class="mb-3">
                <label class="form-label">البرنامج الحالي</label>
                <input type="text" class="form-control" value="{{ $program?->name_ar ?? '—' }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">البرنامج المطلوب</label>
                <select class="form-select @error('new_program_id') is-invalid @enderror" wire:model="new_program_id">
                    <option value="">اختر البرنامج...</option>
                    @foreach ($this->programs as $prog)
                        @if ($prog->id !== $program?->id)
                            <option value="{{ $prog->id }}">{{ $prog->name_ar }}</option>
                        @endif
                    @endforeach
                </select>
                @error('new_program_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @endif

        @if ($type === 'withdrawal')
            <div class="mb-3">
                <label class="form-label">طريقة استرداد المبلغ (إن وُجد)</label>
                <select class="form-select" wire:model="payment_method">
                    @foreach (AcademicRequestOptions::paymentMethods() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">سبب الطلب</label>
            <textarea class="form-control @error('reason') is-invalid @enderror" rows="5" wire:model="reason" placeholder="اشرح سبب الطلب بوضوح..."></textarea>
            @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('student')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="portal-ur-form__actions">
            <button type="submit" class="portal-btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit"><i class="fa-solid fa-paper-plane"></i> إرسال الطلب</span>
                <span wire:loading wire:target="submit">جاري الإرسال...</span>
            </button>
        </div>
    </form>
</div>

@include('partials.portal.shell-end')
