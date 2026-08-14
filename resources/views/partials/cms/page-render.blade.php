{{-- Renders a published CMS page: blocks, HTML body, or empty shell. Never falls back to legacy static. --}}
@php
    use App\Support\CmsBlockDefaults;

    $page = $page ?? null;
    $translation = $translation ?? $page?->translate();
    $pageType = $pageType ?? ($page?->type ?? 'custom');
    $locale = $locale ?? app()->getLocale();
    $context = $context ?? [];
    $forceShowTitle = $forceShowTitle ?? null;

    $mode = $page?->content_mode
        ?? (CmsBlockDefaults::defaultContentMode($pageType));

    $useBlocks = $page
        && $mode === 'blocks'
        && CmsBlockDefaults::hasConfiguredBlocks($translation?->blocks);

    $showTitle = $forceShowTitle ?? ($page?->show_title ?? true);
    $layout = $page?->layout ?? 'default';
@endphp

@if ($useBlocks)
    @include('partials.cms.page-blocks', [
        'blocks' => $translation->blocks,
        'pageType' => $pageType,
        'locale' => $locale,
        'context' => $context,
    ])
@elseif (filled($translation?->body))
    @include('partials.cms.page-body', [
        'translation' => $translation,
        'layout' => $layout,
        'showTitle' => $showTitle,
    ])
@elseif ($page)
    @include('partials.cms.page-empty', [
        'page' => $page,
        'translation' => $translation,
    ])
@else
    @include('partials.cms.page-empty', [
        'page' => null,
        'translation' => null,
    ])
@endif
