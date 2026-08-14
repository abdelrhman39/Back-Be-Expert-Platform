<?php

use App\Models\Statement;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-user')]
class extends Component
{
    public Statement $statement;

    public function mount(Statement $statement): void
    {
        abort_unless($statement->user_id === auth()->id(), 403);
        abort_unless($statement->isIssued(), 404);

        $this->statement = $statement;
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'statements', 'portalTitle' => $this->statement->title])

<div class="portal-dashboard">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $this->statement->title }}</h1>
            <p class="portal-orders-intro__desc">مرجع: <code dir="ltr">{{ $this->statement->reference_no }}</code></p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> طباعة</button>
            <a href="{{ route('statements', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">العودة</a>
        </div>
    </div>

    @include('partials.statements.printable', ['statement' => $this->statement])
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    @media print {
        .header, .new-sidebar, .dashboard-header, .portal-orders-intro { display: none !important; }
    }
</style>
@endpush
