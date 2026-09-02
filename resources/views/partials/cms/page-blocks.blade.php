@php
    use App\Support\CmsBlockDefaults;

    $blocks = CmsBlockDefaults::normalize($blocks ?? null, $pageType ?? 'custom', $locale ?? app()->getLocale());
    $context = $context ?? [];
@endphp

@foreach ($blocks as $block)
    @continue(! ($block['enabled'] ?? true))

    @php
        $type = $block['type'] ?? 'unknown';
        $partial = 'partials.cms.blocks.'.$type;
        $blockLocale = $locale ?? app()->getLocale();
        $block['data'] = cms_text_deep($block['data'] ?? [], $blockLocale);
    @endphp

    @if (view()->exists($partial))
        @include($partial, [
            'block' => $block,
            'locale' => $blockLocale,
            ...$context,
        ])
    @endif
@endforeach
