@php
    use App\Services\ArticleService;

    $data = $block['data'] ?? [];
    $source = $data['source'] ?? 'latest_articles';
    $limit = max(1, min(4, (int) ($data['limit'] ?? 4)));
    $badge = $data['badge'] ?? 'الاخبار والفعاليات';
    $locale = $locale ?? app()->getLocale();
    $isEn = $locale === 'en';
    $allUrl = route('articles.index', ['locale' => $locale]);

    if ($source === 'latest_articles') {
        $items = collect($latestArticles ?? app(ArticleService::class)->latestPublished($limit, $locale))
            ->filter(fn ($article) => (bool) $article->translate($locale))
            ->take($limit)
            ->values()
            ->map(fn ($article) => [
                'title' => $article->translate($locale)?->title,
                'excerpt' => $article->translate($locale)?->excerpt,
                'url' => $article->publicUrl($locale),
                'image' => $article->featuredImageUrl(),
                'badge' => $article->categoryDisplayName($locale),
                'date' => optional($article->published_at)->translatedFormat('d F Y'),
            ]);
    } else {
        $items = collect($data['items'] ?? [])->take($limit)->values()->map(fn ($item) => [
            'title' => $item['title'] ?? '',
            'excerpt' => $item['excerpt'] ?? '',
            'url' => cms_href($item['url'] ?? '#'),
            'image' => resolve_poster_url($item['image'] ?? null),
            'badge' => $badge,
            'date' => $item['date'] ?? null,
        ]);
    }
@endphp

<section class="explore-gigs-section home-catalog-section home-news-section">
    <div class="container">
        @if ($data['title'] ?? null)
            <div class="section-head home-catalog-section__head">
                <div class="section-header" data-aos="fade-up">
                    <h2>{{ $data['title'] }}</h2>
                </div>
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="text-center py-4 text-muted">{{ $isEn ? 'No news published yet.' : 'لا توجد أخبار منشورة حالياً.' }}</div>
        @else
            <div class="lg-news-grid lg-news-grid--centered" data-aos="fade-up">
                @foreach ($items as $item)
                    <article class="lg-news-card">
                        <a class="lg-news-card__media" href="{{ $item['url'] }}">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                        </a>
                        <div class="lg-news-card__body">
                            @if ($item['badge'])
                                <span class="lg-news-card__badge">{{ $item['badge'] }}</span>
                            @endif
                            <h3 class="lg-news-card__title">
                                <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                            </h3>
                            @if ($item['excerpt'])
                                <p class="lg-news-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($item['excerpt']), 90) }}</p>
                            @endif
                            <div class="lg-news-card__meta">
                                @if ($item['date'])
                                    <span>{{ $item['date'] }}</span>
                                @endif
                                <a href="{{ $item['url'] }}">{{ $isEn ? 'Read more' : 'المزيد' }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="home-catalog-section__cta">
                <a href="{{ $allUrl }}" class="btn btn-primary">
                    {{ $isEn ? 'All news & events' : 'جميع الأخبار والفعاليات' }}
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    </div>
</section>
