@php
    $orderRef = $orderRef ?? null;
    $amount = isset($amount) ? (float) $amount : null;
    $variant = $variant ?? 'default';
    $showNotice = $showNotice ?? ($variant === 'portal');
@endphp

<div @class(['portal-bank-transfer', 'portal-bank-transfer--'.$variant])>
    @if ($showNotice)
        <div class="portal-pending-banner portal-bank-transfer__notice">
            <span class="portal-pending-banner__icon"><i class="fa-solid fa-circle-info"></i></span>
            <div class="portal-pending-banner__text">
                <strong>تم تأكيد طلبك</strong>
                <span>يرجى إتمام التحويل البنكي — سيتم تفعيل الدورة بعد مراجعة الإدارة.</span>
            </div>
        </div>
    @endif

    <section class="portal-panel portal-bank-transfer__card">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title">
                <i class="fa-solid fa-building-columns"></i>
                إرشادات التحويل البنكي
            </h2>
        </div>

        <div class="portal-panel__body portal-panel__body--padded">
            @if ($orderRef || $amount !== null)
                <div class="portal-bank-transfer__summary">
                    @if ($orderRef)
                        <div class="portal-bank-transfer__summary-item">
                            <span class="portal-bank-transfer__summary-label">رقم الطلب</span>
                            <code class="portal-bank-transfer__summary-value" dir="ltr">{{ $orderRef }}</code>
                        </div>
                    @endif
                    @if ($amount !== null)
                        <div class="portal-bank-transfer__summary-item portal-bank-transfer__summary-item--amount">
                            <span class="portal-bank-transfer__summary-label">المبلغ المطلوب</span>
                            <strong class="portal-bank-transfer__summary-value" dir="ltr">{{ number_format($amount, 2) }} <small>ر.س</small></strong>
                        </div>
                    @endif
                </div>
            @endif

            <div class="portal-bank-transfer__content">
                {!! $html !!}
            </div>

            <div class="portal-bank-transfer__footer">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <span>تُفعَّل الدورة خلال 1–2 يوم عمل بعد التحقق من التحويل</span>
            </div>
        </div>
    </section>
</div>

@once
    <script>
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-copy-iban]');
            if (!btn) return;

            const text = btn.getAttribute('data-copy-iban') || '';
            if (!text) return;

            navigator.clipboard.writeText(text.replace(/\s+/g, '')).then(function () {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> تم النسخ';
                btn.classList.add('is-copied');
                setTimeout(function () {
                    btn.innerHTML = original;
                    btn.classList.remove('is-copied');
                }, 2000);
            });
        });
    </script>
@endonce
