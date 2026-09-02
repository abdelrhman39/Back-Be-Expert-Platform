@php
    $data = $block['data'] ?? [];
    $items = $data['items'] ?? [];
    $sectionId = $data['section_id'] ?? null;

    if ($sectionId === 'section-mahara') {
        return;
    }
@endphp

<section @if($sectionId) id="{{ $sectionId }}" @endif class="explore-gigs-section">
    <div class="container">
        @if ($data['title'] ?? null)
            <div class="section-head d-flex">
                <div class="section-header aos" data-aos="fade-up">
                    <h2>{{ $data['title'] }}</h2>
                </div>
            </div>
        @endif
        <div class="blog">
            <div class="row">
                @foreach ($items as $item)
                    <div class="col-12 col-md-6">
                        <div class="blog-grid program">
                            <div class="blog-img">
                                <a href="{{ cms_href($item['url'] ?? '#') }}">
                                    <img src="{{ resolve_poster_url($item['image'] ?? null) }}" class="img-fluid" alt="">
                                </a>
                            </div>
                            @if ($item['title'] ?? null)
                                <div class="blog-content">
                                    <div class="blog-title">
                                        <h3><a href="{{ cms_href($item['url'] ?? '#') }}">{{ $item['title'] }}</a></h3>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                @if ($data['cta_label'] ?? null)
                    <div class="w-100 text-center mt-4">
                        <a href="{{ cms_href($data['cta_url'] ?? '#') }}" class="btn btn-primary">{{ $data['cta_label'] }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
