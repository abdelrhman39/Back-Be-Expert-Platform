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
            $this->errorMessage = 'لم يتم العثور على تذكرة بهذه البيانات. تحقق من الرقم والبريد.';

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

<div class="support-page">
    @include('partials.support.nav', ['active' => 'search'])

    <div class="container support-page__container">
        <div class="row g-4 align-items-stretch">
            <div class="col-md-6 order-2 order-md-1">
                <div class="support-form-card h-100">
                    <form wire:submit="search">
                        <div class="mb-4">
                            <label class="form-label">رقم التذكرة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" wire:model="reference" placeholder="TKT-..." dir="ltr">
                            @error('reference') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-lg" wire:model="email" placeholder="أدخل بريدك الإلكتروني" dir="ltr">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        @if ($errorMessage)
                            <div class="alert alert-danger">{{ $errorMessage }}</div>
                        @endif
                        <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">بحث</button>
                    </form>
                </div>
            </div>
            <div class="col-md-6 order-1 order-md-2">
                <aside class="support-aside h-100 d-flex flex-column justify-content-center">
                    <div class="support-aside__icon">🔐</div>
                    <h2>البحث عن تذكرة</h2>
                    <p class="mb-0">ادخل رقم التذكرة وبريدك الإلكتروني للبحث عن تذكرتك ومتابعة الحالة.</p>
                </aside>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}">
@endpush
