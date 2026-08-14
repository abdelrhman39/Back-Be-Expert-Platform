<?php

use App\Models\CatalogCourse;
use App\Services\AdminCatalogCourseService;
use App\Support\CatalogCourseTabs;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', [
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['href' => '/admin/catalog-courses', 'label' => 'دورات الكتالوج'],
        ['label' => 'تحرير دورة'],
    ],
])]
#[Title('دورة كتالوج | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public ?int $courseId = null;

    public string $titleAr = '';

    public string $titleEn = '';

    public string $slug = '';

    public bool $slugManual = false;

    public string $image = '';

    /** @var TemporaryUploadedFile|null */
    public $imageUpload = null;

    public bool $showImageUrlField = false;

    public ?string $priceOnline = null;

    public ?string $priceOnsite = null;

    public string $deliveryType = 'online';

    public string $status = 'published';

    public bool $isFeatured = true;

    public bool $isSelfLearning = false;

    public ?string $durationHours = null;

    public ?string $durationDays = null;

    public string $durationLabel = '';

    public string $city = '';

    public ?int $academicProgramId = null;

    /** @var array<int, int> */
    public array $categoryIds = [];

    public string $metaDescriptionAr = '';

    /** @var list<array{id: string, type: string, title: string, content: string, enabled: bool}> */
    public array $contentBlocks = [];

    public string $activeTab = 'shell';

    public ?string $savedMessage = null;

    public string $toastKey = '';

    public string $newBlockType = 'brief';

    public ?string $expandedBlockId = null;

    public function mount(?CatalogCourse $course = null): void
    {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        if (! $course) {
            $this->categoryIds = [(int) request()->query('category', \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS)];
            $this->seedDefaultBlocks();

            return;
        }

        $course->loadMissing(['details', 'categories']);
        $this->courseId = $course->id;
        $this->titleAr = $course->title_ar;
        $this->titleEn = $course->title_en ?? '';
        $this->slug = $course->showSlug();
        $this->slugManual = filled($this->slug);
        $this->image = $course->image ?? '';
        $this->priceOnline = $course->price_online !== null ? (string) $course->price_online : null;
        $this->priceOnsite = $course->price_onsite !== null ? (string) $course->price_onsite : null;
        $this->deliveryType = $course->delivery_type ?: 'online';
        $this->status = $course->status ?: 'published';
        $this->isFeatured = (bool) $course->is_featured;
        $this->isSelfLearning = (bool) $course->is_self_learning;
        $this->durationHours = $course->duration_hours !== null ? (string) $course->duration_hours : null;
        $this->durationDays = $course->duration_days !== null ? (string) $course->duration_days : null;
        $this->durationLabel = $course->duration_label ?? '';
        $this->city = $course->city ?? '';
        $this->academicProgramId = $course->academic_program_id;
        $this->categoryIds = $course->categories->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->metaDescriptionAr = $course->details?->meta_description_ar ?? '';

        $this->contentBlocks = $course->details
            ? $course->details->blocksForEditor()
            : $this->defaultBlocks();

        $this->expandedBlockId = collect($this->contentBlocks)
            ->first(fn (array $block) => ($block['enabled'] ?? false) && filled(strip_tags((string) ($block['content'] ?? ''))))['id']
            ?? ($this->contentBlocks[0]['id'] ?? null);
    }

    public function updatedTitleAr(string $value): void
    {
        if ($this->slugManual) {
            return;
        }

        $this->slug = app(AdminCatalogCourseService::class)->suggestSlug($value, $this->titleEn ?: null);
    }

    public function updatedTitleEn(string $value): void
    {
        if ($this->slugManual || ! filled($value)) {
            return;
        }

        $this->slug = app(AdminCatalogCourseService::class)->suggestSlug($this->titleAr, $value);
    }

    public function updatedSlug(): void
    {
        $this->slugManual = filled($this->slug);
    }

    public function regenerateSlug(): void
    {
        $this->slugManual = false;
        $this->slug = app(AdminCatalogCourseService::class)->suggestSlug($this->titleAr, $this->titleEn ?: null);
    }

    public function updatedImageUpload(): void
    {
        $this->validate([
            'imageUpload' => ['nullable', 'image', 'max:8192'],
        ], [], [
            'imageUpload' => 'صورة الغلاف',
        ]);
    }

    public function removeCoverImage(): void
    {
        $this->image = '';
        $this->imageUpload = null;
        $this->resetValidation('imageUpload');
    }

    public function toggleImageUrlField(): void
    {
        $this->showImageUrlField = ! $this->showImageUrlField;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['shell', 'details'], true) ? $tab : 'shell';
    }

    public function addBlock(?string $type = null, ?int $atIndex = null): void
    {
        $type = $type ?: $this->newBlockType;
        $allowed = array_merge(array_keys(CatalogCourseTabs::definitions()), ['custom']);

        if (! in_array($type, $allowed, true)) {
            $type = 'custom';
        }

        $id = (string) Str::uuid();
        $block = [
            'id' => $id,
            'type' => $type,
            'title' => $type === 'custom' ? 'قسم جديد' : CatalogCourseTabs::label($type),
            'content' => '',
            'enabled' => true,
        ];

        if ($atIndex === null || $atIndex >= count($this->contentBlocks)) {
            $this->contentBlocks[] = $block;
        } else {
            $blocks = $this->contentBlocks;
            array_splice($blocks, max(0, $atIndex), 0, [$block]);
            $this->contentBlocks = array_values($blocks);
        }

        $this->expandedBlockId = $id;
        $this->activeTab = 'details';
        $this->newBlockType = $type;
    }

    public function removeBlock(string $id): void
    {
        $this->contentBlocks = array_values(array_filter(
            $this->contentBlocks,
            fn (array $block) => ($block['id'] ?? '') !== $id
        ));

        if ($this->expandedBlockId === $id) {
            $this->expandedBlockId = $this->contentBlocks[0]['id'] ?? null;
        }
    }

    public function toggleBlock(string $id): void
    {
        foreach ($this->contentBlocks as $index => $block) {
            if (($block['id'] ?? '') === $id) {
                $this->contentBlocks[$index]['enabled'] = ! ($block['enabled'] ?? true);
                break;
            }
        }
    }

    public function expandBlock(string $id): void
    {
        $this->expandedBlockId = $this->expandedBlockId === $id ? null : $id;
    }

    #[Renderless]
    public function updateBlockContent(string $id, string $content): void
    {
        foreach ($this->contentBlocks as $index => $block) {
            if (($block['id'] ?? '') === $id) {
                $this->contentBlocks[$index]['content'] = $content;
                break;
            }
        }
    }

    public function moveBlock(string $id, string $direction): void
    {
        $index = collect($this->contentBlocks)->search(fn (array $block) => ($block['id'] ?? '') === $id);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= count($this->contentBlocks)) {
            return;
        }

        $blocks = $this->contentBlocks;
        [$blocks[$index], $blocks[$target]] = [$blocks[$target], $blocks[$index]];
        $this->contentBlocks = array_values($blocks);
    }

    public function save(AdminCatalogCourseService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        $this->validate([
            'titleAr' => ['required', 'string', 'max:255'],
            'titleEn' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:500'],
            'imageUpload' => ['nullable', 'image', 'max:8192'],
            'priceOnline' => ['nullable', 'numeric', 'min:0'],
            'priceOnsite' => ['nullable', 'numeric', 'min:0'],
            'deliveryType' => ['required', 'in:online,onsite,both'],
            'status' => ['required', 'in:published,draft,archived'],
            'durationHours' => ['nullable', 'integer', 'min:1'],
            'durationDays' => ['nullable', 'integer', 'min:1'],
            'durationLabel' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'academicProgramId' => ['nullable', 'exists:academic_programs,id'],
            'categoryIds' => ['required', 'array', 'min:1'],
            'categoryIds.*' => ['integer', 'exists:catalog_categories,id'],
            'metaDescriptionAr' => ['nullable', 'string', 'max:500'],
            'contentBlocks' => ['array'],
            'contentBlocks.*.type' => ['required', 'string'],
            'contentBlocks.*.title' => ['nullable', 'string', 'max:120'],
            'contentBlocks.*.content' => ['nullable', 'string'],
            'contentBlocks.*.enabled' => ['boolean'],
        ], [], [
            'titleAr' => 'العنوان بالعربية',
            'categoryIds' => 'التصنيفات',
            'slug' => 'الرابط',
            'imageUpload' => 'صورة الغلاف',
        ]);

        if (! filled($this->slug)) {
            $this->slug = $service->suggestSlug($this->titleAr, $this->titleEn ?: null);
        }

        if ($this->imageUpload instanceof TemporaryUploadedFile) {
            $this->image = $service->storeCoverImage($this->imageUpload);
            $this->imageUpload = null;
        }

        $payload = [
            'title_ar' => $this->titleAr,
            'title_en' => $this->titleEn ?: null,
            'slug' => $this->slug ?: null,
            'image' => $this->image ?: null,
            'price_online' => $this->priceOnline !== null && $this->priceOnline !== '' ? (float) $this->priceOnline : null,
            'price_onsite' => $this->priceOnsite !== null && $this->priceOnsite !== '' ? (float) $this->priceOnsite : null,
            'delivery_type' => $this->deliveryType,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'is_self_learning' => $this->isSelfLearning,
            'duration_hours' => $this->durationHours !== null && $this->durationHours !== '' ? (int) $this->durationHours : null,
            'duration_days' => $this->durationDays !== null && $this->durationDays !== '' ? (int) $this->durationDays : null,
            'duration_label' => $this->durationLabel ?: null,
            'city' => $this->city ?: null,
            'academic_program_id' => $this->academicProgramId,
            'category_ids' => $this->categoryIds,
            'details' => [
                'meta_description_ar' => $this->metaDescriptionAr,
                'content_blocks' => $this->contentBlocks,
            ],
        ];

        if ($this->courseId) {
            $course = CatalogCourse::query()->findOrFail($this->courseId);
            $course = $service->update($course, $payload);
            $this->slug = $course->showSlug();
            $this->contentBlocks = $course->details?->blocksForEditor() ?: $this->contentBlocks;
            $this->savedMessage = 'تم حفظ الدورة/الدبلوم بنجاح. يمكنك معاينة الصفحة العامة في أي وقت.';
            $this->toastKey = 'catalog-saved-'.uniqid();
            $this->js('setTimeout(() => $wire.set("savedMessage", null), 7000)');

            return;
        }

        $course = $service->create($payload);
        $this->redirect(route('admin.catalog-courses.edit', ['course' => $course->id]), navigate: true);
    }

    /** @return list<array{id: string, type: string, title: string, content: string, enabled: bool}> */
    protected function defaultBlocks(): array
    {
        $blocks = [];

        foreach (array_keys(CatalogCourseTabs::definitions()) as $key) {
            $blocks[] = [
                'id' => (string) Str::uuid(),
                'type' => $key,
                'title' => CatalogCourseTabs::label($key),
                'content' => '',
                'enabled' => in_array($key, ['brief', 'goals', 'audience'], true),
            ];
        }

        return $blocks;
    }

    protected function seedDefaultBlocks(): void
    {
        $this->contentBlocks = $this->defaultBlocks();
        $this->expandedBlockId = $this->contentBlocks[0]['id'] ?? null;
    }
};
?>

