@php
    $cms = $cms ?? app(\App\Services\CmsPageService::class);
    $translation = $translation ?? $page->translate($previewLocale);
    $layout = $layout ?? ($page->layout ?? 'default');
    $statusLabel = $statusLabel ?? (\App\Support\CmsOptions::pageStatuses()[$page->status] ?? $page->status);
    $typeLabel = $typeLabel ?? (\App\Support\CmsOptions::pageTypes()[$page->type] ?? $page->type);
    $layoutLabel = $layoutLabel ?? (\App\Support\CmsOptions::pageLayouts()[$layout] ?? $layout);
    $publicUrl = $publicUrl ?? $cms->publicUrl($page, $previewLocale);
    $pageTitle = $pageTitle ?? ($translation?->title ?? '—');
    $pageSlug = $pageSlug ?? $translation?->slug;
@endphp

<header class="cms-preview-chrome" aria-label="شريط معاينة الأدمن">
    <div class="container cms-preview-chrome__inner">
        <div class="cms-preview-chrome__brand">
            <span class="cms-preview-chrome__icon" aria-hidden="true">👁</span>
            <div>
                <strong>معاينة الصفحة</strong>
                <small>كما ستظهر على الموقع</small>
            </div>
        </div>

        <div class="cms-preview-chrome__page">
            <span class="cms-preview-chrome__page-title">{{ $pageTitle }}</span>
            @if ($pageSlug)
                <code class="cms-preview-chrome__page-slug">/{{ $previewLocale }}/page/{{ $pageSlug }}</code>
            @endif
        </div>

        <div class="cms-preview-chrome__chips">
            <span @class(['cms-preview-chip', 'cms-preview-chip--'.$page->status])>{{ $statusLabel }}</span>
            <span class="cms-preview-chip cms-preview-chip--muted">{{ $typeLabel }}</span>
            <span class="cms-preview-chip cms-preview-chip--muted">{{ $layoutLabel }}</span>
            @if ($page->show_in_footer)
                <span class="cms-preview-chip cms-preview-chip--muted">فوتر</span>
            @endif
            @if ($page->noindex ?? false)
                <span class="cms-preview-chip cms-preview-chip--muted">NOINDEX</span>
            @endif
        </div>

        <div class="cms-preview-chrome__actions">
            <div class="cms-preview-locale" role="group" aria-label="اللغة">
                <a href="{{ route('admin.cms-pages.preview', ['page' => $page->id, 'locale' => 'ar']) }}" @class(['is-active' => $previewLocale === 'ar'])>عربي</a>
                <a href="{{ route('admin.cms-pages.preview', ['page' => $page->id, 'locale' => 'en']) }}" @class(['is-active' => $previewLocale === 'en'])">EN</a>
            </div>
            <a href="{{ route('admin.cms-pages.edit', $page) }}" class="cms-preview-btn cms-preview-btn--primary">تعديل</a>
            @if ($publicUrl)
                <a href="{{ $publicUrl }}" class="cms-preview-btn" target="_blank" rel="noopener">عرض منشور</a>
            @endif
            <a href="{{ route('admin.cms-pages') }}" class="cms-preview-btn">← القائمة</a>
        </div>
    </div>
</header>
