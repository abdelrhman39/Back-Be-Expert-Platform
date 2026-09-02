@php
    $data = $block['data'] ?? [];
    $locale = $locale ?? app()->getLocale();
    $backgroundImage = $data['background_image'] ?? platform_campus_path('aerial');
@endphp

<div class="breadcrumb-bar">
    <div class="breadcrumb-img">
        <div class="breadcrumb-left">
            <img src="{{ static_asset($backgroundImage) }}" alt="">
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home', ['locale' => $locale]) }}">{{ $data['parent_label'] ?? 'الرئيسية' }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $data['title'] ?? '' }}</li>
                    </ol>
                </nav>
                <h1 class="breadcrumb-title">{{ $data['title'] ?? '' }}</h1>
            </div>
        </div>
    </div>
</div>
