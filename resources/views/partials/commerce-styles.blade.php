@push('styles')
<style>
    .commerce-page .dashboard-header {
        margin-bottom: 1.25rem;
    }

    .commerce-summary-card {
        border: 1px solid color-mix(in oklab, var(--primary), transparent 82%);
        border-radius: var(--radius-xl, 16px);
        background: linear-gradient(180deg, #fff 0%, color-mix(in oklab, var(--primary), transparent 96%) 100%);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
    }

    .commerce-summary-card .summary-total {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--primary);
    }

    .commerce-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .commerce-list-item {
        background: #fff;
        border: 1px solid #e8edf2;
        border-radius: var(--radius-xl, 16px);
        padding: 1rem 1.15rem;
        transition: box-shadow .2s ease, border-color .2s ease;
    }

    .commerce-list-item:hover {
        border-color: color-mix(in oklab, var(--primary), transparent 70%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
    }

    .commerce-list-thumb {
        display: block;
        width: 120px;
        height: 86px;
        border-radius: var(--radius-lg, 12px);
        overflow: hidden;
        flex-shrink: 0;
        background: #f4f7fa;
    }

    .commerce-list-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .commerce-list-title {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.5;
        margin: 0 0 .35rem;
    }

    .commerce-list-title a {
        color: #1d1d1d;
        text-decoration: none;
    }

    .commerce-list-title a:hover {
        color: var(--primary);
    }

    .commerce-list-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem .75rem;
    }

    .commerce-list-meta .badge {
        font-size: .75rem;
        padding: .35rem .65rem;
    }

    .commerce-list-meta .course-id {
        color: #8f8f8f;
        font-size: .82rem;
    }

    .commerce-list-price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1d1d1d;
        white-space: nowrap;
        margin-bottom: .65rem;
    }

    .commerce-list-actions {
        display: flex;
        justify-content: flex-end;
    }

    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
    }

    @media (max-width: 575.98px) {
        .payment-methods-grid {
            grid-template-columns: 1fr;
        }
    }

    .payment-method-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .payment-method-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .95rem 1rem;
        border: 2px solid #e8edf2;
        border-radius: var(--radius-lg, 12px);
        background: #fff;
        cursor: pointer;
        transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        margin: 0;
    }

    .payment-method-card:hover {
        border-color: color-mix(in oklab, var(--primary), transparent 55%);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
    }

    .payment-method-card.is-selected {
        border-color: var(--primary);
        background: color-mix(in oklab, var(--primary), transparent 94%);
        box-shadow: 0 0 0 1px color-mix(in oklab, var(--primary), transparent 70%);
    }

    .payment-method-card__check {
        position: absolute;
        top: .65rem;
        inset-inline-start: .65rem;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: #fff;
    }

    .payment-method-card.is-selected .payment-method-card__check {
        border-color: var(--primary);
        background: var(--primary);
        box-shadow: inset 0 0 0 3px #fff;
    }

    .payment-method-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 40px;
        flex-shrink: 0;
        margin-inline-start: 1.4rem;
    }

    .payment-method-card__icon img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .payment-method-card__icon svg {
        width: 28px;
        height: 28px;
        color: #334155;
    }

    .payment-method-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 52px;
        padding: .25rem .5rem;
        border-radius: 6px;
        background: #f1f5f9;
        font-size: .72rem;
        font-weight: 700;
        color: #475569;
    }

    .payment-method-card__body {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        min-width: 0;
    }

    .payment-method-card__title {
        font-size: .95rem;
        color: #1d1d1d;
    }

    .payment-method-card__desc {
        font-size: .78rem;
        color: #64748b;
        line-height: 1.4;
    }

    .payment-bank-instructions {
        padding: 1.15rem 1.25rem;
        border-radius: var(--radius-lg, 12px);
        border: 1px dashed color-mix(in oklab, var(--primary), transparent 60%);
        background: color-mix(in oklab, var(--primary), transparent 96%);
    }

    .payment-bank-instructions__content {
        font-size: .92rem;
        color: #334155;
        line-height: 1.7;
    }

    .payment-bank-instructions__content ul,
    .payment-bank-instructions__content ol {
        padding-inline-start: 1.25rem;
        margin-bottom: .75rem;
    }

    .payment-gateway-note {
        display: flex;
        align-items: flex-start;
        gap: .65rem;
        padding: .9rem 1rem;
        border-radius: var(--radius-lg, 12px);
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: .88rem;
        line-height: 1.6;
    }

    .payment-gateway-note svg {
        flex-shrink: 0;
        margin-top: .1rem;
    }

    .commerce-empty {
        background: #fff;
        border: 1px dashed #d7dee7;
        border-radius: var(--radius-xl, 16px);
        padding: 3rem 1.5rem;
    }

    .commerce-empty img {
        max-width: 110px;
        opacity: .35;
    }

    @media (max-width: 767.98px) {
        .commerce-list-item .commerce-list-row {
            flex-wrap: wrap;
        }

        .commerce-list-thumb {
            width: 100%;
            height: 160px;
        }

        .commerce-list-side {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: .25rem;
        }

        .commerce-list-price {
            margin-bottom: 0;
        }
    }

    @media (min-width: 768px) {
        .commerce-list-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .commerce-list-body {
            flex: 1;
            min-width: 0;
        }

        .commerce-list-side {
            text-align: end;
            flex-shrink: 0;
            padding-inline-start: .5rem;
        }
    }

    .commerce-guest-checkout__head p {
        line-height: 1.55;
    }

    .commerce-guest-checkout__form .form-label {
        font-size: 0.86rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .commerce-guest-checkout__form .form-control {
        height: 44px;
        border-radius: 10px;
    }

    .commerce-guest-checkout__submit {
        font-weight: 700;
        border-radius: 10px;
        min-height: 48px;
        box-shadow: 0 10px 22px color-mix(in oklab, var(--primary), transparent 72%);
    }

    .commerce-guest-checkout__trust {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.45rem;
    }

    .commerce-guest-checkout__trust li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.78rem;
        color: #5b6770;
        line-height: 1.45;
    }

    .commerce-guest-checkout__trust i {
        color: var(--primary);
        margin-top: 0.15rem;
    }
</style>
@endpush
