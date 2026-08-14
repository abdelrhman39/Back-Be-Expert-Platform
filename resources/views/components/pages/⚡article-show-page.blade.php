<?php

use App\Models\Article;
use App\Services\ArticleService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
class extends Component
{
    public Article $article;

    public function mount(string $slug, ArticleService $articles): void
    {
        $found = $articles->findPublishedBySlug($slug);

        if (! $found) {
            abort(404);
        }

        $this->article = $found;
    }

    public function title(): string
    {
        $t = $this->article->translate();

        return ($t?->meta_title ?: $t?->title ?: 'مقال').' | مركز التعلم المستمر';
    }
};
?>

@php
    $translation = $article->translate();
    $locale = app()->getLocale();
@endphp

@if ($translation?->meta_description)
    @push('head')
        <meta name="description" content="{{ $translation->meta_description }}">
    @endpush
@endif

<div>
    <section class="breadcrumb-bar">
        <div class="container">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('articles.index', ['locale' => $locale]) }}">الأخبار والفعاليات</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $translation?->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <article class="container" style="padding: 2rem 0 4rem; max-width: 52rem;">
        <header style="margin-bottom: 1.5rem;">
            <span class="badge bg-primary-light mb-2">{{ $article->categoryDisplayName($locale) }}</span>
            <h1 style="font-size: 1.75rem; font-weight: 800; line-height: 1.4;">{{ $translation?->title }}</h1>
            @if ($article->published_at)
                <time class="text-muted small" datetime="{{ $article->published_at->toIso8601String() }}">
                    {{ $article->published_at->translatedFormat('j F Y') }}
                </time>
            @endif
        </header>

        <div style="margin-bottom: 2rem; border-radius: 12px; overflow: hidden;">
            @if ($embed = $article->videoEmbedUrl())
                <div class="ratio ratio-16x9">
                    <iframe src="{{ $embed }}" title="{{ $translation?->title }}" allowfullscreen loading="lazy" style="border:0;width:100%;min-height:320px;border-radius:12px;"></iframe>
                </div>
            @else
                <img src="{{ $article->featuredImageUrl() }}" alt="{{ $translation?->title }}" class="img-fluid w-100">
            @endif
        </div>

        @if ($translation?->excerpt)
            <p class="lead" style="font-size: 1.05rem; color: #475569; margin-bottom: 1.5rem;">{{ $translation->excerpt }}</p>
        @endif

        <div class="cms-article-body">
            {!! $translation?->body !!}
        </div>
    </article>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cms-public.css') }}">
@endpush
