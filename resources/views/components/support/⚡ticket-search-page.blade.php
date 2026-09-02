<?php

use App\Services\SupportTicketService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('البحث عن تذكرة | مركز التعلم المستمر')]
class extends Component
{
    public string $reference = '';

    public string $email = '';

    public ?string $errorMessage = null;

    public function search(SupportTicketService $service): void
    {
        $this->errorMessage = null;

        $validated = $this->validate([
            'reference' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email'],
        ], [], [
            'reference' => 'رقم التذكرة',
            'email' => 'البريد الإلكتروني',
        ]);

        $ticket = $service->findByReferenceAndEmail($validated['reference'], $validated['email']);

        if (! $ticket) {
            $this->errorMessage = app()->getLocale() === 'en'
                ? 'No ticket was found with these details. Check the number and email.'
                : 'لم يتم العثور على تذكرة بهذه البيانات. تحقق من الرقم والبريد.';

            return;
        }

        $service->grantGuestAccess($ticket);

        $this->redirect(route('support.ticket.view', [
            'locale' => app()->getLocale(),
            'ticket' => $ticket->reference_code,
        ]));
    }
};
?>

@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
@endphp

<div class="support-page">
    @include('partials.support.nav', ['active' => 'search'])

    <div class="container support-page__container">
        <header class="support-head">
            <span class="support-head__eyebrow">{{ platform_org() }}</span>
            <h1 class="support-page__title">{{ $isEn ? 'Find a support ticket' : 'البحث عن تذكرة' }}</h1>
            <p class="support-page__lead">
                {{ $isEn
                    ? 'Enter the ticket number and the email used when it was created to follow its status.'
                    : 'أدخل رقم التذكرة والبريد المستخدم عند إنشائها لمتابعة حالتها.' }}
            </p>
        </header>

        <div class="support-track">
            <aside class="support-panel">
                <div class="support-panel__icon" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2>{{ $isEn ? 'Secure follow-up' : 'متابعة آمنة' }}</h2>
                <p>{{ $isEn ? 'Access is limited to the ticket number and matching email.' : 'يقتصر الوصول على رقم التذكرة مع البريد المطابق.' }}</p>
                <ul class="support-panel__list">
                    <li>{{ $isEn ? 'Use the same email you submitted with.' : 'استخدم البريد نفسه الذي أُرسلت به التذكرة.' }}</li>
                    <li>{{ $isEn ? 'Ticket numbers look like TKT-…' : 'رقم التذكرة يبدأ عادةً بـ TKT-' }}</li>
                    <li>
                        {{ $isEn ? 'Need a new request?' : 'طلب جديد؟' }}
                        <a href="{{ route('support.ticket.new', ['locale' => $locale]) }}">{{ $isEn ? 'Create a ticket' : 'أنشئ تذكرة' }}</a>
                    </li>
                </ul>
            </aside>

            <div class="support-form-card">
                <form wire:submit="search" class="support-form">
                    <div class="support-field">
                        <label class="form-label" for="ticket-reference">{{ $isEn ? 'Ticket number' : 'رقم التذكرة' }} <span class="text-danger">*</span></label>
                        <input id="ticket-reference" type="text" class="form-control form-control-lg" wire:model="reference" placeholder="TKT-..." dir="ltr" autocomplete="off">
                        @error('reference') <span class="support-field__error">{{ $message }}</span> @enderror
                    </div>
                    <div class="support-field">
                        <label class="form-label" for="ticket-email">{{ $isEn ? 'Email' : 'البريد الإلكتروني' }} <span class="text-danger">*</span></label>
                        <input id="ticket-email" type="email" class="form-control form-control-lg" wire:model="email" placeholder="{{ $isEn ? 'Enter your email' : 'أدخل بريدك الإلكتروني' }}" dir="ltr" autocomplete="email">
                        @error('email') <span class="support-field__error">{{ $message }}</span> @enderror
                    </div>
                    @if ($errorMessage)
                        <div class="support-alert" role="alert">{{ $errorMessage }}</div>
                    @endif
                    <button type="submit" class="btn btn-primary btn-lg w-100 support-submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="search">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            {{ $isEn ? 'Search' : 'بحث' }}
                        </span>
                        <span wire:loading wire:target="search">{{ $isEn ? 'Searching…' : 'جاري البحث…' }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/support-pages.css') }}?v=2">
@endpush
