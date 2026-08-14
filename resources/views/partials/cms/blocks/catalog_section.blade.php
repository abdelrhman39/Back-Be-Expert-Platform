@php
    $data = $block['data'] ?? [];
    $source = $data['source'] ?? '';
@endphp

@switch($source)
    @case('popular_fields')
        @include('partials.catalog.home-popular-fields', ['fields' => $popularFields ?? collect()])
        @break
    @case('certificates')
        @include('partials.catalog.home-featured-courses', ['courses' => $professionalCertificates ?? collect()])
        @break
    @case('diplomas')
        @include('partials.catalog.home-diplomas', ['diplomas' => $diplomas ?? collect()])
        @break
@endswitch
