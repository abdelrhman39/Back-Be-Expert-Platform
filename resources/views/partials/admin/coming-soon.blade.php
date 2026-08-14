<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>{{ $title ?? 'قريباً' }}</h2>
        <p class="admin-crud-card__meta">{{ $description ?? 'هذا القسم قيد التطوير ضمن المرحلة 5.' }}</p>
    </div>
    <div class="admin-coming-soon">
        <p>سيتم ربط هذه الشاشة بقاعدة البيانات ومسارات Laravel الكاملة قريباً.</p>
        @if (! empty($legacy))
            <p class="mb-0">
                <a href="{{ legacy_page($legacy) }}" class="dash-inline-link" target="_blank" rel="noopener">معاينة النسخة التجريبية (HTML)</a>
            </p>
        @endif
    </div>
</section>

<style>
    .admin-coming-soon {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--sa-muted);
        font-size: 0.92rem;
    }
</style>
