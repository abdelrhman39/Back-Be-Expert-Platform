@php
    use App\Services\ArticleService;

    $data = $block['data'] ?? [];
    $source = $data['source'] ?? 'latest_articles';
    $limit = max(1, (int) ($data['limit'] ?? 6));
    $badge = $data['badge'] ?? 'الاخبار والفعاليات';
    $locale = $locale ?? app()->getLocale();
    $allUrl = route('articles.index', ['locale' => $locale]);

    if ($source === 'latest_articles') {
        $articles = collect($latestArticles ?? app(ArticleService::class)->latestPublished($limit, $locale))
            ->filter(fn ($article) => (bool) $article->translate($locale))
            ->take($limit)
            ->values();
        $slideCount = $articles->count();
    } else {
        $manualItems = collect($data['items'] ?? [])->values();
        $slideCount = $manualItems->count();
        $articles = collect();
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

        @if ($slideCount === 0)
            <div class="text-center py-4 text-muted">لا توجد أخبار منشورة حالياً.</div>
        @else
            <div class="blog home-catalog-slider-wrap" data-aos="fade-up">
                <div
                    class="js-home-catalog-slider owl-carousel owl-rtl"
                    data-slides="{{ min(3, $slideCount) }}"
                >
                    @if ($source === 'latest_articles')
                        @foreach ($articles as $article)
                            @php $t = $article->translate($locale); @endphp
                            <div class="home-catalog-slide">
                                <article class="blog-grid home-news-card">
                                    <div class="blog-img">
                                        <a href="{{ $article->publicUrl($locale) }}">
                                            <img src="{{ $article->featuredImageUrl() }}" class="img-fluid" alt="{{ $t->title }}" loading="lazy">
                                        </a>
                                    </div>
                                    <div class="blog-content">
                                        <div class="user-head">
                                            <div class="badge-text">
                                                <span class="badge bg-primary-light">{{ $article->categoryDisplayName($locale) }}</span>
                                            </div>
                                        </div>
                                        <div class="blog-title">
                                            <h3>
                                                <a href="{{ $article->publicUrl($locale) }}">{{ $t->title }}</a>
                                            </h3>
                                        </div>
                                        @if ($t->excerpt)
                                            <p class="home-news-card__excerpt">{{ \Illuminate\Support\Str::limit($t->excerpt, 120) }}</p>
                                        @endif
                                        <div class="gigs-card-footer justify-content-start gap-2">
                                            <a class="btn btn-primary" href="{{ $article->publicUrl($locale) }}">
                                                مزيد من التفاصيل <i class="feather-eye pe-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    @else
                        @foreach ($manualItems as $item)
                            <div class="home-catalog-slide">
                                <article class="blog-grid home-news-card">
                                    <div class="blog-img">
                                        <a href="{{ cms_href($item['url'] ?? '#') }}">
                                            <img src="{{ resolve_poster_url($item['image'] ?? null) }}" class="img-fluid" alt="{{ $item['title'] ?? '' }}">
                                        </a>
                                    </div>
                                    <div class="blog-content">
                                        <div class="user-head">
                                            <div class="badge-text">
                                                <span class="badge bg-primary-light">{{ $badge }}</span>
                                            </div>
                                        </div>
                                        <div class="blog-title">
                                            <h3>
                                                <a href="{{ cms_href($item['url'] ?? '#') }}">{{ $item['title'] ?? '' }}</a>
                                            </h3>
                                        </div>
                                        <div class="gigs-card-footer justify-content-start gap-2">
                                            <a class="btn btn-primary" href="{{ cms_href($item['url'] ?? '#') }}">
                                                مزيد من التفاصيل <i class="feather-eye pe-2"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="home-catalog-section__cta">
                <a href="{{ $allUrl }}" class="btn btn-primary">
                    جميع الأخبار والفعاليات
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    </div>
</section>
