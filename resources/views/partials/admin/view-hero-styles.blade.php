@push('styles')
<style>
    .admin-batch-view-hero {
        padding: 1.35rem 1.5rem;
        background: linear-gradient(180deg, var(--sa-green-soft) 0%, var(--surface-card) 100%);
        border-bottom: 1px solid var(--sa-border);
    }
    .admin-batch-view-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        gap: 1.25rem 2rem;
        margin: 0;
        padding: 0;
        border: none;
    }
    .admin-batch-view-head__title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.75rem;
        margin-bottom: 0.4rem;
    }
    .admin-batch-view-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--sa-ink);
        line-height: 1.35;
    }
    .admin-batch-view-meta {
        margin: 0 0 0.75rem;
        font-size: 0.82rem;
        color: var(--sa-muted);
        font-weight: 500;
        line-height: 1.5;
    }
    .admin-batch-view-meta__sep { margin: 0 0.35rem; opacity: 0.55; }
    .admin-batch-view-facts {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1.25rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .admin-batch-view-facts li {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem 0.5rem;
        font-size: 0.82rem;
    }
    .admin-batch-view-facts__label {
        color: var(--sa-muted);
        font-weight: 600;
    }
    .admin-batch-view-facts__value {
        color: var(--sa-ink);
        font-weight: 600;
    }
    .admin-batch-view-facts .admin-tag { margin-inline-start: 0; }
    .admin-batch-view-capacity {
        min-width: 11rem;
        max-width: 13rem;
        padding: 0.9rem 1rem;
        border-radius: var(--radius-md);
        background: var(--surface-card);
        border: 1px solid var(--sa-border);
        box-shadow: var(--dash-shadow, 0 1px 3px rgba(0, 0, 0, 0.06));
    }
    .admin-batch-view-capacity__head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.45rem;
    }
    .admin-batch-view-capacity__title {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--sa-muted);
    }
    .admin-batch-view-capacity__count {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--sa-green-dark);
        unicode-bidi: isolate;
    }
    .admin-batch-view-capacity__bar {
        height: 0.5rem;
        border-radius: 999px;
        background: var(--surface-card);
        overflow: hidden;
        border: 1px solid var(--sa-border);
    }
    .admin-batch-view-capacity__bar > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--sa-green), var(--sa-green-dark));
        transition: width 0.25s ease;
    }
    .admin-batch-view-capacity__foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: 0.4rem;
        font-size: 0.75rem;
        color: var(--sa-muted);
    }
    .admin-batch-view-capacity__pct { font-weight: 700; color: var(--sa-green-dark); }
    .admin-batch-view-capacity__seats { text-align: end; }
    .admin-program-view-summary { max-width: 11.5rem; }
    .admin-program-view-summary__list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 0.45rem;
    }
    .admin-program-view-summary__list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: var(--sa-muted);
    }
    .admin-program-view-summary__list strong {
        font-size: 0.92rem;
        font-weight: 800;
        color: var(--sa-green-dark);
    }
    @media (max-width: 860px) {
        .admin-batch-view-hero { padding: 1.15rem 1.25rem; }
        .admin-batch-view-head { grid-template-columns: 1fr; }
        .admin-batch-view-capacity { max-width: none; }
    }
</style>
@endpush
