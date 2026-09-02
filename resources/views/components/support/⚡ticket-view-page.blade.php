<?php

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use App\Support\SupportTicketOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('متابعة التذكرة | مركز التعلم المستمر')]
class extends Component
{
    public SupportTicket $ticket;

    public string $replyBody = '';

    public ?string $flashMessage = null;

    public function mount(SupportTicket $ticket, SupportTicketService $service): void
    {
        abort_unless($service->canView($ticket, auth()->user()), 403);

        $this->ticket = $ticket->load(['messages.user']);
    }

    #[Computed]
    public function messages()
    {
        return $this->ticket->messages()->with('user')->orderBy('created_at')->get();
    }

    public function sendReply(SupportTicketService $service): void
    {
        abort_unless($service->canView($this->ticket, auth()->user()), 403);
        abort_if(in_array($this->ticket->status, ['closed', 'resolved'], true), 403, 'التذكرة مغلقة.');

        $validated = $this->validate([
            'replyBody' => ['required', 'string', 'min:5'],
        ], [], ['replyBody' => 'الرد']);

        $isStaff = auth()->user()?->canAdmin('support.manage') ?? false;

        $service->addReply($this->ticket, $validated['replyBody'], auth()->user(), $isStaff);
        $this->replyBody = '';
        $this->flashMessage = 'تم إرسال ردك.';
        unset($this->messages);
        $this->ticket->refresh();
    }
};
?>

@php
    $locale = app()->getLocale();
    $statusClass = SupportTicketOptions::statusBadgeClass($ticket->status);
@endphp

<div class="support-page">
    @include('partials.support.nav', ['active' => 'search'])

    <div class="container support-page__container">
        <div class="support-ticket-view">
            <header class="support-ticket-view__head">
                <div>
                    <a href="{{ route('support.ticket.search', ['locale' => $locale]) }}" class="support-back">← البحث عن تذكرة</a>
                    <h1 class="support-page__title mb-1">{{ $ticket->subject }}</h1>
                    <p class="support-page__lead mb-0">
                        <code dir="ltr">{{ $ticket->reference_code }}</code>
                        · {{ SupportTicketOptions::categoryLabel($ticket->category) }}
                        · {{ $ticket->created_at->translatedFormat('d M Y') }}
                    </p>
                </div>
                <span @class(['portal-status-pill', $statusClass])>
                    {{ SupportTicketOptions::statusLabel($ticket->status) }}
                </span>
            </header>

            @if ($flashMessage)
                <div class="alert alert-success">{{ $flashMessage }}</div>
            @endif

            <div class="support-thread">
                @foreach ($this->messages as $message)
                    <article @class(['support-msg', 'support-msg--staff' => $message->is_staff])>
                        <header class="support-msg__head">
                            <strong>{{ $message->authorName() }}</strong>
                            <time>{{ $message->created_at->translatedFormat('d M Y — H:i') }}</time>
                        </header>
                        <div class="support-msg__body">{!! nl2br(e($message->body)) !!}</div>
                    </article>
                @endforeach
            </div>

            @if (! in_array($ticket->status, ['closed', 'resolved'], true))
                <form wire:submit="sendReply" class="support-reply-form">
                    <label class="form-label">إضافة رد</label>
                    <textarea class="form-control" wire:model="replyBody" rows="4" placeholder="اكتب ردك هنا..."></textarea>
                    @error('replyBody') <span class="text-danger small">{{ $message }}</span> @enderror
                    <button type="submit" class="btn btn-primary mt-3" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendReply">إرسال الرد</span>
                        <span wire:loading wire:target="sendReply">جاري الإرسال…</span>
                    </button>
                </form>
            @else
                <div class="alert alert-secondary">تم إغلاق هذه التذكرة. للاستفسارات الجديدة، <a href="{{ route('support.ticket.new', ['locale' => $locale]) }}">افتح تذكرة جديدة</a>.</div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/portal-dashboard.css') }}">
@endpush
