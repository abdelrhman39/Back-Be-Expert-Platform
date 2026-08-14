@php
    use App\Support\CmsBlockLink;

    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $buttonStyles = [
        'primary' => 'btn btn-primary',
        'outline-primary' => 'btn btn-outline-primary',
        'outline-secondary' => 'btn btn-outline-secondary',
        'secondary' => 'btn btn-secondary',
    ];
@endphp

<div class="contact-page-intro">
    <div class="container py-4 py-md-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                @if (filled($data['title'] ?? null))
                    <h2 class="h5 fw-bold text-dark mb-3">{{ $data['title'] }}</h2>
                @endif
                @if (filled($data['body'] ?? null))
                    <p class="lead text-muted mb-0">{{ $data['body'] }}</p>
                @endif
            </div>
            @if (! empty($data['buttons']))
                <div class="col-lg-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        @foreach ($data['buttons'] as $button)
                            @php
                                $style = $buttonStyles[$button['style'] ?? 'primary'] ?? 'btn btn-primary';
                                $href = CmsBlockLink::href($button, $locale);
                            @endphp
                            <a class="{{ $style }}" href="{{ $href }}">{{ $button['label'] ?? '' }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
