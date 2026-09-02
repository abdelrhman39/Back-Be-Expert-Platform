<?php

use App\Services\ArticleCategoryService;
use App\Services\ArticleService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app-inner')]
#[Title('الأخبار والفعاليات | مركز التعلم المستمر')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'category', except: '')]
    public string $category = '';

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return app(ArticleCategoryService::class)->active();
    }

    public function articles()
    {
        $categoryId = filled($this->category) ? (int) $this->category : null;

        return app(ArticleService::class)->paginatePublished(12, $categoryId);
    }
};
?>

<section class="breadcrumb-bar">
    <div class="container">
        <nav aria-label="breadcrumb" class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => app()->getLocale()]) }}">الرئيسية</a></li>
                <li class="breadcrumb-item active" aria-current="page">الأخبار والفعاليات</li>
            </ol>
        </nav>
    </div>
</section>

<section class="explore-gigs-section" style="padding: 2.5rem 0 4rem;">
    <div class="container">
        <div class="section-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="section-header">
                <h2>الأخبار والفعاليات</h2>
            </div>
            @if ($this->categories->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <label for="articles-category" class="small text-muted mb-0">التصنيف</label>
                    <select id="articles-category" class="form-select form-select-sm" style="min-width: 12rem;" wire:model.live="category">
                        <option value="">الكل</option>
                        @foreach ($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="blog">
            <div class="row">
                @forelse ($this->articles as $article)
                    @php $t = $article->translate(); @endphp
                    @continue(! $t)
                    <div class="col-md-4 col-sm-6" wire:key="article-{{ $article->id }}">
                        <div class="blog-grid">
                            <div class="blog-img">
                                <a href="{{ $article->publicUrl() }}">
                                    <img src="{{ $article->featuredImageUrl() }}" class="img-fluid" alt="{{ $t->title }}" loading="lazy">
                                </a>
                            </div>
                            <div class="blog-content">
                                <div class="user-head">
                                    <div class="badge-text">
                                        <span class="badge bg-primary-light">{{ $article->categoryDisplayName() }}</span>
                                    </div>
                                </div>
                                <div class="blog-title">
                                    <h3><a href="{{ $article->publicUrl() }}">{{ $t->title }}</a></h3>
                                </div>
                                @if ($t->excerpt)
                                    <p class="text-muted small">{{ $t->excerpt }}</p>
                                @endif
                                <div class="gigs-card-footer justify-content-start gap-2">
                                    <a class="btn btn-primary" href="{{ $article->publicUrl() }}">
                                        {{ app()->getLocale() === 'en' ? 'Read more' : 'مزيد من التفاصيل' }} <i class="feather-eye pe-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">{{ app()->getLocale() === 'en' ? 'No news published yet.' : 'لا توجد أخبار منشورة حالياً.' }}</div>
                @endforelse
            </div>
            @if ($this->articles->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $this->articles->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
