<div class="container py-5" style="max-width: 40rem; text-align: center;">
    <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.75rem;">
        {{ $translation?->title ?? 'صفحة قيد الإعداد' }}
    </h1>
    <p class="text-muted mb-0" style="line-height: 1.7;">
        المحتوى غير متوفر بعد. يرجى إضافة بلوكات أو محتوى HTML من لوحة التحكم ونشر الصفحة.
    </p>
    @canAdmin('pages.manage')
        @if ($page ?? null)
            <p class="mt-3 mb-0">
                <a href="{{ route('admin.cms-pages.edit', $page) }}" class="btn btn-primary btn-sm">تحرير الصفحة</a>
            </p>
        @endif
    @endcanAdmin
</div>
