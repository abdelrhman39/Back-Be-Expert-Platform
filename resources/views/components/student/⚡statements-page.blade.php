<?php

use App\Services\StatementService;
use App\Support\StatementOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('إفاداتي | منصة مركز التعلم المستمر')]
class extends Component
{
    public string $requestType = 'enrollment';

    public string $requestNotes = '';

    public ?string $flashMessage = null;

    #[Computed]
    public function statements()
    {
        return app(StatementService::class)->forUser(auth()->user());
    }

    public function submitRequest(): void
    {
        $this->validate([
            'requestType' => ['required', 'in:'.implode(',', array_keys(StatementOptions::types()))],
            'requestNotes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'requestType' => 'نوع الإفادة',
            'requestNotes' => 'ملاحظات',
        ]);

        app(StatementService::class)->request(
            auth()->user(),
            $this->requestType,
            $this->requestNotes ?: null,
        );

        $this->reset(['requestNotes']);
        $this->flashMessage = 'تم تقديم طلب الإفادة وسيتم مراجعته من الإدارة.';
        unset($this->statements);
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'statements', 'portalTitle' => 'إفاداتي'])

<div class="portal-dashboard portal-statements-page">
    @if ($flashMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ $flashMessage }}</div>
        </div>
    @endif

    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">إفاداتي</h1>
            <p class="portal-orders-intro__desc">اطلب إفادة رسمية (التحاق، تخرج، قيد) وتابع حالة الطلب.</p>
        </div>
    </div>

    <div class="portal-settings-grid">
        <section class="portal-panel portal-settings-form">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-file-circle-plus"></i> طلب إفادة جديدة</h2>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                <form wire:submit="submitRequest">
                    <div class="mb-3">
                        <label class="form-label">نوع الإفادة</label>
                        <select class="form-select" wire:model="requestType">
                            @foreach (StatementOptions::types() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات (اختياري)</label>
                        <textarea class="form-control" rows="3" wire:model="requestNotes" placeholder="أي تفاصيل إضافية للإدارة"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">تقديم الطلب</button>
                </form>
            </div>
        </section>

        <section class="portal-panel">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> طلباتي</h2>
            </div>
            <div class="portal-panel__body">
                @if ($this->statements->isEmpty())
                    <div class="portal-empty portal-empty--compact">
                        <p>لا توجد طلبات إفادات</p>
                    </div>
                @else
                    <div class="portal-statements-list">
                        @foreach ($this->statements as $statement)
                            <article class="portal-statement-item" wire:key="st-{{ $statement->id }}">
                                <div>
                                    <strong>{{ $statement->title }}</strong>
                                    <div class="portal-statement-item__meta">
                                        <span dir="ltr">{{ $statement->reference_no }}</span>
                                        · {{ $statement->requested_at?->translatedFormat('d M Y') }}
                                    </div>
                                </div>
                                <div class="portal-statement-item__side">
                                    @php $statusClass = match($statement->status) { 'issued' => 'success', 'rejected' => 'danger', default => 'warning' }; @endphp
                                    <span class="portal-badge portal-badge--{{ $statusClass }}">{{ StatementOptions::statusLabel($statement->status) }}</span>
                                    @if ($statement->isIssued())
                                        <a href="{{ route('statements.show', ['locale' => $locale, 'statement' => $statement->id]) }}" class="btn btn-sm btn-outline-primary">عرض / طباعة</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-statements-list { display: flex; flex-direction: column; gap: 0.65rem; }
    .portal-statement-item { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.85rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; flex-wrap: wrap; }
    .portal-statement-item__meta { font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; }
    .portal-statement-item__side { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .portal-badge--warning { background: #fef3c7; color: #b45309; }
    .portal-badge--danger { background: #fee2e2; color: #b91c1c; }
    .portal-empty--compact { padding: 1.5rem; text-align: center; color: #64748b; }
</style>
@endpush
