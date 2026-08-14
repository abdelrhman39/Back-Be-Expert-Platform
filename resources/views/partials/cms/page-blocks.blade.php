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
    @endphp

    @if (view()->exists($partial))
        @include($partial, [
            'block' => $block,
            'locale' => $locale ?? app()->getLocale(),
            ...$context,
        ])
    @endif
@endforeach
