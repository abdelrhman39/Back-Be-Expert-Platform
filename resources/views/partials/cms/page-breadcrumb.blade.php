@php
    $locale = app()->getLocale();
    $title = $translation->title ?? '';
@endphp

<div class="breadcrumb-bar breadcrumb-bar-info breadcrumb-info cms-page-breadcrumb">
    <div class="breadcrumb-img">
        <div class="breadcrumb-left">
            <img src="{{ static_asset('assets/banner-bg-03.png') }}" alt="">
        </div>
    </div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                    </ol>
                </nav>
                <h1 class="breadcrumb-title my-3">{{ $title }}</h1>
            </div>
        </div>
    </div>
</div>
