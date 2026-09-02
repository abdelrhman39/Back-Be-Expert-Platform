<?php

use App\Models\CmsPage;
use App\Services\CmsPageService;
use App\Support\CmsOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.cms-preview')]
#[Title('معاينة صفحة | لوحة التحكم')]
class extends Component
{
    public CmsPage $page;

    public string $previewLocale = 'ar';

    public function mount(CmsPage $page): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.view'), 403);

        $this->page = $page->load('translations');
        $this->previewLocale = in_array(request('locale', 'ar'), ['ar', 'en'], true)
            ? request('locale', 'ar')
            : 'ar';

        app()->setLocale($this->previewLocale);
    }

    public function title(): string
    {
        $t = $this->page->translate($this->previewLocale);

        return '[معاينة] '.($t?->meta_title ?: $t?->title ?: 'صفحة');
    }
};
?>

@php
    $cms = app(CmsPageService::class);
    $translation = $page->translate($previewLocale);
    $useBlocks = $page->usesBlocksContent()
        && $translation
        && \App\Support\CmsBlockDefaults::hasConfiguredBlocks($translation->blocks);
    $layout = $page->layout ?? 'default';
    $statusLabel = CmsOptions::pageStatuses()[$page->status] ?? $page->status;
    $typeLabel = CmsOptions::pageTypes()[$page->type] ?? $page->type;
    $layoutLabel = CmsOptions::pageLayouts()[$layout] ?? $layout;
    $publicUrl = $cms->publicUrl($page, $previewLocale);
    $pageTitle = $translation?->title ?? '—';
    $pageSlug = $translation?->slug;
@endphp

<div class="cms-preview-shell">
    <div class="cms-preview-canvas{{ in_array($page->type, ['contact', 'about'], true) ? ' atelier-'.$page->type : '' }}" dir="{{ $previewLocale === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ $previewLocale }}">
        @if ($translation)
            @php
                $blockContext = in_array($page->type, ['home'], true)
                    ? [
                        'popularFields' => app(\App\Services\HomePageService::class)->popularFields(),
                        'professionalCertificates' => app(\App\Services\HomePageService::class)->professionalCertificates(),
                        'diplomas' => app(\App\Services\HomePageService::class)->diplomas(),
                        'latestArticles' => app(\App\Services\ArticleService::class)->latestPublished(6, $previewLocale),
                        'heroMetrics' => app(\App\Services\HomePageService::class)->heroMetrics($previewLocale),
                    ]
                    : [];
            @endphp

            @include('partials.cms.page-render', [
                'page' => $page,
                'translation' => $translation,
                'pageType' => $page->type,
                'locale' => $previewLocale,
                'context' => $blockContext,
            ])
        @else
            <div class="container py-5">
                <div class="cms-page-empty">
                    <p>لا يوجد محتوى باللغة «{{ $previewLocale === 'ar' ? 'العربية' : 'English' }}».</p>
                    <a href="{{ route('admin.cms-pages.edit', $page) }}" class="cms-preview-btn cms-preview-btn--primary">إضافة المحتوى</a>
                </div>
            </div>
        @endif
    </div>
</div>

@push('preview-chrome')
    @include('partials.admin.cms-preview-chrome', compact(
        'page',
        'previewLocale',
        'cms',
        'translation',
        'layout',
        'statusLabel',
        'typeLabel',
        'layoutLabel',
        'publicUrl',
        'pageTitle',
        'pageSlug',
    ))
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cms-public.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cms-preview.css') }}">
    @if ($page->type === 'contact')
        <link rel="stylesheet" href="{{ asset('css/contact-page.css') }}?v=8">
    @endif
    @if ($page->type === 'about')
        <link rel="stylesheet" href="{{ asset('css/about-page.css') }}?v=3">
    @endif
@endpush

@push('scripts')
    <script>
        document.documentElement.classList.add('is-cms-preview');
        document.documentElement.lang = @json(str_replace('_', '-', $previewLocale));
        document.documentElement.dir = @json($previewLocale === 'ar' ? 'rtl' : 'ltr');

        (function () {
            function syncPreviewChromeHeight() {
                var bar = document.querySelector('.cms-preview-chrome');
                if (!bar) {
                    return;
                }

                document.documentElement.style.setProperty('--cms-preview-chrome-height', bar.offsetHeight + 'px');
            }

            syncPreviewChromeHeight();
            window.addEventListener('resize', syncPreviewChromeHeight);
        })();
    </script>
    @if ($page->type === 'contact' && $useBlocks)
        @include('partials.cms.contact-form-script')
    @endif
@endpush

@if ($page->noindex ?? false)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush
@endif

@if ($translation?->meta_description)
    @push('head')
        <meta name="description" content="{{ $translation->meta_description }}">
    @endpush
@endif
