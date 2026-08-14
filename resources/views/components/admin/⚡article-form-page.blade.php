<?php

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Services\ArticleService;
use App\Support\ArticleOptions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مقال | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public ?int $articleId = null;

    #[Url(as: 'tab')]
    public string $activeTab = 'ar';

    public string $status = 'draft';

    public ?int $articleCategoryId = null;

    public bool $isFeatured = false;

    public int $sortOrder = 0;

    public string $featuredImage = '';

    public $featuredImageUpload = null;

    public string $videoUrl = '';

    public string $legacySlug = '';

    public string $internalNotes = '';

    public string $publishedAt = '';

    public string $titleAr = '';

    public string $slugAr = '';

    public string $excerptAr = '';

    public string $metaTitleAr = '';

    public string $metaDescriptionAr = '';

    public string $ogImageAr = '';

    public string $bodyAr = '';

    public string $titleEn = '';

    public string $slugEn = '';

    public string $excerptEn = '';

    public string $metaTitleEn = '';

    public string $metaDescriptionEn = '';

    public string $ogImageEn = '';

    public string $bodyEn = '';

    public function mount(?Article $article = null): void
    {
        abort_unless(auth()->user()?->canAdmin('articles.manage'), 403);

        if (! $article) {
            $this->articleCategoryId = ArticleCategory::query()
                ->where('slug', 'news')
                ->value('id');

            return;
        }

        $this->articleId = $article->id;
        $article->load(['translations', 'articleCategory']);

        $this->status = $article->status;
        $this->articleCategoryId = $article->article_category_id;
        $this->isFeatured = $article->is_featured;
        $this->sortOrder = $article->sort_order;
        $this->featuredImage = $article->featured_image ?? '';
        $this->videoUrl = $article->video_url ?? '';
        $this->legacySlug = $article->legacy_slug ?? '';
        $this->internalNotes = $article->internal_notes ?? '';
        $this->publishedAt = $article->published_at?->format('Y-m-d\TH:i') ?? '';

        $ar = $article->translations->firstWhere('locale', 'ar');
        $en = $article->translations->firstWhere('locale', 'en');

        if ($ar) {
            $this->titleAr = $ar->title;
            $this->slugAr = $ar->slug;
            $this->excerptAr = $ar->excerpt ?? '';
            $this->metaTitleAr = $ar->meta_title ?? '';
            $this->metaDescriptionAr = $ar->meta_description ?? '';
            $this->ogImageAr = $ar->og_image ?? '';
            $this->bodyAr = $ar->body ?? '';
        }

        if ($en) {
            $this->titleEn = $en->title;
            $this->slugEn = $en->slug;
            $this->excerptEn = $en->excerpt ?? '';
            $this->metaTitleEn = $en->meta_title ?? '';
            $this->metaDescriptionEn = $en->meta_description ?? '';
            $this->ogImageEn = $en->og_image ?? '';
            $this->bodyEn = $en->body ?? '';
        }
    }

    #[Computed]
    public function categories()
    {
        return ArticleCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['ar', 'en', 'seo', 'settings'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function removeFeaturedImage(): void
    {
        $this->featuredImage = '';
        $this->featuredImageUpload = null;
    }

    public function saveDraft(ArticleService $articles): void
    {
        $this->persist('draft', $articles);
    }

    public function publish(ArticleService $articles): void
    {
        $this->persist('published', $articles);
    }

    protected function persist(string $targetStatus, ArticleService $articles): void
    {
        if (in_array($targetStatus, ['draft', 'published'], true)) {
            $this->status = $targetStatus;
        }

        $validated = $this->validate($this->rules(), [], $this->attributes());

        $featuredImage = $this->featuredImage;

        if ($this->featuredImageUpload) {
            $featuredImage = $articles->storeFeaturedImage($this->featuredImageUpload);
        }

        $article = $this->articleId
            ? Article::query()->findOrFail($this->articleId)
            : null;

        $publishedAt = $this->publishedAt
            ? \Illuminate\Support\Carbon::parse($this->publishedAt)
            : null;

        $saved = $articles->save([
            'status' => $validated['status'],
            'article_category_id' => $this->articleCategoryId,
            'featured_image' => $featuredImage ?: null,
            'video_url' => $this->videoUrl ?: null,
            'is_featured' => $this->isFeatured,
            'sort_order' => $this->sortOrder,
            'legacy_slug' => $this->legacySlug ?: null,
            'internal_notes' => $this->internalNotes ?: null,
            'published_at' => $publishedAt,
            'translations' => [
                'ar' => [
                    'title' => $this->titleAr,
                    'slug' => $this->slugAr,
                    'excerpt' => $this->excerptAr,
                    'meta_title' => $this->metaTitleAr,
                    'meta_description' => $this->metaDescriptionAr,
                    'og_image' => $this->ogImageAr,
                    'body' => $this->bodyAr,
                ],
                'en' => filled($this->titleEn) ? [
                    'title' => $this->titleEn,
                    'slug' => $this->slugEn,
                    'excerpt' => $this->excerptEn,
                    'meta_title' => $this->metaTitleEn,
                    'meta_description' => $this->metaDescriptionEn,
                    'og_image' => $this->ogImageEn,
                    'body' => $this->bodyEn,
                ] : null,
            ],
        ], $article);

        $this->articleId = $saved->id;
        $this->featuredImage = $saved->featured_image ?? '';
        $this->featuredImageUpload = null;
        $this->publishedAt = $saved->published_at?->format('Y-m-d\TH:i') ?? '';

        session()->flash('admin_message', $this->status === 'published' ? 'تم نشر المقال.' : 'تم حفظ المسودة.');

        if (! $article) {
            $this->redirect(route('admin.articles.edit', ['article' => $saved, 'tab' => $this->activeTab]), navigate: true);
        }
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(ArticleOptions::statuses()))],
            'articleCategoryId' => ['nullable', 'integer', Rule::exists('article_categories', 'id')],
            'titleAr' => ['required', 'string', 'max:255'],
            'slugAr' => ['nullable', 'string', 'max:255'],
            'excerptAr' => ['nullable', 'string', 'max:1000'],
            'metaTitleAr' => ['nullable', 'string', 'max:255'],
            'metaDescriptionAr' => ['nullable', 'string', 'max:500'],
            'ogImageAr' => ['nullable', 'string', 'max:500'],
            'bodyAr' => ['nullable', 'string'],
            'titleEn' => ['nullable', 'string', 'max:255'],
            'slugEn' => ['nullable', 'string', 'max:255'],
            'excerptEn' => ['nullable', 'string', 'max:1000'],
            'metaTitleEn' => ['nullable', 'string', 'max:255'],
            'metaDescriptionEn' => ['nullable', 'string', 'max:500'],
            'ogImageEn' => ['nullable', 'string', 'max:500'],
            'bodyEn' => ['nullable', 'string'],
            'videoUrl' => ['nullable', 'string', 'max:500'],
            'publishedAt' => ['nullable', 'date'],
            'featuredImageUpload' => ['nullable', 'image', 'max:8192'],
        ];
    }

    /** @return array<string, string> */
    protected function attributes(): array
    {
        return [
            'titleAr' => 'العنوان (عربي)',
            'bodyAr' => 'المحتوى (عربي)',
            'featuredImageUpload' => 'صورة البوستر',
            'articleCategoryId' => 'التصنيف',
        ];
    }
};
?>

