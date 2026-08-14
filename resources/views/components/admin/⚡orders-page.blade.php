<?php

use App\Models\Order;
use App\Support\OrderOptions;
use App\Support\PaymentMethods;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminLayout' => 'app',
    'adminBreadcrumb' => [['href' => '/admin', 'label' => 'الرئيسية'], ['label' => 'طلبات الشراء']],
])]
#[Title('طلبات الشراء | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with('user')
            ->when($this->search, fn ($q) => $q->where('reference', 'like', '%'.$this->search.'%'))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }
};
?>

@include('partials.admin.shell-start', ['shellLayout' => 'app', 'shellSidebarActive' => route('admin.orders')])

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث في الطلبات</h2>
    </div>
    <div class="admin-filter-grid" style="grid-template-columns: 2fr 1fr auto;">
        <div class="admin-field">
            <label for="orderSearch">رقم الطلب</label>
            <input id="orderSearch" type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="BX-...">
        </div>
        <div class="admin-field">
            <label for="orderStatus">الحالة</label>
            <select id="orderStatus" class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (OrderOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>طلبات الشراء <span class="admin-crud-card__meta">— {{ $this->orders->total() }} طلب</span></h2>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>المرجع</th>
                    <th>العميل</th>
                    <th>المبلغ</th>
                    <th>طريقة الدفع</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="dash-inline-link"><strong>{{ $order->reference }}</strong></a></td>
                        <td>
                            @if ($order->user)
                                <a href="{{ route('admin.users.show', $order->user) }}" class="dash-inline-link">{{ $order->user->displayName() }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td dir="ltr">{{ number_format((float) $order->total, 2) }} ر.س</td>
                        <td>{{ PaymentMethods::label($order->payment_method ?? '') ?: '—' }}</td>
                        <td>
                            <span @class(['admin-badge', OrderOptions::statusBadgeClass($order->status)])>
                                {{ OrderOptions::statusLabel($order->status) }}
                            </span>
                        </td>
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--sa-muted)">لا توجد طلبات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->orders->hasPages())
        {{ $this->orders->links() }}
    @endif
</section>

<style>
    .admin-badge { display:inline-block; padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:600; }
    .admin-badge--success { background:var(--sa-green-soft); color:var(--sa-green-dark); }
    .admin-badge--muted { background:var(--surface-track); color:var(--sa-muted); }
    .admin-badge--warn { background:#fff7ed; color:#c2410c; }
    .admin-badge--danger { background:#fef2f2; color:#b91c1c; }
    .admin-badge--info { background:#eff6ff; color:#1d4ed8; }
</style>

@include('partials.admin.shell-end')
