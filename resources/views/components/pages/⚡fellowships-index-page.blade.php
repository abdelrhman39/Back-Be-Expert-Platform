<?php

use App\Models\Fellowship;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('الزمالات المهنية | مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function fellowships()
    {
        return Fellowship::query()
            ->where('status', 'open')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
};
?>

@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
@endphp

<div class="atelier-about">
    <div class="breadcrumb-bar">
        <div class="breadcrumb-img">
            <div class="breadcrumb-left">
                <img src="{{ static_asset(platform_campus_path('aerial')) }}" alt="{{ platform_org() }}">
            </div>
        </div>
        <div class="container">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => $locale]) }}">{{ $isEn ? 'Home' : 'الرئيسية' }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('courses.index', ['locale' => $locale]) }}">{{ $isEn ? 'Programs' : 'البرامج' }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $isEn ? 'Professional fellowships' : 'الزمالات المهنية' }}</li>
                </ol>
            </nav>
            <h1 class="breadcrumb-title">{{ $isEn ? 'Professional fellowships' : 'الزمالات المهنية' }}</h1>
        </div>
    </div>

    <section class="about-intro">
        <div class="container">
            <div class="contact-intro" style="max-width:46rem;margin-bottom:2rem;">
                <span class="about-eyebrow">{{ $isEn ? 'Advanced professional tracks' : 'مسارات مهنية متقدمة' }}</span>
                <h2 class="about-intro__title">{{ $isEn ? 'Apply to an open fellowship' : 'قدّم على زمالة مهنية مفتوحة' }}</h2>
                <p class="about-intro__body">
                    {{ $isEn
                        ? 'Fellowships are specialized programs with a dedicated application form. Choose a track to review details and submit your request.'
                        : 'الزمالات برامج تخصصية بنموذج تقديم مستقل. اختر المسار للاطلاع على التفاصيل وإرسال الطلب.' }}
                </p>
            </div>

            <div class="np-paths__grid">
                @forelse ($this->fellowships as $fellowship)
                    <article class="np-paths__card">
                        <div class="np-paths__icon" aria-hidden="true">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <h3 class="np-paths__card-title">{{ $fellowship->displayTitle() }}</h3>
                        @if (filled($fellowship->displayDescription()))
                            <p class="np-paths__card-body">{{ \Illuminate\Support\Str::limit(strip_tags($fellowship->displayDescription()), 180) }}</p>
                        @endif
                        @if ($fellowship->acceptsApplications())
                            <a class="np-paths__link" href="{{ route('fellowship.apply', ['locale' => $locale, 'fellowship' => $fellowship]) }}">
                                <span>{{ $isEn ? 'Apply now' : 'قدّم الآن' }}</span>
                                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            </a>
                        @endif
                    </article>
                @empty
                    <div class="lg-program-teaser" style="grid-column:1/-1;">
                        <span class="lg-program-teaser__icon" aria-hidden="true"><i class="fa-solid fa-user-tie"></i></span>
                        <span class="lg-program-teaser__copy">
                            <strong>{{ $isEn ? 'No open fellowships right now' : 'لا توجد زمالات مفتوحة حالياً' }}</strong>
                            <span>{{ $isEn ? 'Browse certificates and diplomas, or contact us for upcoming tracks.' : 'تصفّح الشهادات والدبلومات، أو تواصل معنا لمعرفة المسارات القادمة.' }}</span>
                        </span>
                        <a class="lg-program-teaser__cta" href="{{ route('courses.index', ['locale' => $locale]) }}">{{ $isEn ? 'Browse programs' : 'تصفح البرامج' }}</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-identity-blocks.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/about-page.css') }}?v=3">
@endpush
