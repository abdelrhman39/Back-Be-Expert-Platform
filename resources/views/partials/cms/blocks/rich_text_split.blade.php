@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $paragraphs = $data['paragraphs'] ?? [];
@endphp

<section class="about-us-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-inner-img">
                    <img src="{{ static_asset($data['image'] ?? 'assets/1853032368970233.png') }}" class="img-fluid" alt="">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-us-info">
                    <div class="about-us-head">
                        @if ($data['title'] ?? null)
                            <h2>{{ $data['title'] }}</h2>
                        @endif
                        @foreach ($paragraphs as $paragraph)
                            <p dir="auto" style="text-align: justify;">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
