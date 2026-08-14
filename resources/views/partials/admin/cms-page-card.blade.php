@use('App\Support\CmsOptions')
@use('Illuminate\Support\Str')

@php
    $title = $tAr?->title ?? $tEn?->title ?? '—';
    $typeLabel = CmsOptions::pageTypes()[$page->type] ?? $page->type;
    $layoutLabel = CmsOptions::pageLayouts()[$page->layout ?? 'default'] ?? 'default';
    $statusLabel = CmsOptions::pageStatuses()[$page->status] ?? $page->status;
@endphp

<article
    wire:key="cms-card-{{ $page->id }}"
    @class([
        'cms-page-card',
        'cms-page-card--published' => $page->status === 'published',
        'cms-page-card--draft' => $page->status === 'draft',
        'cms-page-card--archived' => $page->status === 'archived',
        'cms-page-card--type-'.$page->type,
    ])
>
    <div class="cms-page-card__accent" aria-hidden="true"></div>

    <header class="cms-page-card__head">
        <div class="cms-page-card__title-wrap">
            <h3 class="cms-page-card__title">{{ $title }}</h3>
            <span class="cms-page-card__id">#{{ $page->id }}</span>
        </div>
        <span @class(['cms-page-card__status', 'is-'.$page->status])>{{ $statusLabel }}</span>
    </header>

    @if ($tAr?->slug)
        <div class="cms-page-card__slug" dir="ltr">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
            /ar/page/{{ $tAr->slug }}
        </div>
    @endif

    @if ($tAr?->excerpt)
        <p class="cms-page-card__excerpt">{{ Str::limit(strip_tags($tAr->excerpt), 110) }}</p>
    @endif

    <div class="cms-page-card__tags">
        <span class="cms-page-card__tag cms-page-card__tag--type">{{ $typeLabel }}</span>
        <span class="cms-page-card__tag">{{ $layoutLabel }}</span>
        @if ($tAr)<span class="cms-page-card__lang cms-page-card__lang--ar">AR</span>@endif
        @if ($tEn)<span class="cms-page-card__lang cms-page-card__lang--en">EN</span>@endif
        @if ($page->show_in_footer)<span class="cms-page-card__tag cms-page-card__tag--muted">فوتر</span>@endif
        @if ($page->noindex ?? false)<span class="cms-page-card__tag cms-page-card__tag--warn">NOINDEX</span>@endif
        @if (! ($page->show_title ?? true))<span class="cms-page-card__tag cms-page-card__tag--muted">بدون عنوان</span>@endif
    </div>

    <footer class="cms-page-card__foot">
        <div class="cms-page-card__meta">
            <time datetime="{{ $page->updated_at?->toIso8601String() }}">{{ $page->updated_at?->format('Y-m-d H:i') }}</time>
            @if ($page->creator)
                <span class="cms-page-card__author">{{ $page->creator->displayName() }}</span>
            @endif
        </div>

        @canAdmin('pages.manage')
            <div class="cms-page-card__actions">
                <a href="{{ route('admin.cms-pages.edit', $page) }}" class="cms-page-card__btn cms-page-card__btn--primary">تعديل</a>
                <a href="{{ route('admin.cms-pages.preview', ['page' => $page->id, 'locale' => 'ar']) }}" class="cms-page-card__btn" target="_blank" rel="noopener" title="معاينة">معاينة</a>
                @if ($publicUrl)
                    <a href="{{ $publicUrl }}" class="cms-page-card__btn" target="_blank" rel="noopener" title="عرض عام">عرض</a>
                @endif
                <button type="button" class="cms-page-card__btn" wire:click="toggleStatus({{ $page->id }})" title="{{ $page->status === 'published' ? 'إلغاء النشر' : 'نشر' }}">
                    {{ $page->status === 'published' ? 'مسودة' : 'نشر' }}
                </button>
                <button type="button" class="cms-page-card__btn cms-page-card__btn--icon" wire:click="duplicatePage({{ $page->id }})" title="نسخ">⧉</button>
                @if ($page->type !== 'home')
                    <button type="button" class="cms-page-card__btn cms-page-card__btn--danger" wire:click="deletePage({{ $page->id }})" wire:confirm="حذف هذه الصفحة نهائياً؟">حذف</button>
                @endif
            </div>
        @endcanAdmin
    </footer>
</article>
