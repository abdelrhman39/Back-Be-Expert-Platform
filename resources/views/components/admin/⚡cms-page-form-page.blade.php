<?php

use App\Models\CmsPage;
use App\Services\CmsMenuService;
use App\Services\CmsPageService;
use App\Support\CmsBlockDefaults;
use App\Support\CmsOptions;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('صفحة CMS | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;
    public ?int $pageId = null;

    #[Url(as: 'tab')]
    public string $activeTab = 'general';

    public string $type = 'custom';

    public string $layout = 'default';

    public string $contentMode = 'html';

    public string $status = 'draft';

    public int $sortOrder = 0;

    public bool $showInFooter = false;

    public bool $showTitle = true;

    public bool $noindex = false;

    public string $legacySlug = '';

    public string $internalNotes = '';

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

    /** @var list<array<string, mixed>> */
    public array $blocksAr = [];

    /** @var list<array<string, mixed>> */
    public array $blocksEn = [];

    public string $blocksLocale = 'ar';

    public string $newBlockType = 'rich_text_split';

    /** Temporary CMS image upload (logos / avatars). */
    public $cmsImageFile = null;

    public ?string $cmsImageTarget = null;

    public function mount(?CmsPage $page = null): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.manage'), 403);

        if (! $page) {
            $this->contentMode = CmsBlockDefaults::defaultContentMode($this->type);

            return;
        }

        $this->pageId = $page->id;
        $page->load('translations');

        $this->type = $page->type;
        $this->layout = $page->layout ?? 'default';
        $this->contentMode = $page->content_mode
            ?: CmsBlockDefaults::defaultContentMode($page->type);
        $this->status = $page->status;
        $this->sortOrder = $page->sort_order;
        $this->showInFooter = $page->show_in_footer;
        $this->showTitle = $page->show_title ?? true;
        $this->noindex = $page->noindex ?? false;
        $this->legacySlug = $page->legacy_slug ?? '';
        $this->internalNotes = $page->internal_notes ?? '';

        $ar = $page->translations->firstWhere('locale', 'ar');
        $en = $page->translations->firstWhere('locale', 'en');

        if ($ar) {
            $this->titleAr = $ar->title;
            $this->slugAr = $ar->slug;
            $this->excerptAr = $ar->excerpt ?? '';
            $this->metaTitleAr = $ar->meta_title ?? '';
            $this->metaDescriptionAr = $ar->meta_description ?? '';
            $this->ogImageAr = $ar->og_image ?? '';
            $this->bodyAr = $ar->body ?? '';
            $this->blocksAr = is_array($ar->blocks) ? array_values($ar->blocks) : [];
        }

        if ($en) {
            $this->titleEn = $en->title;
            $this->slugEn = $en->slug;
            $this->excerptEn = $en->excerpt ?? '';
            $this->metaTitleEn = $en->meta_title ?? '';
            $this->metaDescriptionEn = $en->meta_description ?? '';
            $this->ogImageEn = $en->og_image ?? '';
            $this->bodyEn = $en->body ?? '';
            $this->blocksEn = is_array($en->blocks) ? array_values($en->blocks) : [];
        }

        if ($this->contentMode === 'blocks' && $this->blocksAr === [] && CmsBlockDefaults::usesBlocks($this->type)) {
            $this->blocksAr = CmsBlockDefaults::forPageType($this->type, 'ar');
            $this->blocksEn = CmsBlockDefaults::forPageType($this->type, 'en');
        }
    }

    public function updatedType(string $value): void
    {
        if ($this->pageId) {
            return;
        }

        $this->contentMode = CmsBlockDefaults::defaultContentMode($value);

        if ($this->contentMode === 'blocks' && $this->blocksAr === []) {
            $this->blocksAr = CmsBlockDefaults::forPageType($value, 'ar');
            $this->blocksEn = CmsBlockDefaults::forPageType($value, 'en');
        }
    }

    public function updatedContentMode(string $value): void
    {
        if ($value !== 'blocks') {
            if ($this->activeTab === 'blocks') {
                $this->activeTab = 'general';
            }

            return;
        }

        if ($this->blocksAr === [] && CmsBlockDefaults::usesBlocks($this->type)) {
            $this->blocksAr = CmsBlockDefaults::forPageType($this->type, 'ar');
            $this->blocksEn = CmsBlockDefaults::forPageType($this->type, 'en');
        }
    }

    public function setTab(string $tab): void
    {
        $allowed = ['general', 'seo', 'blocks', 'ar', 'en', 'settings', 'preview'];

        if ($tab === 'blocks' && $this->contentMode !== 'blocks') {
            return;
        }

        if (in_array($tab, $allowed, true)) {
            $this->activeTab = $tab;
        }
    }

    public function resetBlocksDefaults(): void
    {
        if ($this->contentMode !== 'blocks') {
            return;
        }

        if ($this->blocksLocale === 'en') {
            $this->blocksEn = CmsBlockDefaults::forPageType($this->type, 'en')
                ?: ($this->blocksEn ?: []);
            if ($this->blocksEn === [] && $this->blocksAr !== []) {
                $this->blocksEn = $this->blocksAr;
            }
        } else {
            $defaults = CmsBlockDefaults::forPageType($this->type, 'ar');
            $this->blocksAr = $defaults !== [] ? $defaults : $this->blocksAr;
        }
    }

    public function addBlock(): void
    {
        if ($this->contentMode !== 'blocks') {
            return;
        }

        $types = array_keys(\App\Support\CmsBlockRegistry::types());

        if (! in_array($this->newBlockType, $types, true)) {
            return;
        }

        $skeleton = CmsBlockDefaults::skeleton($this->newBlockType);
        $prop = $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
        $this->{$prop}[] = $skeleton;
    }

    public function removeBlock(int $index): void
    {
        $prop = $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
        $blocks = $this->{$prop};
        unset($blocks[$index]);
        $this->{$prop} = array_values($blocks);
    }

    public function toggleBlockEnabled(int $index): void
    {
        $prop = $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
        $this->{$prop}[$index]['enabled'] = ! ($this->{$prop}[$index]['enabled'] ?? true);
    }

    public function moveBlockUp(int $index): void
    {
        $prop = $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';

        if ($index <= 0) {
            return;
        }

        $blocks = $this->{$prop};
        [$blocks[$index - 1], $blocks[$index]] = [$blocks[$index], $blocks[$index - 1]];
        $this->{$prop} = array_values($blocks);
    }

    public function moveBlockDown(int $index): void
    {
        $prop = $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
        $blocks = $this->{$prop};

        if ($index >= count($blocks) - 1) {
            return;
        }

        [$blocks[$index + 1], $blocks[$index]] = [$blocks[$index], $blocks[$index + 1]];
        $this->{$prop} = array_values($blocks);
    }

    public function addLogoItem(int $blockIndex): void
    {
        $prop = $this->blocksProp();
        $logos = $this->{$prop}[$blockIndex]['data']['logos'] ?? [];
        $logos[] = ['image' => '', 'alt' => ''];
        $this->{$prop}[$blockIndex]['data']['logos'] = array_values($logos);
    }

    public function removeLogoItem(int $blockIndex, int $logoIndex): void
    {
        $prop = $this->blocksProp();
        $logos = $this->{$prop}[$blockIndex]['data']['logos'] ?? [];
        unset($logos[$logoIndex]);
        $this->{$prop}[$blockIndex]['data']['logos'] = array_values($logos);
    }

    public function moveLogoItem(int $blockIndex, int $logoIndex, string $direction): void
    {
        $this->moveNestedItem($blockIndex, 'logos', $logoIndex, $direction);
    }

    public function addTestimonialItem(int $blockIndex): void
    {
        $prop = $this->blocksProp();
        $items = $this->{$prop}[$blockIndex]['data']['items'] ?? [];
        $items[] = ['quote' => '', 'name' => '', 'role' => '', 'avatar' => '', 'rating' => 5];
        $this->{$prop}[$blockIndex]['data']['items'] = array_values($items);
    }

    public function removeTestimonialItem(int $blockIndex, int $itemIndex): void
    {
        $prop = $this->blocksProp();
        $items = $this->{$prop}[$blockIndex]['data']['items'] ?? [];
        unset($items[$itemIndex]);
        $this->{$prop}[$blockIndex]['data']['items'] = array_values($items);
    }

    public function moveTestimonialItem(int $blockIndex, int $itemIndex, string $direction): void
    {
        $this->moveNestedItem($blockIndex, 'items', $itemIndex, $direction);
    }

    public function prepareCmsImageUpload(int $blockIndex, string $listKey, int $itemIndex, string $field): void
    {
        $this->cmsImageTarget = implode('|', [
            $this->blocksLocale,
            (string) $blockIndex,
            $listKey,
            (string) $itemIndex,
            $field,
        ]);
    }

    public function updatedCmsImageFile(): void
    {
        if (! $this->cmsImageFile || ! filled($this->cmsImageTarget)) {
            return;
        }

        $this->validate([
            'cmsImageFile' => ['required', 'image', 'max:4096'],
        ], [], [
            'cmsImageFile' => 'الصورة',
        ]);

        [$locale, $blockIndex, $listKey, $itemIndex, $field] = array_pad(explode('|', $this->cmsImageTarget), 5, null);
        $prop = $locale === 'en' ? 'blocksEn' : 'blocksAr';
        $blockIndex = (int) $blockIndex;
        $itemIndex = (int) $itemIndex;

        if (! isset($this->{$prop}[$blockIndex]['data'][$listKey][$itemIndex])) {
            $this->cmsImageFile = null;
            $this->cmsImageTarget = null;

            return;
        }

        $path = $this->cmsImageFile->storeAs(
            'media-library/cms',
            Str::uuid().'.'.$this->cmsImageFile->getClientOriginalExtension(),
            'public'
        );

        $this->{$prop}[$blockIndex]['data'][$listKey][$itemIndex][$field] = '/storage/'.$path;
        $this->cmsImageFile = null;
        $this->cmsImageTarget = null;
    }

    protected function blocksProp(): string
    {
        return $this->blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
    }

    protected function moveNestedItem(int $blockIndex, string $listKey, int $itemIndex, string $direction): void
    {
        $prop = $this->blocksProp();
        $items = array_values($this->{$prop}[$blockIndex]['data'][$listKey] ?? []);
        $swap = $direction === 'up' ? $itemIndex - 1 : $itemIndex + 1;

        if (! isset($items[$itemIndex], $items[$swap])) {
            return;
        }

        [$items[$itemIndex], $items[$swap]] = [$items[$swap], $items[$itemIndex]];
        $this->{$prop}[$blockIndex]['data'][$listKey] = array_values($items);
    }

    public function saveDraft(CmsPageService $pages, CmsMenuService $menus): void
    {
        $this->persist('draft', $pages, $menus);
    }

    public function publish(CmsPageService $pages, CmsMenuService $menus): void
    {
        $this->persist('published', $pages, $menus);
    }

    protected function persist(string $targetStatus, CmsPageService $pages, CmsMenuService $menus): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.manage'), 403);

        if (in_array($targetStatus, ['draft', 'published'], true)) {
            $this->status = $targetStatus;
        }

        $this->validate([
            'type' => ['required', Rule::in(array_keys(CmsOptions::pageTypes()))],
            'layout' => ['required', Rule::in(array_keys(CmsOptions::pageLayouts()))],
            'contentMode' => ['required', Rule::in(array_keys(CmsOptions::contentModes()))],
            'status' => ['required', Rule::in(array_keys(CmsOptions::pageStatuses()))],
            'sortOrder' => ['integer', 'min:0'],
            'titleAr' => ['required', 'string', 'max:255'],
            'slugAr' => ['nullable', 'string', 'max:191'],
            'excerptAr' => ['nullable', 'string', 'max:500'],
            'ogImageAr' => ['nullable', 'string', 'max:512'],
            'titleEn' => ['nullable', 'string', 'max:255'],
            'slugEn' => ['nullable', 'string', 'max:191'],
            'excerptEn' => ['nullable', 'string', 'max:500'],
            'ogImageEn' => ['nullable', 'string', 'max:512'],
        ], [], [
            'titleAr' => 'العنوان (عربي)',
            'slugAr' => 'الرابط (عربي)',
            'layout' => 'التخطيط',
            'contentMode' => 'وضع المحتوى',
        ]);

        $page = $this->pageId ? CmsPage::query()->findOrFail($this->pageId) : null;
        $useBlocks = $this->contentMode === 'blocks';

        $saved = $pages->save([
            'type' => $this->type,
            'layout' => $this->layout,
            'content_mode' => $this->contentMode,
            'status' => $this->status,
            'sort_order' => $this->sortOrder,
            'show_in_footer' => $this->showInFooter,
            'show_title' => $this->showTitle,
            'noindex' => $this->noindex,
            'legacy_slug' => $this->legacySlug ?: null,
            'internal_notes' => $this->internalNotes ?: null,
            'translations' => [
                'ar' => [
                    'title' => $this->titleAr,
                    'slug' => $this->slugAr ?: $this->titleAr,
                    'excerpt' => $this->excerptAr ?: null,
                    'meta_title' => $this->metaTitleAr ?: null,
                    'meta_description' => $this->metaDescriptionAr ?: null,
                    'og_image' => $this->ogImageAr ?: null,
                    'body' => $useBlocks ? null : ($this->bodyAr ?: null),
                    'blocks' => $useBlocks ? array_values($this->blocksAr) : null,
                ],
                'en' => $this->titleEn ? [
                    'title' => $this->titleEn,
                    'slug' => $this->slugEn ?: $this->titleEn,
                    'excerpt' => $this->excerptEn ?: null,
                    'meta_title' => $this->metaTitleEn ?: null,
                    'meta_description' => $this->metaDescriptionEn ?: null,
                    'og_image' => $this->ogImageEn ?: null,
                    'body' => $useBlocks ? null : ($this->bodyEn ?: null),
                    'blocks' => $useBlocks ? array_values($this->blocksEn) : null,
                ] : null,
            ],
        ], $page);

        $this->pageId = $saved->id;
        $menus->forgetCache();

        session()->flash('admin_message', $this->status === 'published' ? 'تم نشر الصفحة.' : 'تم حفظ المسودة.');

        if (! $page) {
            $this->redirect(route('admin.cms-pages.edit', $saved), navigate: true);
        }
    }
};
?>

