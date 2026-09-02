<?php

use App\Services\SupportTicketService;
use App\Support\SupportTicketOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('إنشاء تذكرة | مركز التعلم المستمر')]
class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $nationalId = '';

    public string $phone = '';

    public string $category = '';

    public string $specialization = '';

    public string $subject = '';

    public string $body = '';

    public ?string $createdReference = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->name = $user->displayName();
            $this->email = $user->email ?? '';
            $this->nationalId = $user->national_id ?? '';
            $this->phone = $user->phone ?? '';
        }
    }

    public function submit(SupportTicketService $service): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nationalId' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'category' => ['required', 'in:'.implode(',', array_keys(SupportTicketOptions::categories()))],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'category' => 'الفئة',
            'subject' => 'الموضوع',
            'body' => 'الوصف',
        ]);

        $body = $validated['body'];

        if (filled($this->specialization)) {
            $body = "التخصص: {$this->specialization}\n\n".$body;
        }

        $ticket = $service->create([
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'] ?: null,
            'contact_national_id' => $validated['nationalId'] ?: null,
            'body' => $body,
        ], auth()->user());

        $service->grantGuestAccess($ticket);
        $this->createdReference = $ticket->reference_code;
    }
};
?>

@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
@endphp

<div class="support-page">
    @include('partials.support.nav', ['active' => 'new'])

    <div class="container support-page__container">
        @if ($createdReference)
            <div class="support-success">
                <div class="support-success__icon" aria-hidden="true">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h1>{{ $isEn ? 'Your ticket was sent' : 'تم إرسال تذكرتك بنجاح' }}</h1>
                <p>{{ $isEn ? 'Ticket number' : 'رقم التذكرة' }}</p>
                <code class="support-success__ref" dir="ltr">{{ $createdReference }}</code>
                <p class="support-success__hint">
                    {{ $isEn
                        ? 'Save this number. You will need it with your email to follow up.'
                        : 'احفظ الرقم — ستحتاجه مع بريدك لمتابعة التذكرة.' }}
                </p>
                <div class="support-success__actions">
                    <a href="{{ route('support.ticket.view', ['locale' => $locale, 'ticket' => $createdReference]) }}" class="btn btn-primary">{{ $isEn ? 'Open ticket' : 'متابعة التذكرة' }}</a>
                    <a href="{{ route('support.ticket.search', ['locale' => $locale]) }}" class="btn btn-outline-secondary">{{ $isEn ? 'Find later' : 'بحث لاحقاً' }}</a>
                </div>
            </div>
        @else
            <header class="support-head">
                <span class="support-head__eyebrow">{{ platform_org() }}</span>
                <h1 class="support-page__title">{{ $isEn ? 'Create a support ticket' : 'إنشاء تذكرة دعم' }}</h1>
                <p class="support-page__lead">
                    {{ $isEn
                        ? 'Fill in the required details so the support team can review your request more quickly.'
                        : 'عبّئ البيانات المطلوبة لتسهيل معالجة طلبك من فريق الدعم.' }}
                </p>
            </header>

            <div class="support-compose">
                <form wire:submit="submit" class="support-form-card">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-name">{{ $isEn ? 'Name' : 'الاسم' }} <span class="text-danger">*</span></label>
                            <input id="ticket-name" type="text" class="form-control" wire:model="name" autocomplete="name">
                            @error('name') <span class="support-field__error">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-email">{{ $isEn ? 'Email' : 'البريد الإلكتروني' }} <span class="text-danger">*</span></label>
                            <input id="ticket-email" type="email" class="form-control" wire:model="email" dir="ltr" autocomplete="email">
                            @error('email') <span class="support-field__error">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-nid">{{ $isEn ? 'ID number' : 'رقم الهوية' }}</label>
                            <input id="ticket-nid" type="text" class="form-control" wire:model="nationalId" dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-phone">{{ $isEn ? 'Mobile' : 'رقم الجوال' }}</label>
                            <input id="ticket-phone" type="tel" class="form-control" wire:model="phone" dir="ltr" autocomplete="tel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-category">{{ $isEn ? 'Category' : 'الفئة' }} <span class="text-danger">*</span></label>
                            <select id="ticket-category" class="form-select" wire:model="category">
                                <option value="">{{ $isEn ? 'Select' : 'اختر' }}</option>
                                @foreach (SupportTicketOptions::categories() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category') <span class="support-field__error">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ticket-spec">{{ $isEn ? 'Specialization' : 'التخصص' }}</label>
                            <input id="ticket-spec" type="text" class="form-control" wire:model="specialization">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ticket-subject">{{ $isEn ? 'Subject' : 'الموضوع' }} <span class="text-danger">*</span></label>
                            <input id="ticket-subject" type="text" class="form-control" wire:model="subject">
                            @error('subject') <span class="support-field__error">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ticket-body">{{ $isEn ? 'Description' : 'الوصف' }} <span class="text-danger">*</span></label>
                            <textarea id="ticket-body" class="form-control" wire:model="body" rows="7" placeholder="{{ $isEn ? 'Describe the issue or inquiry in detail…' : 'صف المشكلة أو الاستفسار بتفصيل…' }}"></textarea>
                            @error('body') <span class="support-field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 support-submit mt-4" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            {{ $isEn ? 'Send ticket' : 'إرسال التذكرة' }}
                        </span>
                        <span wire:loading wire:target="submit">{{ $isEn ? 'Sending…' : 'جاري الإرسال…' }}</span>
                    </button>
                </form>

                <aside class="support-panel">
                    <div class="support-panel__icon" aria-hidden="true">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h2>{{ $isEn ? 'How it works' : 'كيف تتم المعالجة' }}</h2>
                    <ol class="support-panel__steps">
                        <li>{{ $isEn ? 'Choose the closest category to your request.' : 'اختر التصنيف الأقرب لطلبك.' }}</li>
                        <li>{{ $isEn ? 'Describe the issue clearly so we can respond faster.' : 'صف المشكلة بوضوح لتسريع الرد.' }}</li>
                        <li>{{ $isEn ? 'Save the ticket number after sending.' : 'احفظ رقم التذكرة بعد الإرسال.' }}</li>
                    </ol>
                    <a class="support-panel__link" href="{{ route('support.faq', ['locale' => $locale]) }}">
                        {{ $isEn ? 'Check the FAQ first' : 'راجع الأسئلة الشائعة أولاً' }}
                    </a>
                    <a class="support-panel__link" href="{{ route('support.ticket.search', ['locale' => $locale]) }}">
                        {{ $isEn ? 'Already have a ticket?' : 'لديك تذكرة قائمة؟' }}
                    </a>
                </aside>
            </div>
        @endif
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/support-pages.css') }}?v=2">
@endpush