@php
    $pageTitle = $articleId ? 'تعديل مقال' : 'مقال جديد';
    $previewArticle = $articleId ? Article::query()->with('translations')->find($articleId) : null;
    $mediaUploadUrl = route('admin.articles.media');
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.articles'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.articles'), 'label' => 'الأخبار والفعاليات'],
        ['label' => $pageTitle],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<section class="admin-crud-card article-admin-form">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $pageTitle }}</h2>
            @if ($articleId)
                <p class="admin-crud-card__meta">
                    #{{ $articleId }} —
                    <span class="{{ ArticleOptions::statusBadgeClass($status) }}">{{ ArticleOptions::statusLabel($status) }}</span>
                </p>
            @endif
        </div>
        <div class="article-admin-head-actions">
            @if ($previewArticle?->publicUrl())
                <a href="{{ $previewArticle->publicUrl() }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة عامة</a>
            @endif
            <a href="{{ route('admin.articles') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
        </div>
    </div>

    <form wire:submit="saveDraft">
        <div class="article-admin-layout">
            <div class="article-admin-main">
                <div class="article-admin-tabs" role="tablist">
                    @foreach (['ar' => 'عربي', 'en' => 'English', 'seo' => 'SEO', 'settings' => 'إعدادات متقدمة'] as $key => $label)
                        <button type="button" @class(['article-admin-tab', 'is-active' => $activeTab === $key]) wire:click="setTab('{{ $key }}')" role="tab">{{ $label }}</button>
                    @endforeach
                </div>

                @if ($activeTab === 'ar')
                    <div class="article-admin-tab-panel">
                        <div class="admin-filter-grid cms-admin-grid-2">
                            <div class="admin-field">
                                <label>العنوان *</label>
                                <input type="text" class="admin-control" wire:model="titleAr">
                                @error('titleAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                            </div>
                            <div class="admin-field">
                                <label>الرابط (slug)</label>
                                <input type="text" class="admin-control" wire:model="slugAr" dir="ltr" placeholder="يُولَّد تلقائياً من العنوان">
                            </div>
                        </div>
                        <div class="admin-field">
                            <label>مقتطف قصير</label>
                            <textarea class="admin-control" rows="3" wire:model="excerptAr" placeholder="يظهر في بطاقة الخبر وصفحات القوائم"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>المحتوى</label>
                            @include('partials.admin.wysiwyg', [
                                'model' => 'bodyAr',
                                'value' => $bodyAr,
                                'direction' => 'rtl',
                                'language' => 'ar',
                                'height' => 480,
                                'uploadUrl' => $mediaUploadUrl,
                            ])
                            <p class="admin-field-hint">يمكنك إدراج صور وفيديو (YouTube/Vimeo) من شريط الأدوات.</p>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'en')
                    <div class="article-admin-tab-panel">
                        <div class="admin-filter-grid cms-admin-grid-2">
                            <div class="admin-field">
                                <label>Title</label>
                                <input type="text" class="admin-control" wire:model="titleEn" dir="ltr">
                            </div>
                            <div class="admin-field">
                                <label>Slug</label>
                                <input type="text" class="admin-control" wire:model="slugEn" dir="ltr">
                            </div>
                        </div>
                        <div class="admin-field">
                            <label>Excerpt</label>
                            <textarea class="admin-control" rows="3" wire:model="excerptEn" dir="ltr"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>Body</label>
                            @include('partials.admin.wysiwyg', [
                                'model' => 'bodyEn',
                                'value' => $bodyEn,
                                'direction' => 'ltr',
                                'language' => 'en',
                                'height' => 480,
                                'uploadUrl' => $mediaUploadUrl,
                            ])
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'seo')
                    <div class="article-admin-tab-panel">
                        <div class="article-admin-locale-block">
                            <h3 class="admin-form-section__title">SEO — العربية</h3>
                            <div class="admin-field">
                                <label>عنوان SEO</label>
                                <input type="text" class="admin-control" wire:model="metaTitleAr" placeholder="يُستخدم العنوان إن تُرك فارغاً">
                            </div>
                            <div class="admin-field">
                                <label>وصف SEO</label>
                                <textarea class="admin-control" rows="2" wire:model="metaDescriptionAr" maxlength="320"></textarea>
                            </div>
                            <div class="admin-field">
                                @include('partials.admin.media-field', [
                                    'wireModel' => 'ogImageAr',
                                    'id' => 'ogImageAr',
                                    'label' => 'صورة Open Graph',
                                    'previewUrl' => filled($ogImageAr) ? resolve_poster_url($ogImageAr) : null,
                                    'placeholder' => 'https://... أو /storage/...',
                                ])
                            </div>
                        </div>
                        <div class="article-admin-locale-block">
                            <h3 class="admin-form-section__title">SEO — English</h3>
                            <div class="admin-field">
                                <label>Meta title</label>
                                <input type="text" class="admin-control" wire:model="metaTitleEn" dir="ltr">
                            </div>
                            <div class="admin-field">
                                <label>Meta description</label>
                                <textarea class="admin-control" rows="2" wire:model="metaDescriptionEn" maxlength="320" dir="ltr"></textarea>
                            </div>
                            <div class="admin-field">
                                @include('partials.admin.media-field', [
                                    'wireModel' => 'ogImageEn',
                                    'id' => 'ogImageEn',
                                    'label' => 'Open Graph image',
                                    'previewUrl' => filled($ogImageEn) ? resolve_poster_url($ogImageEn) : null,
                                    'placeholder' => 'https://... or /storage/...',
                                ])
                            </div>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'settings')
                    <div class="article-admin-tab-panel">
                        <div class="admin-field">
                            <label>رابط قديم (legacy)</label>
                            <input type="text" class="admin-control" wire:model="legacySlug" dir="ltr" placeholder="ar/blog/old-post.html">
                            <p class="admin-field-hint">للمرجعية فقط — الروابط العامة تستخدم slug المقال.</p>
                        </div>
                        <div class="admin-field">
                            <label>ملاحظات داخلية</label>
                            <textarea class="admin-control" rows="4" wire:model="internalNotes" placeholder="ملاحظات الفريق — لا تظهر للزوار"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>ترتيب العرض</label>
                            <input type="number" class="admin-control" wire:model="sortOrder" min="0">
                        </div>
                    </div>
                @endif

                <div class="article-admin-form-actions">
                    <button type="submit" class="admin-btn-secondary admin-btn-secondary--sm">حفظ مسودة</button>
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="publish">حفظ ونشر</button>
                </div>
            </div>

            <aside class="article-admin-sidebar">
                <div class="article-admin-panel">
                    <h3 class="article-admin-panel__title">النشر</h3>
                    <div class="admin-field">
                        <label>الحالة</label>
                        <select class="admin-control" wire:model="status">
                            @foreach (ArticleOptions::statuses() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label>تاريخ النشر</label>
                        <input type="datetime-local" class="admin-control" wire:model="publishedAt" dir="ltr">
                        @error('publishedAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <label class="admin-check" style="margin-top:0.5rem;">
                        <input type="checkbox" wire:model="isFeatured">
                        <span>مقال مميز</span>
                    </label>
                </div>

                <div class="article-admin-panel">
                    <h3 class="article-admin-panel__title">التصنيف</h3>
                    <div class="admin-field">
                        <select class="admin-control" wire:model="articleCategoryId">
                            <option value="">— بدون تصنيف —</option>
                            @foreach ($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>
                    <a href="{{ route('admin.article-categories') }}" class="admin-btn-secondary admin-btn-secondary--sm" style="width:100%;text-align:center;">إدارة التصنيفات</a>
                </div>

                <div class="article-admin-panel">
                    <h3 class="article-admin-panel__title">صورة البوستر</h3>
                    <div
                        x-data
                        x-on:media-picker-selected.window="
                            if ($event.detail.target === 'featuredImage') {
                                $wire.set('featuredImage', $event.detail.url);
                                $wire.set('featuredImageUpload', null);
                            }
                        "
                    >
                        @if ($featuredImage || $featuredImageUpload)
                            <div class="article-poster-preview">
                                @if ($featuredImageUpload)
                                    <img src="{{ $featuredImageUpload->temporaryUrl() }}" alt="">
                                @else
                                    <img src="{{ resolve_poster_url($featuredImage) }}" alt="">
                                @endif
                                <button type="button" class="article-poster-preview__remove" wire:click="removeFeaturedImage">إزالة</button>
                            </div>
                        @endif
                        <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:0.65rem;">
                            <button type="button" class="admin-btn-primary admin-btn-primary--sm" onclick="Livewire.dispatch('open-media-picker', { target: 'featuredImage', accept: 'image', title: 'صورة بوستر المقال' })">
                                <i class="fa-regular fa-images"></i> من المكتبة / رفع جديد
                            </button>
                        </div>
                        <label class="article-poster-drop">
                            <input type="file" wire:model="featuredImageUpload" accept="image/*" hidden>
                            <span>أو اسحب صورة هنا للرفع المباشر</span>
                            <p class="article-poster-drop__hint">JPG, PNG, WebP — حتى 8 ميجابايت</p>
                        </label>
                        <div wire:loading wire:target="featuredImageUpload" class="admin-field-hint">جاري الرفع…</div>
                        @error('featuredImageUpload')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="article-admin-panel">
                    <h3 class="article-admin-panel__title">فيديو مميز</h3>
                    <div class="admin-field">
                        <label>رابط YouTube / Vimeo</label>
                        <input type="url" class="admin-control" wire:model="videoUrl" dir="ltr" placeholder="https://youtube.com/watch?v=...">
                        <p class="admin-field-hint">يُعرض أعلى المحتوى في صفحة المقال.</p>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/article-admin.css') }}">
@endpush

@script
<script>
    Livewire.hook('morph.updated', () => {
        if (window.domainWysiwyg) {
            window.domainWysiwyg.initAll(document);
        }
    });
</script>
@endscript

@include('partials.admin.shell-end')