@php
    $service = app(AdminCatalogCourseService::class);
    $categories = $service->categoryOptions();
    $programs = $service->programOptions();
    $blockTypes = CatalogCourseTabs::definitions();
    $isDiploma = in_array(\App\Services\CatalogCourseService::CATEGORY_DIPLOMAS, $categoryIds, true);
    $enabledBlocks = collect($contentBlocks)->where('enabled', true)->count();
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.catalog-courses'),
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-catalog-course-form.css') }}?v=6">
@endpush

@assets
<script src="{{ asset('js/admin-rich-editor.js') }}?v=1"></script>
@endassets

@if ($savedMessage)
    @include('partials.admin.toast', [
        'message' => $savedMessage,
        'type' => 'success',
        'title' => 'تم الحفظ بنجاح',
        'key' => $toastKey ?: 'catalog-saved',
    ])
@endif

<section class="ccf">
    <header class="ccf-hero">
        <div class="ccf-hero__text">
            <span class="ccf-hero__eyebrow">كتالوج البرامج</span>
            <h1>{{ $courseId ? 'تحرير دورة / دبلوم' : 'إضافة دورة / دبلوم' }}</h1>
            <p>
                إدارة بيانات العرض في الموقع وصفحة التفاصيل.
                @if ($isDiploma)
                    <strong>التصنيف الحالي: الدبلومات</strong>
                @endif
            </p>
        </div>
        <div class="ccf-hero__actions">
            @if ($courseId)
                <a href="{{ route('courses.show', ['locale' => app()->getLocale(), 'course' => $slug ?: $courseId]) }}" class="admin-btn-secondary" target="_blank" rel="noopener">
                    <i class="fa-solid fa-eye"></i> معاينة
                </a>
                <a href="{{ route('admin.catalog-courses.content', ['course' => $courseId]) }}" class="admin-btn-secondary">
                    <i class="fa-solid fa-layer-group"></i> الوحدات
                </a>
            @endif
            <a href="{{ route('admin.catalog-courses') }}" class="admin-btn-secondary">العودة</a>
        </div>
    </header>

    <nav class="ccf-tabs" aria-label="أقسام النموذج">
        <button type="button" class="ccf-tabs__btn {{ $activeTab === 'shell' ? 'is-active' : '' }}" wire:click="setTab('shell')">
            <i class="fa-solid fa-id-card"></i>
            البيانات الأساسية
        </button>
        <button type="button" class="ccf-tabs__btn {{ $activeTab === 'details' ? 'is-active' : '' }}" wire:click="setTab('details')">
            <i class="fa-solid fa-cubes"></i>
            محتوى صفحة التفاصيل
            <em>{{ $enabledBlocks }} قسم نشط</em>
        </button>
    </nav>

    <form wire:submit="save" class="ccf-form">
        @if ($activeTab === 'shell')
            <div class="ccf-grid">
                <section class="ccf-card ccf-card--identity">
                    <div class="ccf-card__head">
                        <h2>الهوية والعنوان</h2>
                        <p>ما يظهر للطالب في البطاقة وصفحة التفاصيل.</p>
                    </div>
                    <div class="ccf-fields ccf-fields--2">
                        <div class="admin-field">
                            <label class="admin-label">العنوان بالعربية *</label>
                            <input type="text" class="admin-control" wire:model.live.debounce.400ms="titleAr" dir="rtl" lang="ar" placeholder="مثال: دبلوم الذكاء الاصطناعي التطبيقي">
                            @error('titleAr') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="admin-field">
                            <label class="admin-label">العنوان بالإنجليزية</label>
                            <input type="text" class="admin-control ccf-control--ltr" wire:model.live.debounce.400ms="titleEn" dir="ltr" lang="en" placeholder="Applied AI Diploma">
                        </div>
                        <div class="admin-field ccf-field--full">
                            <div class="ccf-slug-head">
                                <label class="admin-label">
                                    رابط الصفحة (slug)
                                    <span class="ccf-hint-chip">يُحدَّث تلقائياً من العنوان</span>
                                </label>
                                <button type="button" class="ccf-slug-regen" wire:click="regenerateSlug" title="إعادة توليد من العنوان">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    إعادة توليد
                                </button>
                            </div>
                            <div class="ccf-slug" dir="ltr">
                                <span class="ccf-slug__icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                                <span class="ccf-slug__prefix">/ar/courses/</span>
                                <input
                                    type="text"
                                    class="ccf-slug__input"
                                    wire:model.live.debounce.400ms="slug"
                                    dir="ltr"
                                    spellcheck="false"
                                    autocomplete="off"
                                    placeholder="applied-ai-diploma"
                                >
                            </div>
                            <small class="ccf-help">يُكتب بالإنجليزية بأحرف صغيرة وشرطات، ويظهر في عنوان الصفحة العامة.</small>
                            @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="admin-field ccf-field--full">
                            <div class="ccf-cover-head">
                                <label class="admin-label">صورة الغلاف</label>
                                <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                                    <button type="button" class="ccf-cover-link-toggle" onclick="Livewire.dispatch('open-media-picker', { target: 'image', accept: 'image', title: 'اختيار صورة الغلاف' })">
                                        <i class="fa-regular fa-images"></i>
                                        من المكتبة
                                    </button>
                                    <button type="button" class="ccf-cover-link-toggle" wire:click="toggleImageUrlField">
                                        <i class="fa-solid fa-link"></i>
                                        {{ $showImageUrlField ? 'إخفاء الرابط اليدوي' : 'استخدام رابط / مسار' }}
                                    </button>
                                </div>
                            </div>

                            <div
                                x-data
                                x-on:media-picker-selected.window="
                                    if ($event.detail.target === 'image') {
                                        $wire.set('image', $event.detail.url);
                                        $wire.set('imageUpload', null);
                                    }
                                "
                            >

                            @php
                                $coverPreview = $imageUpload
                                    ? $imageUpload->temporaryUrl()
                                    : (filled($image) ? resolve_poster_url($image) : null);
                            @endphp

                            <div class="ccf-cover-panel {{ $coverPreview ? 'has-preview' : '' }}">
                                @if ($coverPreview)
                                    <div class="ccf-cover-preview">
                                        <img src="{{ $coverPreview }}" alt="معاينة صورة الغلاف">
                                        <div class="ccf-cover-preview__actions">
                                            <label class="ccf-cover-preview__btn">
                                                <input type="file" wire:model="imageUpload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                                استبدال
                                            </label>
                                            <button type="button" class="ccf-cover-preview__btn is-danger" wire:click="removeCoverImage">
                                                <i class="fa-solid fa-trash"></i>
                                                إزالة
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <label class="ccf-cover-drop">
                                        <input type="file" wire:model="imageUpload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                                        <span class="ccf-cover-drop__icon"><i class="fa-regular fa-image"></i></span>
                                        <strong>اسحب الصورة هنا أو انقر للرفع</strong>
                                        <small>JPG, PNG, WebP — حتى 8 ميجابايت · نسبة مفضّلة عريضة (16:9)</small>
                                    </label>
                                @endif

                                <div class="ccf-cover-status" wire:loading wire:target="imageUpload">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    جاري رفع الصورة…
                                </div>
                            </div>

                            @if ($showImageUrlField)
                                <div class="ccf-cover-url" dir="ltr">
                                    <span class="ccf-cover-url__icon" aria-hidden="true"><i class="fa-solid fa-link"></i></span>
                                    <input
                                        type="text"
                                        class="ccf-cover-url__input"
                                        wire:model.live.debounce.300ms="image"
                                        dir="ltr"
                                        placeholder="/storage/... or https://..."
                                    >
                                </div>
                                <small class="ccf-help">اختياري: الصق مساراً نسبياً أو رابطاً خارجياً إن لم ترفع ملفاً.</small>
                            @endif

                            @error('imageUpload') <small class="text-danger">{{ $message }}</small> @enderror
                            @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ccf-card ccf-card--commerce">
                    <div class="ccf-card__head ccf-card__head--row">
                        <div>
                            <h2>التسعير والتقديم</h2>
                            <p>اختر نمط التدريب، حالة النشر، والأسعار الظاهرة للطالب.</p>
                        </div>
                        <span class="ccf-status-pill ccf-status-pill--{{ $status }}">
                            @if ($status === 'published') منشور
                            @elseif ($status === 'draft') مسودة
                            @else مؤرشف
                            @endif
                        </span>
                    </div>

                    <div class="ccf-commerce">
                        <div class="ccf-commerce__section">
                            <div class="ccf-commerce__label">
                                <strong>نوع التقديم</strong>
                                <span>يحدد خيارات التسجيل المتاحة للطالب</span>
                            </div>
                            <div class="ccf-choice" role="radiogroup" aria-label="نوع التقديم">
                                <label class="ccf-choice__item {{ $deliveryType === 'online' ? 'is-selected' : '' }}">
                                    <input type="radio" wire:model.live="deliveryType" value="online">
                                    <span class="ccf-choice__icon"><i class="fa-solid fa-laptop"></i></span>
                                    <span class="ccf-choice__text">
                                        <strong>عن بعد</strong>
                                        <small>تدريب إلكتروني فقط</small>
                                    </span>
                                </label>
                                <label class="ccf-choice__item {{ $deliveryType === 'onsite' ? 'is-selected' : '' }}">
                                    <input type="radio" wire:model.live="deliveryType" value="onsite">
                                    <span class="ccf-choice__icon"><i class="fa-solid fa-building"></i></span>
                                    <span class="ccf-choice__text">
                                        <strong>حضوري</strong>
                                        <small>في المقر فقط</small>
                                    </span>
                                </label>
                                <label class="ccf-choice__item {{ $deliveryType === 'both' ? 'is-selected' : '' }}">
                                    <input type="radio" wire:model.live="deliveryType" value="both">
                                    <span class="ccf-choice__icon"><i class="fa-solid fa-layer-group"></i></span>
                                    <span class="ccf-choice__text">
                                        <strong>الاثنان معاً</strong>
                                        <small>الطالب يختار</small>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="ccf-commerce__section">
                            <div class="ccf-commerce__label">
                                <strong>حالة النشر</strong>
                                <span>تحكم في ظهور البرنامج في الموقع</span>
                            </div>
                            <div class="ccf-choice ccf-choice--status" role="radiogroup" aria-label="حالة النشر">
                                <label class="ccf-choice__item {{ $status === 'published' ? 'is-selected is-published' : '' }}">
                                    <input type="radio" wire:model.live="status" value="published">
                                    <span class="ccf-choice__dot"></span>
                                    <span class="ccf-choice__text"><strong>منشور</strong></span>
                                </label>
                                <label class="ccf-choice__item {{ $status === 'draft' ? 'is-selected is-draft' : '' }}">
                                    <input type="radio" wire:model.live="status" value="draft">
                                    <span class="ccf-choice__dot"></span>
                                    <span class="ccf-choice__text"><strong>مسودة</strong></span>
                                </label>
                                <label class="ccf-choice__item {{ $status === 'archived' ? 'is-selected is-archived' : '' }}">
                                    <input type="radio" wire:model.live="status" value="archived">
                                    <span class="ccf-choice__dot"></span>
                                    <span class="ccf-choice__text"><strong>مؤرشف</strong></span>
                                </label>
                            </div>
                        </div>

                        <div class="ccf-commerce__section">
                            <div class="ccf-commerce__label">
                                <strong>الأسعار</strong>
                                <span>بالريال السعودي · الحقول غير المتاحة تُعطَّل تلقائياً</span>
                            </div>
                            <div class="ccf-price-grid">
                                @php $onlineEnabled = in_array($deliveryType, ['online', 'both'], true); @endphp
                                <div class="ccf-price {{ $onlineEnabled ? 'is-active' : 'is-off' }}">
                                    <div class="ccf-price__meta">
                                        <span class="ccf-price__icon"><i class="fa-solid fa-wifi"></i></span>
                                        <div>
                                            <strong>سعر عن بعد</strong>
                                            <small>{{ $onlineEnabled ? 'ظاهر للطالب' : 'غير متاح لهذا النمط' }}</small>
                                        </div>
                                    </div>
                                    <div class="ccf-price__input" dir="ltr">
                                        <input type="number" step="0.01" min="0" wire:model="priceOnline" @disabled(! $onlineEnabled) placeholder="0.00">
                                        <em>ر.س</em>
                                    </div>
                                </div>

                                @php $onsiteEnabled = in_array($deliveryType, ['onsite', 'both'], true); @endphp
                                <div class="ccf-price {{ $onsiteEnabled ? 'is-active' : 'is-off' }}">
                                    <div class="ccf-price__meta">
                                        <span class="ccf-price__icon"><i class="fa-solid fa-location-dot"></i></span>
                                        <div>
                                            <strong>سعر حضوري</strong>
                                            <small>{{ $onsiteEnabled ? 'ظاهر للطالب' : 'غير متاح لهذا النمط' }}</small>
                                        </div>
                                    </div>
                                    <div class="ccf-price__input" dir="ltr">
                                        <input type="number" step="0.01" min="0" wire:model="priceOnsite" @disabled(! $onsiteEnabled) placeholder="0.00">
                                        <em>ر.س</em>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ccf-card ccf-card--duration">
                    <div class="ccf-card__head">
                        <h2>المدة والموقع</h2>
                        <p>معلومات تظهر في بطاقة البرنامج وصفحة التسجيل.</p>
                    </div>
                    <div class="ccf-meta-grid">
                        <div class="ccf-meta">
                            <span class="ccf-meta__icon"><i class="fa-regular fa-calendar"></i></span>
                            <div class="ccf-meta__body">
                                <label class="admin-label">المدة الظاهرة</label>
                                <input type="text" class="admin-control" wire:model="durationLabel" placeholder="مثال: 6 أشهر">
                                <small class="ccf-help">نص حر يراه الطالب (أشهر / أسابيع…)</small>
                            </div>
                        </div>
                        <div class="ccf-meta">
                            <span class="ccf-meta__icon"><i class="fa-solid fa-city"></i></span>
                            <div class="ccf-meta__body">
                                <label class="admin-label">المدينة</label>
                                <input type="text" class="admin-control" wire:model="city" placeholder="الرياض">
                                <small class="ccf-help">مهم للتدريب الحضوري</small>
                            </div>
                        </div>
                        <div class="ccf-meta">
                            <span class="ccf-meta__icon"><i class="fa-solid fa-clock"></i></span>
                            <div class="ccf-meta__body">
                                <label class="admin-label">ساعات التدريب</label>
                                <input type="number" min="1" class="admin-control" wire:model="durationHours" placeholder="مثلاً: 40">
                            </div>
                        </div>
                        <div class="ccf-meta">
                            <span class="ccf-meta__icon"><i class="fa-solid fa-sun"></i></span>
                            <div class="ccf-meta__body">
                                <label class="admin-label">عدد الأيام</label>
                                <input type="number" min="1" class="admin-control" wire:model="durationDays" placeholder="مثلاً: 10">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ccf-card ccf-card--taxonomy">
                    <div class="ccf-card__head ccf-card__head--row">
                        <div>
                            <h2>التصنيف والظهور</h2>
                            <p>اختر التصنيفات التي ينتمي إليها البرنامج، ثم حدّد خيارات الظهور في الموقع.</p>
                        </div>
                        <span class="ccf-selected-count">
                            {{ count($categoryIds) }} تصنيفات محددة
                        </span>
                    </div>

                    <div class="ccf-taxonomy">
                        <div class="ccf-taxonomy__section">
                            <div class="ccf-taxonomy__label">
                                <strong>التصنيفات *</strong>
                                <span>يمكن اختيار أكثر من تصنيف</span>
                            </div>
                            <div class="ccf-cat-grid" role="group" aria-label="تصنيفات الدورة">
                                @foreach ($categories as $category)
                                    @php $checked = in_array((int) $category['id'], array_map('intval', $categoryIds), true); @endphp
                                    <label class="ccf-cat {{ $checked ? 'is-selected' : '' }}">
                                        <input type="checkbox" value="{{ $category['id'] }}" wire:model.live="categoryIds">
                                        <span class="ccf-cat__check" aria-hidden="true">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                        <span class="ccf-cat__text">{{ $category['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('categoryIds') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="ccf-taxonomy__section">
                            <div class="ccf-taxonomy__label">
                                <strong>خيارات الظهور</strong>
                                <span>تحكم سريع في إبراز البرنامج</span>
                            </div>
                            <div class="ccf-visibility">
                                <label class="ccf-visibility__card {{ $isFeatured ? 'is-on' : '' }}">
                                    <input type="checkbox" wire:model.live="isFeatured">
                                    <span class="ccf-visibility__icon"><i class="fa-solid fa-house"></i></span>
                                    <span class="ccf-visibility__body">
                                        <strong>الصفحة الرئيسية</strong>
                                        <small>يظهر ضمن الأقسام المميزة في الصفحة الرئيسية.</small>
                                    </span>
                                    <span class="ccf-visibility__switch" aria-hidden="true"></span>
                                </label>
                                <label class="ccf-visibility__card {{ $isSelfLearning ? 'is-on' : '' }}">
                                    <input type="checkbox" wire:model.live="isSelfLearning">
                                    <span class="ccf-visibility__icon"><i class="fa-solid fa-laptop"></i></span>
                                    <span class="ccf-visibility__body">
                                        <strong>تعلم ذاتي</strong>
                                        <small>مناسب للمحتوى غير المرتبط بجدول حضور ثابت.</small>
                                    </span>
                                    <span class="ccf-visibility__switch" aria-hidden="true"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="ccf-card ccf-card--program">
                    <div class="ccf-card__head">
                        <h2>
                            ربط ببرنامج أكاديمي
                            <span class="ccf-optional">اختياري</span>
                            <button type="button" class="ccf-help-tip" title="ما فائدة هذا الربط؟" aria-label="ما فائدة هذا الربط؟">
                                <i class="fa-solid fa-circle-question"></i>
                                <span class="ccf-help-tip__popup" role="tooltip">
                                    عند الربط: تظهر خيارات التقسيط المرتبطة بالبرنامج، ويمكن مواءمة خطط السداد الأكاديمية مع صفحة الشراء في الكتالوج.
                                    بدون ربط: الدورة تبقى منتجاً تسويقياً مستقلاً في المتجر فقط.
                                </span>
                            </button>
                        </h2>
                        <p>اربط عنصر الكتالوج ببرنامج دراسي من `/admin/programs` عند الحاجة.</p>
                    </div>

                    <div class="ccf-program-note" role="note">
                        <span class="ccf-program-note__icon"><i class="fa-solid fa-link"></i></span>
                        <div>
                            <strong>ماذا يحدث عند الاختيار؟</strong>
                            <ul>
                                <li>يظهر للطلاب أن التقسيط متاح إذا كانت دفعات البرنامج مفتوحة للتسجيل والتقسيط.</li>
                                <li>تُستخدم خطط التقسيط المرتبطة بهذا البرنامج عند الدفع من صفحة الدورة.</li>
                                <li>لا ينقل المحتوى الأكاديمي تلقائياً — الربط للعرض والدفع فقط.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="admin-field" style="margin-top:1rem;">
                        <label class="admin-label">البرنامج الأكاديمي</label>
                        <select class="admin-control" wire:model="academicProgramId">
                            <option value="">— بدون ربط (منتج كتالوج مستقل) —</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program['id'] }}">{{ $program['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </section>
            </div>
        @else
            <div class="ccf-details">
                <section class="ccf-card ccf-card--details">
                    <div class="ccf-card__head">
                        <h2>محرر أقسام صفحة التفاصيل</h2>
                        <p>ابنِ صفحة الدورة بأقسام حرة: أضف، رتّب، ونسّق النص داخل المحرر — بدون التعامل مع HTML.</p>
                    </div>

                    <div class="admin-field ccf-seo-field">
                        <label class="admin-label">وصف مختصر لمحركات البحث (SEO)</label>
                        <textarea class="admin-control" rows="2" wire:model="metaDescriptionAr" placeholder="جملة قصيرة تظهر في نتائج البحث"></textarea>
                    </div>

                    <div class="ccf-palette">
                        <div class="ccf-palette__head">
                            <strong>إضافة قسم</strong>
                            <span>اختر نوعاً جاهزاً أو قسماً مخصصاً — يُضاف فوراً في نهاية القائمة</span>
                        </div>
                        <div class="ccf-palette__grid">
                            @foreach ($blockTypes as $type => $meta)
                                <button type="button" class="ccf-palette__chip" wire:click="addBlock('{{ $type }}')">
                                    <i class="fa-solid fa-plus"></i>
                                    {{ $meta['label'] }}
                                </button>
                            @endforeach
                            <button type="button" class="ccf-palette__chip ccf-palette__chip--custom" wire:click="addBlock('custom')">
                                <i class="fa-solid fa-pen-ruler"></i>
                                قسم مخصص حر
                            </button>
                        </div>
                    </div>

                    @if ($contentBlocks === [])
                        <div class="ccf-empty">
                            <i class="fa-solid fa-cubes"></i>
                            <p>لا توجد أقسام بعد — ابدأ من الشبكة أعلاه أو أضف الوصف العام</p>
                            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="addBlock('brief')">أضف قسم الوصف العام</button>
                        </div>
                    @else
                        <div class="ccf-blocks">
                            <div class="ccf-insert">
                                <button type="button" class="ccf-insert__btn" wire:click="addBlock('custom', 0)">
                                    <i class="fa-solid fa-plus"></i> إدراج قسم في البداية
                                </button>
                            </div>

                            @foreach ($contentBlocks as $index => $block)
                                @php
                                    $blockId = $block['id'];
                                    $isOpen = $expandedBlockId === $blockId;
                                    $typeLabel = ($block['type'] ?? '') === 'custom'
                                        ? 'مخصص'
                                        : CatalogCourseTabs::label((string) $block['type']);
                                @endphp
                                <article class="ccf-block {{ ($block['enabled'] ?? true) ? 'is-enabled' : 'is-disabled' }} {{ $isOpen ? 'is-open' : '' }}" wire:key="block-{{ $blockId }}">
                                    <header class="ccf-block__head">
                                        <button type="button" class="ccf-block__identity" wire:click="expandBlock('{{ $blockId }}')">
                                            <span class="ccf-block__index">{{ $index + 1 }}</span>
                                            <div>
                                                <strong>{{ $block['title'] ?: $typeLabel }}</strong>
                                                <small>{{ $typeLabel }}{{ ($block['enabled'] ?? true) ? '' : ' · مخفي' }}</small>
                                            </div>
                                        </button>
                                        <div class="ccf-block__tools">
                                            <button type="button" class="ccf-icon-btn" wire:click="moveBlock('{{ $blockId }}', 'up')" @disabled($index === 0) title="أعلى">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </button>
                                            <button type="button" class="ccf-icon-btn" wire:click="moveBlock('{{ $blockId }}', 'down')" @disabled($index === count($contentBlocks) - 1) title="أسفل">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </button>
                                            <button type="button" class="ccf-icon-btn {{ ($block['enabled'] ?? true) ? 'is-on' : '' }}" wire:click="toggleBlock('{{ $blockId }}')" title="إظهار / إخفاء">
                                                <i class="fa-solid {{ ($block['enabled'] ?? true) ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                            </button>
                                            <button type="button" class="ccf-icon-btn" wire:click="expandBlock('{{ $blockId }}')" title="{{ $isOpen ? 'طي' : 'تحرير' }}">
                                                <i class="fa-solid {{ $isOpen ? 'fa-chevron-up' : 'fa-pen' }}"></i>
                                            </button>
                                            <button type="button" class="ccf-icon-btn is-danger" wire:click="removeBlock('{{ $blockId }}')" wire:confirm="حذف هذا القسم؟" title="حذف">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </header>

                                    @if ($isOpen)
                                        <div class="ccf-block__body">
                                            <div class="ccf-fields ccf-fields--2">
                                                <div class="admin-field">
                                                    <label class="admin-label">عنوان القسم الظاهر للطالب</label>
                                                    <input type="text" class="admin-control" wire:model.live="contentBlocks.{{ $index }}.title">
                                                </div>
                                                <div class="admin-field">
                                                    <label class="admin-label">نوع القسم</label>
                                                    <select class="admin-control" wire:model.live="contentBlocks.{{ $index }}.type">
                                                        @foreach ($blockTypes as $type => $meta)
                                                            <option value="{{ $type }}">{{ $meta['label'] }}</option>
                                                        @endforeach
                                                        <option value="custom">قسم مخصص</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="admin-field ccf-rte-field">
                                                <label class="admin-label">المحتوى</label>
                                                <div
                                                    class="ccf-rte"
                                                    wire:ignore
                                                    wire:key="rte-{{ $blockId }}"
                                                    x-data="ccfRichEditor({
                                                        initial: @js($block['content'] ?? ''),
                                                        blockId: @js($blockId)
                                                    })"
                                                >
                                                    <div class="ccf-rte__toolbar" role="toolbar" aria-label="تنسيق النص">
                                                        <button type="button" class="ccf-rte__btn" @click="formatBlock('p')" title="فقرة"><i class="fa-solid fa-paragraph"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="formatBlock('h3')" title="عنوان"><i class="fa-solid fa-heading"></i></button>
                                                        <span class="ccf-rte__sep"></span>
                                                        <button type="button" class="ccf-rte__btn" @click="command('bold')" title="عريض"><i class="fa-solid fa-bold"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('italic')" title="مائل"><i class="fa-solid fa-italic"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('underline')" title="تسطير"><i class="fa-solid fa-underline"></i></button>
                                                        <span class="ccf-rte__sep"></span>
                                                        <button type="button" class="ccf-rte__btn" @click="command('insertUnorderedList')" title="قائمة نقاط"><i class="fa-solid fa-list-ul"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('insertOrderedList')" title="قائمة مرقّمة"><i class="fa-solid fa-list-ol"></i></button>
                                                        <span class="ccf-rte__sep"></span>
                                                        <button type="button" class="ccf-rte__btn" @click="command('justifyRight')" title="محاذاة يمين"><i class="fa-solid fa-align-right"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('justifyCenter')" title="توسيط"><i class="fa-solid fa-align-center"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('justifyLeft')" title="محاذاة يسار"><i class="fa-solid fa-align-left"></i></button>
                                                        <span class="ccf-rte__sep"></span>
                                                        <button type="button" class="ccf-rte__btn" @click="insertLink()" title="رابط"><i class="fa-solid fa-link"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('unlink')" title="إزالة الرابط"><i class="fa-solid fa-link-slash"></i></button>
                                                        <button type="button" class="ccf-rte__btn" @click="command('removeFormat')" title="مسح التنسيق"><i class="fa-solid fa-eraser"></i></button>
                                                    </div>
                                                    <div
                                                        class="ccf-rte__surface"
                                                        x-ref="editor"
                                                        contenteditable="true"
                                                        dir="rtl"
                                                        role="textbox"
                                                        aria-multiline="true"
                                                        data-placeholder="اكتب محتوى القسم هنا..."
                                                        @input="queueSync()"
                                                        @blur="sync()"
                                                        @paste="onPaste($event)"
                                                    ></div>
                                                </div>
                                                <small class="ccf-help">نسّق النص من شريط الأدوات. الحجم موحّد تلقائياً، واللصق يُدخل نصاً نظيفاً.</small>
                                            </div>
                                        </div>
                                    @endif
                                </article>

                                <div class="ccf-insert">
                                    <button type="button" class="ccf-insert__btn" wire:click="addBlock('custom', {{ $index + 1 }})">
                                        <i class="fa-solid fa-plus"></i> إدراج قسم هنا
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @endif

        <footer class="ccf-actions">
            <button type="submit" class="ccf-actions__save" wire:loading.attr="disabled">
                <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="save"></i>
                <span wire:loading.remove wire:target="save">حفظ التغييرات</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
            <a href="{{ route('admin.catalog-courses') }}" class="admin-btn-secondary">إلغاء</a>
        </footer>
    </form>
</section>

@include('partials.admin.shell-end')