@php
    $pageTitle = $pageId ? 'تعديل صفحة' : 'صفحة جديدة';
    $cms = app(CmsPageService::class);
    $previewPage = $pageId ? CmsPage::query()->with('translations')->find($pageId) : null;
@endphp

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.cms-pages'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.cms-pages'), 'label' => 'صفحات الموقع'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card cms-admin-form">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $pageTitle }}</h2>
            @if ($pageId)
                <p class="admin-crud-card__meta">#{{ $pageId }} — <span class="{{ CmsOptions::statusBadgeClass($status) }}">{{ CmsOptions::pageStatuses()[$status] ?? $status }}</span></p>
            @endif
        </div>
        <div class="cms-admin-head-actions">
            @if ($pageId)
                <a href="{{ route('admin.cms-pages.preview', ['page' => $pageId, 'locale' => 'ar']) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة</a>
                @if ($publicUrl = $cms->publicUrl($previewPage, 'ar'))
                    <a href="{{ $publicUrl }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">عرض عام</a>
                @endif
            @endif
            <a href="{{ route('admin.cms-pages') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
        </div>
    </div>

    <div class="cms-admin-tabs" role="tablist">
        @foreach ([
            'general' => 'عام',
            'seo' => 'SEO',
            ...($contentMode === 'blocks' ? ['blocks' => 'البلوكات'] : []),
            'ar' => 'عربي',
            'en' => 'English',
            'settings' => 'إعدادات',
            'preview' => 'معاينة',
        ] as $key => $label)
            <button type="button" @class(['cms-admin-tab', 'is-active' => $activeTab === $key]) wire:click="setTab('{{ $key }}')" role="tab">{{ $label }}</button>
        @endforeach
    </div>

    <form wire:submit="saveDraft">
        @if ($activeTab === 'general')
            <div class="cms-admin-tab-panel">
                <div class="admin-filter-grid cms-admin-grid-3">
                    <div class="admin-field">
                        <label>نوع الصفحة</label>
                        <select class="admin-control" wire:model.live="type">
                            @foreach (CmsOptions::pageTypes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label>تخطيط العرض</label>
                        <select class="admin-control" wire:model="layout">
                            @foreach (CmsOptions::pageLayouts() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="admin-field">
                        <label>ترتيب الفوتر</label>
                        <input type="number" min="0" class="admin-control" wire:model="sortOrder">
                    </div>
                </div>

                <fieldset class="cms-content-mode">
                    <legend>وضع محتوى الصفحة</legend>
                    <div class="cms-content-mode__options">
                        @foreach (CmsOptions::contentModes() as $value => $label)
                            <label @class(['cms-content-mode__option', 'is-active' => $contentMode === $value])>
                                <input type="radio" wire:model.live="contentMode" value="{{ $value }}">
                                <span class="cms-content-mode__label">{{ $label }}</span>
                                <span class="cms-content-mode__hint">
                                    @if ($value === 'blocks')
                                        تحكم كامل بأقسام الصفحة (إضافة / إخفاء / ترتيب). مناسب للرئيسية والصفحات المركّبة.
                                    @else
                                        محرر HTML حر للمحتوى الكامل. بدون بلوكات.
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="admin-filter-grid cms-admin-grid-2">
                    <div class="admin-field">
                        <label class="admin-checkbox">
                            <input type="checkbox" wire:model="showInFooter">
                            <span>إظهار في فوتر السياسات</span>
                        </label>
                    </div>
                    <div class="admin-field">
                        <label class="admin-checkbox">
                            <input type="checkbox" wire:model="showTitle">
                            <span>إظهار عنوان الصفحة للزوار</span>
                        </label>
                    </div>
                </div>
                <div class="cms-admin-hint">
                    <strong>ملاحظة:</strong> أنواع home / about / contact ترتبط بمسارات ثابتة في الموقع. الصفحات المخصصة تُعرض على <code dir="ltr">/ar/page/{slug}</code>. يمكن اختيار البلوكات أو HTML لأي نوع.
                </div>
            </div>
        @endif

        @if ($activeTab === 'seo')
            <div class="cms-admin-tab-panel">
                <div class="cms-admin-locale-block">
                    <h3 class="admin-form-section__title">SEO — العربية</h3>
                    <div class="admin-field">
                        <label>Meta title</label>
                        <input type="text" class="admin-control" wire:model="metaTitleAr" placeholder="يُستخدم العنوان إن تُرك فارغاً">
                    </div>
                    <div class="admin-field">
                        <label>Meta description</label>
                        <textarea class="admin-control" rows="2" wire:model="metaDescriptionAr" maxlength="320"></textarea>
                    </div>
                    <div class="admin-field">
                        <label>صورة Open Graph (URL)</label>
                        <input type="url" class="admin-control" wire:model="ogImageAr" dir="ltr" placeholder="https://...">
                    </div>
                </div>
                <div class="cms-admin-locale-block">
                    <h3 class="admin-form-section__title">SEO — English</h3>
                    <div class="admin-field">
                        <label>Meta title</label>
                        <input type="text" class="admin-control" wire:model="metaTitleEn">
                    </div>
                    <div class="admin-field">
                        <label>Meta description</label>
                        <textarea class="admin-control" rows="2" wire:model="metaDescriptionEn" maxlength="320"></textarea>
                    </div>
                    <div class="admin-field">
                        <label>OG image URL</label>
                        <input type="url" class="admin-control" wire:model="ogImageEn" dir="ltr">
                    </div>
                </div>
                <div class="admin-field">
                    <label class="admin-checkbox">
                        <input type="checkbox" wire:model="noindex">
                        <span>منع الفهرسة (noindex) — للصفحات الداخلية أو المسودات التجريبية</span>
                    </label>
                </div>
            </div>
        @endif

        @if ($activeTab === 'blocks' && $contentMode === 'blocks')
            <div class="cms-admin-tab-panel">
                @include('partials.admin.cms-blocks-editor')
            </div>
        @endif

        @if ($activeTab === 'ar')
            <div class="cms-admin-tab-panel">
                <div class="admin-filter-grid cms-admin-grid-2">
                    <div class="admin-field">
                        <label>العنوان *</label>
                        <input type="text" class="admin-control" wire:model="titleAr">
                        @error('titleAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>الرابط (slug)</label>
                        <input type="text" class="admin-control" wire:model="slugAr" dir="ltr" placeholder="يُولَّد تلقائياً من العنوان">
                    </div>
                </div>
                <div class="admin-field">
                    <label>مقتطف / Lead</label>
                    <textarea class="admin-control" rows="2" wire:model="excerptAr" maxlength="500" placeholder="يظهر في القوائم ونتائج البحث"></textarea>
                </div>
                @if ($contentMode === 'html')
                    <div class="admin-field">
                        <label>المحتوى (HTML)</label>
                        @include('partials.admin.wysiwyg', ['model' => 'bodyAr', 'value' => $bodyAr, 'direction' => 'rtl', 'language' => 'ar', 'height' => 420])
                    </div>
                @else
                    <div class="cms-admin-hint">
                        هذه الصفحة تعمل بوضع <strong>البلوكات</strong>. عدّل المحتوى من تبويب البلوكات. لتحرير HTML حر غيّر وضع المحتوى من تبويب «عام».
                    </div>
                @endif
            </div>
        @endif

        @if ($activeTab === 'en')
            <div class="cms-admin-tab-panel">
                <div class="admin-filter-grid cms-admin-grid-2">
                    <div class="admin-field">
                        <label>Title</label>
                        <input type="text" class="admin-control" wire:model="titleEn">
                    </div>
                    <div class="admin-field">
                        <label>Slug</label>
                        <input type="text" class="admin-control" wire:model="slugEn" dir="ltr">
                    </div>
                </div>
                <div class="admin-field">
                    <label>Excerpt</label>
                    <textarea class="admin-control" rows="2" wire:model="excerptEn" maxlength="500"></textarea>
                </div>
                @if ($contentMode === 'html')
                    <div class="admin-field">
                        <label>Body</label>
                        @include('partials.admin.wysiwyg', ['model' => 'bodyEn', 'value' => $bodyEn, 'direction' => 'ltr', 'language' => 'en', 'height' => 420])
                    </div>
                @else
                    <div class="cms-admin-hint">
                        Content is managed via the <strong>Blocks</strong> tab while this page uses blocks mode.
                    </div>
                @endif
            </div>
        @endif

        @if ($activeTab === 'settings')
            <div class="cms-admin-tab-panel">
                <div class="admin-field">
                    <label>رابط قديم (legacy redirect)</label>
                    <input type="text" class="admin-control" wire:model="legacySlug" dir="ltr" placeholder="privacy-policy">
                    <div class="admin-field-hint">للتوجيه من روابط قديمة إن وُجدت — اختياري فقط.</div>
                </div>
                <div class="admin-field">
                    <label>ملاحظات داخلية (لا تظهر للزوار)</label>
                    <textarea class="admin-control" rows="4" wire:model="internalNotes" placeholder="ملاحظات الفريق، مواعيد المراجعة، إلخ."></textarea>
                </div>
                <div class="admin-field">
                    <label>الحالة الحالية</label>
                    <select class="admin-control" wire:model="status">
                        @foreach (CmsOptions::pageStatuses() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        @if ($activeTab === 'preview')
            <div class="cms-admin-tab-panel">
                @if ($pageId)
                    <div class="cms-admin-preview-links">
                        <a href="{{ route('admin.cms-pages.preview', ['page' => $pageId, 'locale' => 'ar']) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة عربي</a>
                        <a href="{{ route('admin.cms-pages.preview', ['page' => $pageId, 'locale' => 'en']) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">Preview EN</a>
                    </div>
                    <iframe
                        class="cms-admin-preview-frame"
                        src="{{ route('admin.cms-pages.preview', ['page' => $pageId, 'locale' => 'ar']) }}"
                        title="معاينة الصفحة"
                    ></iframe>
                @else
                    <p class="admin-crud-card__meta">احفظ الصفحة أولاً لتفعيل المعاينة المباشرة.</p>
                @endif
            </div>
        @endif

        <div class="cms-admin-form-actions">
            <button type="submit" class="admin-btn-secondary admin-btn-secondary--sm">حفظ مسودة</button>
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="publish">حفظ ونشر</button>
        </div>
    </form>
</section>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cms-admin.css') }}">
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
