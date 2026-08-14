<?php

use App\Models\SupportTicket;
use App\Support\SupportTicketOptions;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تذاكر الدعم | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function updatedStatus(): void { $this->resetPage(); }

    #[Computed]
    public function tickets()
    {
        return SupportTicket::query()
            ->withCount('messages')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('reference_code', 'like', '%'.$this->search.'%')
                    ->orWhere('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_email', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'open' => SupportTicket::query()->whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count(),
            'resolved' => SupportTicket::query()->where('status', 'resolved')->count(),
            'total' => SupportTicket::query()->count(),
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.support-tickets'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'تذاكر الدعم'],
    ],
])

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>تذاكر الدعم الفني <span class="admin-crud-card__meta">— {{ $this->stats['open'] }} نشطة</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="رقم، موضوع، بريد...">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (SupportTicketOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الموضوع</th>
                    <th>العميل</th>
                    <th>الفئة</th>
                    <th>الحالة</th>
                    <th>الرسائل</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->tickets as $ticket)
                    <tr>
                        <td><code dir="ltr">{{ $ticket->reference_code }}</code></td>
                        <td>{{ Str::limit($ticket->subject, 40) }}</td>
                        <td>
                            <strong>{{ $ticket->contact_name }}</strong>
                            <span class="admin-table-sub" dir="ltr">{{ $ticket->contact_email }}</span>
                        </td>
                        <td>{{ SupportTicketOptions::categoryLabel($ticket->category) }}</td>
                        <td>
                            <span @class(['admin-badge', SupportTicketOptions::adminBadgeClass($ticket->status)])>
                                {{ SupportTicketOptions::statusLabel($ticket->status) }}
                            </span>
                        </td>
                        <td>{{ $ticket->messages_count }}</td>
                        <td>{{ $ticket->created_at->translatedFormat('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem">لا توجد تذاكر.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->tickets->hasPages())
        {{ $this->tickets->links() }}
    @endif
</section>

@include('partials.admin.shell-end')
