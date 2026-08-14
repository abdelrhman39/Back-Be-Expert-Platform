<?php

use App\Models\RegistrationApplication;
use App\Support\RegistrationApplicationOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('متابعة الطلب | مركز التعلم المستمر')]
class extends Component
{
    public string $applicationNo = '';

    public string $email = '';

    public ?RegistrationApplication $application = null;

    public ?string $lookupError = null;

    public function mount(?string $application = null): void
    {
        if ($application) {
            $this->applicationNo = $application;
        }

        if (auth()->check()) {
            $this->email = auth()->user()->email ?? '';
        }
    }

    public function lookup(): void
    {
        $this->validate([
            'applicationNo' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email'],
        ], [], [
            'applicationNo' => 'رقم الطلب',
            'email' => 'البريد الإلكتروني',
        ]);

        $found = RegistrationApplication::query()
            ->where('application_no', strtoupper(trim($this->applicationNo)))
            ->where('applicant_email', strtolower(trim($this->email)))
            ->first();

        if (! $found) {
            $this->application = null;
            $this->lookupError = 'لم يُعثر على طلب مطابق. تحقق من الرقم والبريد.';

            return;
        }

        $this->application = $found;
        $this->lookupError = null;
    }

    public function resetLookup(): void
    {
        $this->application = null;
        $this->lookupError = null;
    }
};
?>

@php $locale = app()->getLocale(); @endphp

<div class="page-content">
    <div class="container py-5">
        <h1 class="mb-4">متابعة طلب التسجيل</h1>

        @if (! $application)
            <div class="blog-form" style="max-width: 520px;">
                @if ($lookupError)
                    <div class="alert alert-warning">{{ $lookupError }}</div>
                @endif
                <form wire:submit="lookup">
                    <div class="form-group mb-3">
                        <label class="form-label">رقم الطلب *</label>
                        <input type="text" class="form-control" wire:model="applicationNo" dir="ltr" placeholder="APP-20260608-XXXX">
                        @error('applicationNo') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">البريد الإلكتروني *</label>
                        <input type="email" class="form-control" wire:model="email" dir="ltr">
                        @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">بحث</button>
                </form>
            </div>
        @else
            <div class="blog-form">
                <div class="card">
                    <div class="card-body">
                        <h4>{{ $application->typeLabel() }}</h4>
                        <p class="text-muted mb-3">رقم الطلب: <code dir="ltr">{{ $application->application_no }}</code></p>

                        <dl class="row mb-0">
                            <dt class="col-sm-4">الحالة</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ $application->statusLabel() }}
                                </span>
                            </dd>
                            <dt class="col-sm-4">تاريخ التقديم</dt>
                            <dd class="col-sm-8">{{ $application->submitted_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                            <dt class="col-sm-4">الاسم</dt>
                            <dd class="col-sm-8">{{ $application->applicant_name }}</dd>
                            <dt class="col-sm-4">البريد</dt>
                            <dd class="col-sm-8" dir="ltr">{{ $application->applicant_email }}</dd>
                            @if ($application->reviewed_at)
                                <dt class="col-sm-4">تاريخ المراجعة</dt>
                                <dd class="col-sm-8">{{ $application->reviewed_at->format('Y-m-d H:i') }}</dd>
                            @endif
                            @if ($application->admin_notes && in_array($application->status, ['approved', 'rejected'], true))
                                <dt class="col-sm-4">ملاحظات</dt>
                                <dd class="col-sm-8">{{ $application->admin_notes }}</dd>
                            @endif
                        </dl>

                        @if ($application->payload)
                            <hr>
                            <h5 class="mb-3">تفاصيل الطلب</h5>
                            <dl class="row">
                                @foreach (RegistrationApplicationOptions::fieldsFor($application->type) as $field)
                                    @if ($field['type'] === 'file')
                                        @continue
                                    @endif
                                    @php $val = $application->payloadValue($field['key']); @endphp
                                    @if (filled($val))
                                        <dt class="col-sm-4">{{ $field['label'] }}</dt>
                                        <dd class="col-sm-8">
                                            @if (isset($field['options'][$val]))
                                                {{ $field['options'][$val] }}
                                            @else
                                                {{ is_array($val) ? implode(', ', $val) : $val }}
                                            @endif
                                        </dd>
                                    @endif
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary mt-3" wire:click="resetLookup">بحث عن طلب آخر</button>
            </div>
        @endif
    </div>
</div>
