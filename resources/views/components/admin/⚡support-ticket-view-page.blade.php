<?php

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use App\Support\SupportTicketOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تذكرة دعم | لوحة التحكم')]
class extends Component
{
    public SupportTicket $ticket;

    public string $replyBody = '';

    public string $newStatus = '';

    public ?string $flashMessage = null;

    public function mount(SupportTicket $ticket): void
    {
        abort_unless(auth()->user()?->canAdmin('support.view'), 403);

        $this->ticket = $ticket->load(['messages.user', 'user']);
        $this->newStatus = $ticket->status;
    }

    #[Computed]
    public function messages()
    {
        return $this->ticket->messages()->with('user')->orderBy('created_at')->get();
    }

    public function sendReply(SupportTicketService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('support.manage'), 403);

        $validated = $this->validate([
            'replyBody' => ['required', 'string', 'min:5'],
        ], [], ['replyBody' => 'الرد']);

        $service->addReply($this->ticket, $validated['replyBody'], auth()->user(), true);
        $this->replyBody = '';
        $this->flashMessage = 'تم إرسال الرد.';
        unset($this->messages);
        $this->ticket->refresh();
    }

    public function updateStatus(SupportTicketService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('support.manage'), 403);

        $this->validate([
            'newStatus' => ['required', 'in:'.implode(',', array_keys(SupportTicketOptions::statuses()))],
        ]);

        $service->updateStatus($this->ticket, $this->newStatus);
        $this->ticket->refresh();
        $this->flashMessage = 'تم تحديث حالة التذكرة.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.support-tickets'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.support-tickets'), 'label' => 'تذاكر الدعم'],
        ['label' => $ticket->reference_code],
    ],
])

@if ($flashMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $flashMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $ticket->subject }}</h2>
            <p class="admin-crud-card__meta">
                <code dir="ltr">{{ $ticket->reference_code }}</code>
                · {{ SupportTicketOptions::categoryLabel($ticket->category) }}
                · {{ $ticket->created_at->translatedFormat('d M Y H:i') }}
            </p>
        </div>
        <span @class(['admin-badge', SupportTicketOptions::adminBadgeClass($ticket->status)])>
            {{ SupportTicketOptions::statusLabel($ticket->status) }}
        </span>
    </div>

    <div class="admin-filter-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1rem;">
        <div class="admin-field">
            <label>العميل</label>
            <p class="admin-crud-card__meta"><strong>{{ $ticket->contact_name }}</strong><br dir="ltr">{{ $ticket->contact_email }}</p>
        </div>
        <div class="admin-field">
            <label>الجوال / الهوية</label>
            <p class="admin-crud-card__meta" dir="ltr">{{ $ticket->contact_phone ?? '—' }} / {{ $ticket->contact_national_id ?? '—' }}</p>
        </div>
        <div class="admin-field">
            <label>حساب مسجّل</label>
            <p class="admin-crud-card__meta">
                @if ($ticket->user)
                    <a href="{{ route('admin.users.show', $ticket->user) }}">{{ $ticket->user->displayName() }}</a>
                @else
                    زائر
                @endif
            </p>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head"><h2>المحادثة</h2></div>
    <div class="support-thread" style="margin:0 0 1rem;">
        @foreach ($this->messages as $message)
            <article @class(['support-msg', 'support-msg--staff' => $message->is_staff]) style="margin-bottom:0.5rem;">
                <header class="support-msg__head">
                    <strong>{{ $message->authorName() }}</strong>
                    <time>{{ $message->created_at->translatedFormat('d M Y — H:i') }}</time>
                </header>
                <div class="support-msg__body">{!! nl2br(e($message->body)) !!}</div>
            </article>
        @endforeach
    </div>

    @canAdmin('support.manage')
        <form wire:submit="sendReply" class="mb-3">
            <div class="admin-field">
                <label>رد فريق الدعم</label>
                <textarea class="admin-control" wire:model="replyBody" rows="4"></textarea>
                @error('replyBody') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">إرسال الرد</button>
        </form>

        <form wire:submit="updateStatus" class="admin-filter-grid" style="grid-template-columns:1fr auto;align-items:end;gap:0.75rem;">
            <div class="admin-field">
                <label>تحديث الحالة</label>
                <select class="admin-control" wire:model="newStatus">
                    @foreach (SupportTicketOptions::statuses() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="admin-btn-secondary admin-btn-secondary--sm">حفظ الحالة</button>
        </form>
    @endcanAdmin
</section>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}">
@endpush

@include('partials.admin.shell-end')
