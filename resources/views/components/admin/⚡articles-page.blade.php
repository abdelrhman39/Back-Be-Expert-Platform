<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\ArticleService;
use App\Support\ArticleOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الأخبار والفعاليات | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function togglePublish(int $articleId): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);

        $article = Article::query()->with('translations')->findOrFail($articleId);
        $next = $article->status === 'published' ? 'draft' : 'published';

        app(ArticleService::class)->setStatus($article, $next);

        session()->flash('admin_message', $next === 'published' ? 'تم نشر المقال.' : 'تم إلغاء نشر المقال.');
    }

    public function deleteArticle(int $articleId): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);

        $article = Article::query()->findOrFail($articleId);
        app(ArticleService::class)->delete($article);

        session()->flash('admin_message', 'تم حذف المقال.');
    }

    #[Computed]
    public function stats(): array
    {
        return app(ArticleService::class)->stats();
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return ArticleCategory::query()
            ->orderBy('sort_order')
            ->pluck('name_ar', 'id')
            ->all();
    }

    #[Computed]
    public function articles()
    {
        return Article::query()
            ->with(['translations', 'creator', 'articleCategory'])
            ->when($this->search, fn ($q) => $q->whereHas('translations', fn ($t) => $t
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->category, fn ($q) => $q->where('article_category_id', $this->category))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.articles'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الأخبار والفعاليات'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>الأخبار والفعاليات <span class="admin-crud-card__meta">— {{ $this->stats['published'] }} منشور / {{ $this->stats['total'] }} إجمالي</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="العنوان أو الرابط">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (ArticleOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>التصنيف</label>
            <select class="admin-control" wire:model.live="category">
                <option value="">الكل</option>
                @foreach ($this->categoryOptions as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>المقالات</h2>
        @canAdmin('articles.manage')
            <a href="{{ route('admin.articles.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ مقال جديد</a>
            <a href="{{ route('admin.article-categories') }}" class="admin-btn-secondary admin-btn-secondary--sm">التصنيفات</a>
        @endcanAdmin
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنوان</th>
                    <th>التصنيف</th>
                    <th>الحالة</th>
                    <th>تاريخ النشر</th>
                    <th>مميز</th>
                    <th><span class="visually-hidden">إجراءات</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->articles as $article)
                    @php $t = $article->translate('ar') ?? $article->translate(); @endphp
                    <tr wire:key="article-row-{{ $article->id }}">
                        <td>{{ $article->id }}</td>
                        <td>
                            <strong>{{ $t?->title ?? '—' }}</strong>
                            @if ($t?->slug)
                                <div><code class="admin-code" dir="ltr">{{ $t->slug }}</code></div>
                            @endif
                        </td>
                        <td>{{ ArticleOptions::articleCategoryLabel($article) }}</td>
                        <td>{{ ArticleOptions::statusLabel($article->status) }}</td>
                        <td dir="ltr">{{ $article->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $article->is_featured ? 'نعم' : '—' }}</td>
                        <td>
                            <div class="admin-row-actions">
                                @if ($article->publicUrl())
                                    <a href="{{ $article->publicUrl() }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة</a>
                                @endif
                                @canAdmin('articles.manage')
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="togglePublish({{ $article->id }})">
                                        {{ $article->status === 'published' ? 'إلغاء النشر' : 'نشر' }}
                                    </button>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteArticle({{ $article->id }})" wire:confirm="حذف هذا المقال؟">حذف</button>
                                @endcanAdmin
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:2rem">لا توجد مقالات بعد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->articles->hasPages())
        {{ $this->articles->links() }}
    @endif
</section>

@push('styles')
<style>.admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;}</style>
@endpush

@include('partials.admin.shell-end')
