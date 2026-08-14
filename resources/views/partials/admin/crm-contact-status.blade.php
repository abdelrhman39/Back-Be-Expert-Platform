@php
    /** @var \App\Models\CrmContact $contact */
    $canChangeStatus = \App\Support\CrmAccess::canChangeStatus(auth()->user());
@endphp

@if ($canChangeStatus)
    <select
        class="crm-status-select"
        style="--status-color: {{ \App\Support\CrmOptions::statusColor($contact->status) }}"
        wire:change="updateContactStatus({{ $contact->id }}, $event.target.value)"
        wire:loading.attr="disabled"
        wire:target="updateContactStatus,confirmPaymentStatus"
        onclick="event.stopPropagation()"
        aria-label="تغيير مرحلة {{ $contact->name }}"
    >
        @foreach (\App\Support\CrmOptions::statuses() as $key => $label)
            <option value="{{ $key }}" @selected($contact->status === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @if ($contact->hasPaymentReceipt())
        <button type="button" class="crm-receipt-link" wire:click="downloadPaymentReceipt({{ $contact->id }})" title="تنزيل إيصال السداد">
            <i class="fa-solid fa-paperclip"></i> إيصال
        </button>
    @endif
@else
    <span class="crm-status" style="background: {{ \App\Support\CrmOptions::statusColor($contact->status) }}22; color: {{ \App\Support\CrmOptions::statusColor($contact->status) }}">
        {{ \App\Support\CrmOptions::statusLabel($contact->status) }}
    </span>
    @if ($contact->hasPaymentReceipt())
        <button type="button" class="crm-receipt-link" wire:click="downloadPaymentReceipt({{ $contact->id }})">
            <i class="fa-solid fa-paperclip"></i> إيصال
        </button>
    @endif
@endif
<small>{{ \App\Support\CrmOptions::label(\App\Support\CrmOptions::priorities(), $contact->priority) }}</small>
