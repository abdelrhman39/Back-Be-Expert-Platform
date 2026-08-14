@props(['request', 'canReview' => false])

<div class="admin-actions-menu">
    <button
        type="button"
        class="admin-kebab"
        aria-expanded="false"
        aria-haspopup="true"
        aria-label="إجراءات الطلب"
    >
        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
            <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
        </svg>
    </button>
    <ul class="admin-actions-dropdown" hidden role="menu">
        <li role="none">
            <a href="{{ route('admin.requests.show', $request) }}" class="admin-actions-item" role="menuitem">
                <svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>عرض</span>
            </a>
        </li>
        @if ($canReview && $request->canReview())
            <li role="none">
                <button
                    type="button"
                    class="admin-actions-item admin-actions-item--success admin-actions-item--btn"
                    role="menuitem"
                    wire:click="approveRequest({{ $request->id }})"
                    wire:confirm="تأكيد الموافقة على هذا الطلب؟"
                >
                    <svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    <span>موافقة</span>
                </button>
            </li>
            <li role="none">
                <button
                    type="button"
                    class="admin-actions-item admin-actions-item--danger admin-actions-item--btn"
                    role="menuitem"
                    wire:click="rejectRequest({{ $request->id }})"
                    wire:confirm="تأكيد رفض هذا الطلب؟"
                >
                    <svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    <span>رفض</span>
                </button>
            </li>
        @endif
    </ul>
</div>
