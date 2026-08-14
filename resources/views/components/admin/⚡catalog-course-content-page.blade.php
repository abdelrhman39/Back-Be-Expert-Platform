<?php

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use App\Services\AdminCourseContentService;
use App\Support\CourseContentOptions;
use App\Support\CourseModuleOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('محتوى الدورة | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public CatalogCourse $course;

    public string $panel = 'none';

    public ?int $editingModuleId = null;

    public ?int $editingLessonId = null;

    public ?int $lessonModuleId = null;

    public string $moduleTitleAr = '';

    public string $moduleTitleEn = '';

    public string $moduleCode = '';

    public string $moduleSummaryAr = '';

    public string $moduleSummaryEn = '';

    public string $moduleDescriptionAr = '';

    public string $moduleDescriptionEn = '';

    public string $moduleObjectivesAr = '';

    public string $moduleObjectivesEn = '';

    public string $moduleStatus = 'published';

    public bool $moduleIsOptional = false;

    public ?int $moduleEstimatedDuration = null;

    /** @var array<int|string> */
    public array $modulePrerequisiteIds = [];

    public ?int $moduleDripDays = null;

    public string $moduleUnlockAt = '';

    public string $moduleCompletionRule = 'all_lessons';

    public string $moduleIcon = '';

    public $moduleImage = null;

    public bool $removeModuleImage = false;

    public ?string $existingModuleImageName = null;

    public string $moduleMetaTitleAr = '';

    public string $moduleMetaTitleEn = '';

    public string $moduleMetaDescriptionAr = '';

    public string $moduleMetaDescriptionEn = '';

    public string $moduleNotesInternal = '';

    public int $moduleSortOrder = 0;

    public string $moduleFormTab = 'basic';

    public string $lessonTitleAr = '';

    public string $lessonTitleEn = '';

    public string $lessonCode = '';

    public string $lessonSummaryAr = '';

    public string $lessonSummaryEn = '';

    public string $lessonType = 'html';

    public string $lessonStatus = 'published';

    public bool $lessonIsPreview = false;

    public bool $lessonCompletionRequired = true;

    public string $lessonBodyAr = '';

    public string $lessonBodyEn = '';

    public string $lessonExternalUrl = '';

    public string $lessonVideoProvider = 'youtube';

    public string $lessonResourceUrl = '';

    public ?int $lessonDuration = null;

    public int $lessonSortOrder = 0;

    public string $lessonMetaTitleAr = '';

    public string $lessonMetaTitleEn = '';

    public string $lessonMetaDescriptionAr = '';

    public string $lessonMetaDescriptionEn = '';

    public string $lessonNotesInternal = '';

    public string $lessonFormTab = 'basic';

    public $lessonFile = null;

    public bool $removeLessonFile = false;

    public ?string $existingLessonFileName = null;

    public ?string $flashMessage = null;

    /** @var array<int> */
    public array $expandedModuleIds = [];

    public function mount(CatalogCourse $course): void
    {
        abort_unless(
            auth()->user()?->canAdmin('catalog.manage') || auth()->user()?->canAdmin('catalog.view'),
            403
        );

        $this->course = $course;
        $this->expandedModuleIds = CatalogCourseModule::query()
            ->where('course_id', $course->id)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();
    }

    public function isModuleExpanded(int $moduleId): bool
    {
        return in_array($moduleId, $this->expandedModuleIds, true);
    }

    public function toggleModuleCollapse(int $moduleId): void
    {
        if ($this->isModuleExpanded($moduleId)) {
            $this->expandedModuleIds = array_values(array_filter(
                $this->expandedModuleIds,
                fn (int $id) => $id !== $moduleId,
            ));
        } else {
            $this->expandedModuleIds[] = $moduleId;
        }
    }

    public function expandAllModules(): void
    {
        $this->expandedModuleIds = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();
    }

    public function collapseAllModules(): void
    {
        $this->expandedModuleIds = [];
    }

    protected function ensureModuleExpanded(int $moduleId): void
    {
        if (! $this->isModuleExpanded($moduleId)) {
            $this->expandedModuleIds[] = $moduleId;
        }
    }

    #[Computed]
    public function curriculum()
    {
        return app(AdminCourseContentService::class)->curriculum($this->course);
    }

    #[Computed]
    public function stats(): array
    {
        return app(AdminCourseContentService::class)->stats($this->course);
    }

    public function openCreateModule(): void
    {
        $this->resetForm();
        $this->panel = 'module';
        $this->editingModuleId = 0;
        $this->moduleSortOrder = $this->stats['modules'] + 1;
        $this->moduleStatus = 'published';
        $this->moduleCompletionRule = 'all_lessons';
        $this->moduleFormTab = 'basic';
    }

    public function openEditModule(int $moduleId): void
    {
        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($moduleId);

        $this->resetForm();
        $this->panel = 'module';
        $this->editingModuleId = $module->id;
        $this->moduleTitleAr = $module->title_ar;
        $this->moduleTitleEn = $module->title_en ?? '';
        $this->moduleCode = $module->code ?? '';
        $this->moduleSummaryAr = $module->summary_ar ?? '';
        $this->moduleSummaryEn = $module->summary_en ?? '';
        $this->moduleDescriptionAr = $module->description_ar ?? '';
        $this->moduleDescriptionEn = $module->description_en ?? '';
        $this->moduleObjectivesAr = $module->objectives_ar ?? '';
        $this->moduleObjectivesEn = $module->objectives_en ?? '';
        $this->moduleStatus = $module->status ?? 'published';
        $this->moduleIsOptional = (bool) $module->is_optional;
        $this->moduleEstimatedDuration = $module->estimated_duration_minutes;
        $this->modulePrerequisiteIds = $module->prerequisiteIds();
        $this->moduleDripDays = $module->drip_days;
        $this->moduleUnlockAt = $module->unlock_at?->format('Y-m-d\TH:i') ?? '';
        $this->moduleCompletionRule = $module->completion_rule ?? 'all_lessons';
        $this->moduleIcon = $module->icon ?? '';
        $this->existingModuleImageName = $module->image_name;
        $this->moduleMetaTitleAr = $module->meta_title_ar ?? '';
        $this->moduleMetaTitleEn = $module->meta_title_en ?? '';
        $this->moduleMetaDescriptionAr = $module->meta_description_ar ?? '';
        $this->moduleMetaDescriptionEn = $module->meta_description_en ?? '';
        $this->moduleNotesInternal = $module->notes_internal ?? '';
        $this->moduleSortOrder = $module->sort_order;
        $this->moduleFormTab = 'basic';
    }

    public function openCreateLesson(int $moduleId): void
    {
        CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($moduleId);

        $this->resetForm();
        $this->panel = 'lesson';
        $this->editingLessonId = 0;
        $this->lessonModuleId = $moduleId;
        $this->ensureModuleExpanded($moduleId);
        $this->lessonSortOrder = (int) CatalogCourseLesson::query()->where('module_id', $moduleId)->max('sort_order') + 1;
        $this->lessonStatus = 'published';
        $this->lessonCompletionRequired = true;
        $this->lessonVideoProvider = 'youtube';
        $this->lessonFormTab = 'basic';
    }

    public function openEditLesson(int $lessonId): void
    {
        $lesson = CatalogCourseLesson::query()
            ->with('module')
            ->whereHas('module', fn ($q) => $q->where('course_id', $this->course->id))
            ->findOrFail($lessonId);

        $this->resetForm();
        $this->panel = 'lesson';
        $this->editingLessonId = $lesson->id;
        $this->lessonModuleId = $lesson->module_id;
        $this->ensureModuleExpanded($lesson->module_id);
        $this->lessonTitleAr = $lesson->title_ar;
        $this->lessonTitleEn = $lesson->title_en ?? '';
        $this->lessonCode = $lesson->code ?? '';
        $this->lessonSummaryAr = $lesson->summary_ar ?? '';
        $this->lessonSummaryEn = $lesson->summary_en ?? '';
        $this->lessonType = $lesson->type;
        $this->lessonStatus = $lesson->status ?? 'published';
        $this->lessonIsPreview = (bool) $lesson->is_preview;
        $this->lessonCompletionRequired = (bool) ($lesson->completion_required ?? true);
        $this->lessonBodyAr = $lesson->body_ar ?? '';
        $this->lessonBodyEn = $lesson->body_en ?? '';
        $this->lessonExternalUrl = $lesson->external_url ?? '';
        $this->lessonVideoProvider = $lesson->video_provider ?? 'youtube';
        $this->lessonResourceUrl = $lesson->resource_url ?? '';
        $this->lessonDuration = $lesson->duration_minutes;
        $this->lessonSortOrder = $lesson->sort_order;
        $this->lessonMetaTitleAr = $lesson->meta_title_ar ?? '';
        $this->lessonMetaTitleEn = $lesson->meta_title_en ?? '';
        $this->lessonMetaDescriptionAr = $lesson->meta_description_ar ?? '';
        $this->lessonMetaDescriptionEn = $lesson->meta_description_en ?? '';
        $this->lessonNotesInternal = $lesson->notes_internal ?? '';
        $this->existingLessonFileName = $lesson->file_name;
        $this->removeLessonFile = false;
        $this->lessonFile = null;
        $this->lessonFormTab = 'basic';
    }

    public function closePanel(): void
    {
        $this->resetForm();
    }

    public function saveModule(AdminCourseContentService $service): void
    {
        $validated = $this->validate([
            'moduleTitleAr' => ['required', 'string', 'max:255'],
            'moduleTitleEn' => ['nullable', 'string', 'max:255'],
            'moduleCode' => ['nullable', 'string', 'max:64'],
            'moduleSummaryAr' => ['nullable', 'string', 'max:1000'],
            'moduleSummaryEn' => ['nullable', 'string', 'max:1000'],
            'moduleDescriptionAr' => ['nullable', 'string'],
            'moduleDescriptionEn' => ['nullable', 'string'],
            'moduleObjectivesAr' => ['nullable', 'string'],
            'moduleObjectivesEn' => ['nullable', 'string'],
            'moduleStatus' => ['required', 'in:published,draft,hidden'],
            'moduleIsOptional' => ['boolean'],
            'moduleEstimatedDuration' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'modulePrerequisiteIds' => ['nullable', 'array'],
            'modulePrerequisiteIds.*' => ['integer'],
            'moduleDripDays' => ['nullable', 'integer', 'min:0', 'max:365'],
            'moduleUnlockAt' => ['nullable', 'date'],
            'moduleCompletionRule' => ['required', 'in:all_lessons,any_lesson,manual'],
            'moduleIcon' => ['nullable', 'string', 'max:64'],
            'moduleImage' => ['nullable', 'image', 'max:5120'],
            'moduleMetaTitleAr' => ['nullable', 'string', 'max:255'],
            'moduleMetaTitleEn' => ['nullable', 'string', 'max:255'],
            'moduleMetaDescriptionAr' => ['nullable', 'string', 'max:500'],
            'moduleMetaDescriptionEn' => ['nullable', 'string', 'max:500'],
            'moduleNotesInternal' => ['nullable', 'string', 'max:2000'],
            'moduleSortOrder' => ['required', 'integer', 'min:1', 'max:999'],
        ], [], [
            'moduleTitleAr' => 'عنوان الوحدة (عربي)',
            'moduleTitleEn' => 'عنوان الوحدة (إنجليزي)',
            'moduleCode' => 'رمز الوحدة',
            'moduleStatus' => 'حالة الوحدة',
            'moduleSortOrder' => 'ترتيب الوحدة',
        ]);

        $prerequisiteIds = array_values(array_filter(
            array_map('intval', $validated['modulePrerequisiteIds'] ?? []),
            fn (int $id) => $id > 0 && $id !== $this->editingModuleId,
        ));

        $payload = [
            'title_ar' => $validated['moduleTitleAr'],
            'title_en' => $validated['moduleTitleEn'] ?: null,
            'code' => $validated['moduleCode'] ?: null,
            'summary_ar' => $validated['moduleSummaryAr'] ?: null,
            'summary_en' => $validated['moduleSummaryEn'] ?: null,
            'description_ar' => $validated['moduleDescriptionAr'] ?: null,
            'description_en' => $validated['moduleDescriptionEn'] ?: null,
            'objectives_ar' => $validated['moduleObjectivesAr'] ?: null,
            'objectives_en' => $validated['moduleObjectivesEn'] ?: null,
            'status' => $validated['moduleStatus'],
            'is_optional' => $validated['moduleIsOptional'] ?? false,
            'estimated_duration_minutes' => $validated['moduleEstimatedDuration'],
            'prerequisite_module_ids' => $prerequisiteIds ?: null,
            'drip_days' => $validated['moduleDripDays'],
            'unlock_at' => filled($validated['moduleUnlockAt'] ?? null) ? $validated['moduleUnlockAt'] : null,
            'completion_rule' => $validated['moduleCompletionRule'],
            'icon' => $validated['moduleIcon'] ?: null,
            'meta_title_ar' => $validated['moduleMetaTitleAr'] ?: null,
            'meta_title_en' => $validated['moduleMetaTitleEn'] ?: null,
            'meta_description_ar' => $validated['moduleMetaDescriptionAr'] ?: null,
            'meta_description_en' => $validated['moduleMetaDescriptionEn'] ?: null,
            'notes_internal' => $validated['moduleNotesInternal'] ?: null,
            'sort_order' => $validated['moduleSortOrder'],
        ];

        if ($this->editingModuleId) {
            $module = CatalogCourseModule::query()
                ->where('course_id', $this->course->id)
                ->findOrFail($this->editingModuleId);
            $service->updateModule($module, $payload, $this->moduleImage, $this->removeModuleImage);
            $this->flashMessage = 'تم تحديث الوحدة.';
        } else {
            $module = $service->createModule($this->course, $payload, $this->moduleImage);
            $this->expandedModuleIds[] = $module->id;
            $this->flashMessage = 'تم إضافة الوحدة.';
        }

        $this->closePanel();
        unset($this->curriculum, $this->stats);
    }

    public function saveLesson(AdminCourseContentService $service): void
    {
        $validated = $this->validate([
            'lessonModuleId' => ['required', 'integer'],
            'lessonTitleAr' => ['required', 'string', 'max:255'],
            'lessonTitleEn' => ['nullable', 'string', 'max:255'],
            'lessonCode' => ['nullable', 'string', 'max:64'],
            'lessonSummaryAr' => ['nullable', 'string', 'max:1000'],
            'lessonSummaryEn' => ['nullable', 'string', 'max:1000'],
            'lessonType' => ['required', 'in:html,video,document'],
            'lessonStatus' => ['required', 'in:published,draft,hidden'],
            'lessonIsPreview' => ['boolean'],
            'lessonCompletionRequired' => ['boolean'],
            'lessonBodyAr' => ['nullable', 'string'],
            'lessonBodyEn' => ['nullable', 'string'],
            'lessonExternalUrl' => ['nullable', 'string', 'max:500'],
            'lessonVideoProvider' => ['nullable', 'in:youtube,vimeo,custom'],
            'lessonResourceUrl' => ['nullable', 'string', 'max:500'],
            'lessonDuration' => ['nullable', 'integer', 'min:1', 'max:999'],
            'lessonSortOrder' => ['required', 'integer', 'min:1', 'max:999'],
            'lessonMetaTitleAr' => ['nullable', 'string', 'max:255'],
            'lessonMetaTitleEn' => ['nullable', 'string', 'max:255'],
            'lessonMetaDescriptionAr' => ['nullable', 'string', 'max:500'],
            'lessonMetaDescriptionEn' => ['nullable', 'string', 'max:500'],
            'lessonNotesInternal' => ['nullable', 'string', 'max:2000'],
            'lessonFile' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,zip'],
        ], [], [
            'lessonTitleAr' => 'عنوان الدرس (عربي)',
            'lessonType' => 'نوع الدرس',
            'lessonExternalUrl' => 'رابط الفيديو',
            'lessonStatus' => 'حالة الدرس',
        ]);

        if ($validated['lessonType'] === 'video' && blank($validated['lessonExternalUrl'])) {
            $this->addError('lessonExternalUrl', 'رابط الفيديو مطلوب لدروس الفيديو.');

            return;
        }

        if ($validated['lessonType'] === 'document'
            && blank($validated['lessonBodyAr'])
            && ! $this->lessonFile
            && blank($this->existingLessonFileName)) {
            $this->addError('lessonBodyAr', 'أضف محتوى نصي أو ارفع ملفاً للدرس.');

            return;
        }

        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($validated['lessonModuleId']);

        $payload = [
            'title_ar' => $validated['lessonTitleAr'],
            'title_en' => $validated['lessonTitleEn'] ?: null,
            'code' => $validated['lessonCode'] ?: null,
            'summary_ar' => $validated['lessonSummaryAr'] ?: null,
            'summary_en' => $validated['lessonSummaryEn'] ?: null,
            'type' => $validated['lessonType'],
            'status' => $validated['lessonStatus'],
            'is_preview' => $validated['lessonIsPreview'] ?? false,
            'completion_required' => $validated['lessonCompletionRequired'] ?? true,
            'body_ar' => $validated['lessonBodyAr'] ?: null,
            'body_en' => $validated['lessonBodyEn'] ?: null,
            'external_url' => $validated['lessonExternalUrl'] ?: null,
            'video_provider' => $validated['lessonVideoProvider'] ?: null,
            'resource_url' => $validated['lessonResourceUrl'] ?: null,
            'duration_minutes' => $validated['lessonDuration'],
            'meta_title_ar' => $validated['lessonMetaTitleAr'] ?: null,
            'meta_title_en' => $validated['lessonMetaTitleEn'] ?: null,
            'meta_description_ar' => $validated['lessonMetaDescriptionAr'] ?: null,
            'meta_description_en' => $validated['lessonMetaDescriptionEn'] ?: null,
            'notes_internal' => $validated['lessonNotesInternal'] ?: null,
            'sort_order' => $validated['lessonSortOrder'],
        ];

        if ($this->editingLessonId) {
            $lesson = CatalogCourseLesson::query()
                ->whereHas('module', fn ($q) => $q->where('course_id', $this->course->id))
                ->findOrFail($this->editingLessonId);
            $service->updateLesson($lesson, $payload, $this->lessonFile, $this->removeLessonFile);
            $this->flashMessage = 'تم تحديث الدرس.';
        } else {
            $service->createLesson($module, $payload, $this->lessonFile);
            $this->flashMessage = 'تم إضافة الدرس.';
        }

        $this->closePanel();
        unset($this->curriculum, $this->stats);
    }

    public function deleteModule(int $moduleId, AdminCourseContentService $service): void
    {
        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($moduleId);

        $service->deleteModule($module);
        $this->flashMessage = 'تم حذف الوحدة ودروسها.';
        $this->closePanel();
        unset($this->curriculum, $this->stats);
    }

    public function deleteLesson(int $lessonId, AdminCourseContentService $service): void
    {
        $lesson = CatalogCourseLesson::query()
            ->whereHas('module', fn ($q) => $q->where('course_id', $this->course->id))
            ->findOrFail($lessonId);

        $service->deleteLesson($lesson);
        $this->flashMessage = 'تم حذف الدرس.';
        $this->closePanel();
        unset($this->curriculum, $this->stats);
    }

    public function moveModule(int $moduleId, string $direction, AdminCourseContentService $service): void
    {
        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($moduleId);

        $service->moveModule($module, $direction);
        unset($this->curriculum);
    }

    public function moveLesson(int $lessonId, string $direction, AdminCourseContentService $service): void
    {
        $lesson = CatalogCourseLesson::query()
            ->whereHas('module', fn ($q) => $q->where('course_id', $this->course->id))
            ->findOrFail($lessonId);

        $service->moveLesson($lesson, $direction);
        unset($this->curriculum);
    }

    /** @param  array<int|string>  $orderedIds */
    public function reorderModules(array $orderedIds, AdminCourseContentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        $service->reorderModules($this->course, array_map('intval', $orderedIds));
        unset($this->curriculum);
    }

    /** @param  array<int|string>  $orderedIds */
    public function reorderLessons(int $moduleId, array $orderedIds, AdminCourseContentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->findOrFail($moduleId);

        $service->reorderLessons($module, array_map('intval', $orderedIds));
        unset($this->curriculum);
    }

    public function duplicateModule(int $moduleId, AdminCourseContentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        $module = CatalogCourseModule::query()
            ->where('course_id', $this->course->id)
            ->with('lessons')
            ->findOrFail($moduleId);

        $service->duplicateModule($module);
        $this->flashMessage = 'تم نسخ الوحدة.';
        unset($this->curriculum, $this->stats);
    }

    public function removeExistingFile(): void
    {
        $this->removeLessonFile = true;
        $this->existingLessonFileName = null;
        $this->lessonFile = null;
    }

    public function removeExistingModuleImage(): void
    {
        $this->removeModuleImage = true;
        $this->existingModuleImageName = null;
        $this->moduleImage = null;
    }

    public function setLessonFormTab(string $tab): void
    {
        $this->lessonFormTab = $tab;
    }

    public function setModuleFormTab(string $tab): void
    {
        $this->moduleFormTab = $tab;
    }

    protected function resetForm(): void
    {
        $this->panel = 'none';
        $this->editingModuleId = null;
        $this->editingLessonId = null;
        $this->lessonModuleId = null;
        $this->reset([
            'moduleTitleAr', 'moduleTitleEn', 'moduleCode',
            'moduleSummaryAr', 'moduleSummaryEn',
            'moduleDescriptionAr', 'moduleDescriptionEn',
            'moduleObjectivesAr', 'moduleObjectivesEn',
            'moduleStatus', 'moduleIsOptional', 'moduleEstimatedDuration',
            'modulePrerequisiteIds', 'moduleDripDays', 'moduleUnlockAt',
            'moduleCompletionRule', 'moduleIcon', 'moduleImage',
            'removeModuleImage', 'existingModuleImageName',
            'moduleMetaTitleAr', 'moduleMetaTitleEn',
            'moduleMetaDescriptionAr', 'moduleMetaDescriptionEn',
            'moduleNotesInternal', 'moduleSortOrder', 'moduleFormTab',
            'lessonTitleAr', 'lessonTitleEn', 'lessonCode',
            'lessonSummaryAr', 'lessonSummaryEn',
            'lessonType', 'lessonStatus', 'lessonIsPreview', 'lessonCompletionRequired',
            'lessonBodyAr', 'lessonBodyEn', 'lessonExternalUrl', 'lessonVideoProvider', 'lessonResourceUrl',
            'lessonDuration', 'lessonSortOrder', 'lessonFormTab',
            'lessonMetaTitleAr', 'lessonMetaTitleEn', 'lessonMetaDescriptionAr', 'lessonMetaDescriptionEn',
            'lessonNotesInternal',
            'lessonFile', 'removeLessonFile', 'existingLessonFileName',
        ]);
        $this->lessonType = 'html';
        $this->lessonStatus = 'published';
        $this->lessonCompletionRequired = true;
        $this->lessonVideoProvider = 'youtube';
        $this->lessonFormTab = 'basic';
        $this->moduleStatus = 'published';
        $this->moduleCompletionRule = 'all_lessons';
        $this->modulePrerequisiteIds = [];
        $this->moduleFormTab = 'basic';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.catalog-courses'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.catalog-courses'), 'label' => 'دورات الكatalog'],
        ['label' => 'محتوى الدورة'],
    ],
])

@if ($flashMessage)
    <div class="admin-alert admin-alert--info is-visible cc-flash" role="status">{{ $flashMessage }}</div>
@endif

<section class="admin-crud-card cc-hero">
    <div class="cc-hero__row">
        <div class="cc-hero__info">
            <a href="{{ route('admin.catalog-courses') }}" class="cc-back-link">← العودة للدورات</a>
            <h2 class="cc-hero__title">{{ $course->title_ar }}</h2>
            <p class="admin-crud-card__meta">
                {{ $course->displayPrice() ?? '—' }}
                · {{ $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري' }}
                · {{ $course->status === 'published' ? 'منشور' : $course->status }}
            </p>
        </div>
        <div class="cc-hero__stats">
            <div class="cc-stat"><strong>{{ $this->stats['modules'] }}</strong><span>وحدات</span></div>
            <div class="cc-stat"><strong>{{ $this->stats['lessons'] }}</strong><span>دروس</span></div>
        </div>
    </div>
</section>

<div @class(['cc-layout', 'cc-layout--module' => $panel === 'module', 'cc-layout--lesson' => $panel === 'lesson'])>
    <div class="cc-main">
        <section class="admin-crud-card">
            <div class="admin-crud-card__head admin-crud-card__head--row">
                <div>
                    <h2>منهج الدورة</h2>
                    <p class="admin-crud-card__meta">رتّب الوحدات والدروس كما تظهر للمتدرب في مشغّل التعلم.</p>
                </div>
                <div class="cc-curriculum-tools">
                    <button type="button" class="cc-icon-btn" wire:click="expandAllModules">فتح الكل</button>
                    <button type="button" class="cc-icon-btn" wire:click="collapseAllModules">طيّ الكل</button>
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="openCreateModule">
                        + وحدة جديدة
                    </button>
                </div>
            </div>

            @if ($this->curriculum->isEmpty())
                <div class="cc-empty">
                    <p>لا يوجد محتوى لهذه الدورة بعد.</p>
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="openCreateModule">إضافة أول وحدة</button>
                </div>
            @else
                <div class="cc-modules" id="cc-modules-list">
                    @foreach ($this->curriculum as $moduleIndex => $module)
                        @php $moduleExpanded = $this->isModuleExpanded($module->id); @endphp
                        <article @class([
                            'cc-module',
                            'cc-module--collapsed' => ! $moduleExpanded,
                            'cc-module--open' => $panel === 'lesson' && $lessonModuleId === $module->id,
                        ]) data-id="{{ $module->id }}">
                            <header class="cc-module__head">
                                <div class="cc-module__title-wrap">
                                    <button type="button" class="cc-drag-handle" title="اسحب لإعادة الترتيب">⠿</button>
                                    <button type="button"
                                        class="cc-module__toggle"
                                        wire:click="toggleModuleCollapse({{ $module->id }})"
                                        aria-expanded="{{ $moduleExpanded ? 'true' : 'false' }}"
                                        title="{{ $moduleExpanded ? 'طيّ الوحدة' : 'فتح الوحدة' }}">
                                        <span class="cc-module__chevron">{{ $moduleExpanded ? '▾' : '▸' }}</span>
                                    </button>
                                    <span class="cc-module__order">{{ $moduleIndex + 1 }}</span>
                                    <button type="button" class="cc-module__title-btn" wire:click="toggleModuleCollapse({{ $module->id }})">
                                        <h3 class="cc-module__title">
                                            @if ($module->icon)
                                                <i class="fa-solid {{ $module->icon }} cc-module__icon"></i>
                                            @endif
                                            {{ $module->displayTitle() }}
                                        </h3>
                                        <span class="cc-module__meta">
                                            {{ $module->lessons->count() }} دروس · ترتيب {{ $module->sort_order }}
                                            @if ($module->estimated_duration_minutes)
                                                · {{ $module->estimated_duration_minutes }} د
                                            @endif
                                            @if ($module->code)
                                                · {{ $module->code }}
                                            @endif
                                        </span>
                                        @if ($module->summary_ar)
                                            <p class="cc-module__summary">{{ Str::limit($module->summary_ar, 120) }}</p>
                                        @endif
                                        <div class="cc-module__badges">
                                            @if (($module->status ?? 'published') !== 'published')
                                                <span class="{{ $module->statusBadgeClass() }}">{{ Str::before($module->statusLabel(), ' —') }}</span>
                                            @endif
                                            @if ($module->is_optional)
                                                <span class="cc-badge cc-badge--optional">اختيارية</span>
                                            @endif
                                            @if ($module->drip_days)
                                                <span class="cc-badge cc-badge--drip">بعد {{ $module->drip_days }} يوم</span>
                                            @endif
                                        </div>
                                    </button>
                                </div>
                                <div class="cc-module__actions">
                                    <button type="button" class="cc-icon-btn" title="تحريك لأعلى" wire:click="moveModule({{ $module->id }}, 'up')" @disabled($moduleIndex === 0)>↑</button>
                                    <button type="button" class="cc-icon-btn" title="تحريك لأسفل" wire:click="moveModule({{ $module->id }}, 'down')" @disabled($moduleIndex === $this->curriculum->count() - 1)>↓</button>
                                    <button type="button" class="cc-icon-btn cc-icon-btn--edit" wire:click="openEditModule({{ $module->id }})">تعديل</button>
                                    <button type="button" class="cc-icon-btn cc-icon-btn--add" wire:click="openCreateLesson({{ $module->id }})">+ درس</button>
                                    <button type="button" class="cc-icon-btn" wire:click="duplicateModule({{ $module->id }})" title="نسخ الوحدة">⧉</button>
                                    <button type="button" class="cc-icon-btn cc-icon-btn--danger"
                                        wire:click="deleteModule({{ $module->id }})"
                                        wire:confirm="حذف الوحدة «{{ $module->displayTitle() }}» وجميع دروسها؟">حذف</button>
                                </div>
                            </header>

                            @if ($moduleExpanded)
                                <div class="cc-module__body">
                                    @if ($module->lessons->isEmpty())
                                        <p class="cc-module__empty">لا توجد دروس — أضف أول درس.</p>
                                    @else
                                        <ul class="cc-lessons" data-module-id="{{ $module->id }}">
                                            @foreach ($module->lessons as $lessonIndex => $lesson)
                                                <li @class(['cc-lesson', 'cc-lesson--active' => $panel === 'lesson' && $editingLessonId === $lesson->id]) data-id="{{ $lesson->id }}">
                                                    <button type="button" class="cc-drag-handle cc-drag-handle--sm" title="اسحب">⠿</button>
                                                    <div class="cc-lesson__main">
                                                        <span class="cc-lesson__type cc-lesson__type--{{ $lesson->type }}">{{ CourseContentOptions::lessonTypeLabel($lesson->type) }}</span>
                                                        <strong>{{ $lesson->displayTitle() }}</strong>
                                                        @if ($lesson->duration_minutes)
                                                            <small>{{ $lesson->duration_minutes }} د</small>
                                                        @endif
                                                        @if ($lesson->file_name)
                                                            <small class="cc-lesson__file">📎 {{ $lesson->file_name }}</small>
                                                        @endif
                                                        @if ($lesson->is_preview)
                                                            <span class="cc-badge cc-badge--preview">معاينة</span>
                                                        @endif
                                                        @if (($lesson->status ?? 'published') !== 'published')
                                                            <span class="{{ $lesson->statusBadgeClass() }}">{{ Str::before($lesson->statusLabel(), ' —') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="cc-lesson__actions">
                                                        <button type="button" class="cc-icon-btn" wire:click="moveLesson({{ $lesson->id }}, 'up')" @disabled($lessonIndex === 0)>↑</button>
                                                        <button type="button" class="cc-icon-btn" wire:click="moveLesson({{ $lesson->id }}, 'down')" @disabled($lessonIndex === $module->lessons->count() - 1)>↓</button>
                                                        <button type="button" class="cc-icon-btn cc-icon-btn--edit" wire:click="openEditLesson({{ $lesson->id }})">تعديل</button>
                                                        <button type="button" class="cc-icon-btn cc-icon-btn--danger"
                                                            wire:click="deleteLesson({{ $lesson->id }})"
                                                            wire:confirm="حذف الدرس «{{ $lesson->displayTitle() }}»؟">حذف</button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <aside class="cc-panel">
        @if ($panel === 'module')
            <section class="admin-crud-card cc-form-card cc-form-card--module">
                <div class="admin-crud-card__head admin-crud-card__head--row">
                    <h2>{{ $editingModuleId ? 'تعديل وحدة' : 'وحدة جديدة' }}</h2>
                    <button type="button" class="cc-icon-btn" wire:click="closePanel">✕</button>
                </div>

                <nav class="cc-form-tabs" aria-label="أقسام نموذج الوحدة">
                    @foreach ([
                        'basic' => 'أساسيات',
                        'content' => 'المحتوى',
                        'settings' => 'إعدادات',
                        'schedule' => 'الجدولة',
                        'media' => 'وسائط',
                        'seo' => 'SEO',
                        'notes' => 'ملاحظات',
                    ] as $tabKey => $tabLabel)
                        <button type="button"
                            @class(['cc-form-tabs__btn', 'cc-form-tabs__btn--active' => $moduleFormTab === $tabKey])
                            wire:click="setModuleFormTab('{{ $tabKey }}')">{{ $tabLabel }}</button>
                    @endforeach
                </nav>

                <form wire:submit="saveModule" class="cc-form">
                    @if ($moduleFormTab === 'basic')
                        <div class="admin-field">
                            <label>عنوان الوحدة (عربي) *</label>
                            <input type="text" class="admin-control" wire:model="moduleTitleAr">
                            @error('moduleTitleAr') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-field">
                            <label>عنوان الوحدة (إنجليزي)</label>
                            <input type="text" class="admin-control" wire:model="moduleTitleEn" dir="ltr">
                        </div>
                        <div class="admin-field">
                            <label>رمز الوحدة (داخلي)</label>
                            <input type="text" class="admin-control" wire:model="moduleCode" dir="ltr" placeholder="M01-INTRO">
                            <small class="cc-field-hint">اختياري — للتتبع الداخلي والتصدير</small>
                        </div>
                        <div class="admin-filter-grid" style="grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <div class="admin-field">
                                <label>حالة الوحدة *</label>
                                <select class="admin-control" wire:model="moduleStatus">
                                    @foreach (CourseModuleOptions::statuses() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="admin-field">
                                <label>ترتيب العرض</label>
                                <input type="number" class="admin-control" wire:model="moduleSortOrder" min="1" max="999">
                                @error('moduleSortOrder') <span class="admin-field-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <label class="cc-check">
                            <input type="checkbox" wire:model="moduleIsOptional">
                            <span>وحدة اختيارية (لا تؤثر على إكمال الدورة)</span>
                        </label>
                    @elseif ($moduleFormTab === 'content')
                        <div class="admin-field">
                            <label>ملخص قصير (عربي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="moduleSummaryAr" rows="2" maxlength="1000"></textarea>
                            <small class="cc-field-hint">يظهر تحت عنوان الوحدة في المنهج</small>
                        </div>
                        <div class="admin-field">
                            <label>ملخص قصير (إنجليزي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="moduleSummaryEn" rows="2" dir="ltr"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>وصف الوحدة (عربي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'moduleDescriptionAr', 'value' => $moduleDescriptionAr, 'direction' => 'rtl', 'language' => 'ar'])
                        </div>
                        <div class="admin-field">
                            <label>وصف الوحدة (إنجليزي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'moduleDescriptionEn', 'value' => $moduleDescriptionEn, 'direction' => 'ltr', 'language' => 'en'])
                        </div>
                        <div class="admin-field">
                            <label>أهداف التعلم (عربي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'moduleObjectivesAr', 'value' => $moduleObjectivesAr, 'direction' => 'rtl', 'language' => 'ar'])
                        </div>
                        <div class="admin-field">
                            <label>أهداف التعلم (إنجليزي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'moduleObjectivesEn', 'value' => $moduleObjectivesEn, 'direction' => 'ltr', 'language' => 'en'])
                        </div>
                    @elseif ($moduleFormTab === 'settings')
                        <div class="admin-field">
                            <label>المدة التقديرية (دقيقة)</label>
                            <input type="number" class="admin-control" wire:model="moduleEstimatedDuration" min="1" max="9999" placeholder="مثال: 180">
                        </div>
                        <div class="admin-field">
                            <label>قاعدة إكمال الوحدة</label>
                            <select class="admin-control" wire:model="moduleCompletionRule">
                                @foreach (CourseModuleOptions::completionRules() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($this->curriculum->count() > 1)
                            <div class="admin-field">
                                <label>متطلبات سابقة (وحدات يجب إكمالها أولاً)</label>
                                <div class="cc-checklist">
                                    @foreach ($this->curriculum as $mod)
                                        @if ($mod->id !== $editingModuleId)
                                            <label class="cc-check">
                                                <input type="checkbox" value="{{ $mod->id }}" wire:model="modulePrerequisiteIds">
                                                <span>{{ $mod->displayTitle() }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="cc-field-hint">أضف وحدات أخرى لتفعيل المتطلبات السابقة.</p>
                        @endif
                    @elseif ($moduleFormTab === 'schedule')
                        <div class="admin-field">
                            <label>فتح تدريجي — بعد (أيام من التسجيل)</label>
                            <input type="number" class="admin-control" wire:model="moduleDripDays" min="0" max="365" placeholder="0 = فوري">
                            <small class="cc-field-hint">اتركه فارغاً للفتح الفوري</small>
                        </div>
                        <div class="admin-field">
                            <label>تاريخ/وقت فتح محدد</label>
                            <input type="datetime-local" class="admin-control" wire:model="moduleUnlockAt">
                            <small class="cc-field-hint">اختياري — يُطبَّق بالإضافة إلى التدريجي</small>
                        </div>
                    @elseif ($moduleFormTab === 'media')
                        <div class="admin-field">
                            <label>أيقونة الوحدة</label>
                            <select class="admin-control" wire:model="moduleIcon">
                                @foreach (CourseModuleOptions::icons() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label>صورة غلاف الوحدة</label>
                            @if ($existingModuleImageName && $editingModuleId)
                                <div class="cc-file-current">
                                    <span>🖼 {{ $existingModuleImageName }}</span>
                                    <a href="{{ route('admin.catalog-courses.module-image', ['course' => $course->id, 'module' => $editingModuleId]) }}" target="_blank" class="cc-icon-btn">عرض</a>
                                    <button type="button" class="cc-icon-btn cc-icon-btn--danger" wire:click="removeExistingModuleImage">إزالة</button>
                                </div>
                            @endif
                            <input type="file" class="admin-control" wire:model="moduleImage" accept="image/*">
                            <div wire:loading wire:target="moduleImage" class="small text-muted mt-1">جاري رفع الصورة…</div>
                            @error('moduleImage') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                    @elseif ($moduleFormTab === 'seo')
                        <div class="admin-field">
                            <label>Meta Title (عربي)</label>
                            <input type="text" class="admin-control" wire:model="moduleMetaTitleAr">
                        </div>
                        <div class="admin-field">
                            <label>Meta Title (English)</label>
                            <input type="text" class="admin-control" wire:model="moduleMetaTitleEn" dir="ltr">
                        </div>
                        <div class="admin-field">
                            <label>Meta Description (عربي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="moduleMetaDescriptionAr" rows="3"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>Meta Description (English)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="moduleMetaDescriptionEn" rows="3" dir="ltr"></textarea>
                        </div>
                    @elseif ($moduleFormTab === 'notes')
                        <div class="admin-field">
                            <label>ملاحظات داخلية (للمشرفين فقط)</label>
                            <textarea class="admin-control cc-textarea" wire:model="moduleNotesInternal" rows="6" placeholder="ملاحظات للفريق — لا تظهر للمتدرب"></textarea>
                        </div>
                    @endif

                    <div class="cc-form__actions">
                        <button type="submit" class="admin-btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveModule">حفظ الوحدة</span>
                            <span wire:loading wire:target="saveModule">جاري الحفظ…</span>
                        </button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closePanel">إلغاء</button>
                    </div>
                </form>
            </section>
        @elseif ($panel === 'lesson')
            <section class="admin-crud-card cc-form-card cc-form-card--module">
                <div class="admin-crud-card__head admin-crud-card__head--row">
                    <h2>{{ $editingLessonId ? 'تعديل درس' : 'درس جديد' }}</h2>
                    <button type="button" class="cc-icon-btn" wire:click="closePanel">✕</button>
                </div>

                <nav class="cc-form-tabs" aria-label="أقسام نموذج الدرس">
                    @foreach ([
                        'basic' => 'أساسيات',
                        'content' => 'المحتوى',
                        'media' => 'وسائط',
                        'settings' => 'إعدادات',
                        'seo' => 'SEO',
                        'notes' => 'ملاحظات',
                    ] as $tabKey => $tabLabel)
                        <button type="button"
                            @class(['cc-form-tabs__btn', 'cc-form-tabs__btn--active' => $lessonFormTab === $tabKey])
                            wire:click="setLessonFormTab('{{ $tabKey }}')">{{ $tabLabel }}</button>
                    @endforeach
                </nav>

                <form wire:submit="saveLesson" class="cc-form">
                    @if ($lessonFormTab === 'basic')
                        <div class="admin-field">
                            <label>الوحدة *</label>
                            <select class="admin-control" wire:model="lessonModuleId">
                                @foreach ($this->curriculum as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->displayTitle() }}</option>
                                @endforeach
                            </select>
                            @error('lessonModuleId') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-field">
                            <label>عنوان الدرس (عربي) *</label>
                            <input type="text" class="admin-control" wire:model="lessonTitleAr">
                            @error('lessonTitleAr') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-field">
                            <label>عنوان الدرس (إنجليزي)</label>
                            <input type="text" class="admin-control" wire:model="lessonTitleEn" dir="ltr">
                        </div>
                        <div class="admin-field">
                            <label>رمز الدرس (داخلي)</label>
                            <input type="text" class="admin-control" wire:model="lessonCode" dir="ltr" placeholder="L01-INTRO">
                        </div>
                        <div class="admin-filter-grid" style="grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <div class="admin-field">
                                <label>نوع الدرس *</label>
                                <select class="admin-control" wire:model.live="lessonType">
                                    @foreach (CourseContentOptions::lessonTypes() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="admin-field">
                                <label>حالة الدرس *</label>
                                <select class="admin-control" wire:model="lessonStatus">
                                    @foreach (CourseContentOptions::lessonStatuses() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="admin-filter-grid" style="grid-template-columns:1fr 1fr;gap:0.75rem;">
                            <div class="admin-field">
                                <label>المدة (دقيقة)</label>
                                <input type="number" class="admin-control" wire:model="lessonDuration" min="1" max="999">
                            </div>
                            <div class="admin-field">
                                <label>ترتيب العرض</label>
                                <input type="number" class="admin-control" wire:model="lessonSortOrder" min="1" max="999">
                            </div>
                        </div>
                    @elseif ($lessonFormTab === 'content')
                        <div class="admin-field">
                            <label>ملخص الدرس (عربي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="lessonSummaryAr" rows="2"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>ملخص الدرس (إنجليزي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="lessonSummaryEn" rows="2" dir="ltr"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>المحتوى (عربي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'lessonBodyAr', 'value' => $lessonBodyAr, 'direction' => 'rtl', 'language' => 'ar'])
                            @error('lessonBodyAr') <span class="admin-field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="admin-field">
                            <label>المحتوى (إنجليزي)</label>
                            @include('partials.admin.wysiwyg', ['model' => 'lessonBodyEn', 'value' => $lessonBodyEn, 'direction' => 'ltr', 'language' => 'en'])
                        </div>
                    @elseif ($lessonFormTab === 'media')
                        @if ($lessonType === 'video')
                            <div class="admin-field">
                                <label>مزود الفيديو</label>
                                <select class="admin-control" wire:model="lessonVideoProvider">
                                    @foreach (CourseContentOptions::videoProviders() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="admin-field">
                                <label>رابط الفيديو *</label>
                                <input type="url" class="admin-control" wire:model="lessonExternalUrl" dir="ltr" placeholder="https://youtu.be/... أو embed URL">
                                <small class="cc-field-hint">يُحوَّل تلقائياً إلى رابط embed عند الحفظ</small>
                                @error('lessonExternalUrl') <span class="admin-field-error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        @if ($lessonType === 'document')
                            <div class="admin-field">
                                <label>ملف الدرس (PDF / Word / PPT)</label>
                                @if ($existingLessonFileName)
                                    <div class="cc-file-current">
                                        <span>📎 {{ $existingLessonFileName }}</span>
                                        <button type="button" class="cc-icon-btn cc-icon-btn--danger" wire:click="removeExistingFile">إزالة</button>
                                    </div>
                                @endif
                                <input type="file" class="admin-control" wire:model="lessonFile">
                                <div wire:loading wire:target="lessonFile" class="small text-muted mt-1">جاري رفع الملف…</div>
                                @error('lessonFile') <span class="admin-field-error">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        <div class="admin-field">
                            <label>رابط مورد إضافي</label>
                            <input type="url" class="admin-control" wire:model="lessonResourceUrl" dir="ltr" placeholder="https://...">
                            <small class="cc-field-hint">رابط خارجي للمراجع أو المواد التكميلية</small>
                        </div>
                    @elseif ($lessonFormTab === 'settings')
                        <label class="cc-check">
                            <input type="checkbox" wire:model="lessonIsPreview">
                            <span>درس معاينة مجاني (يظهر كـ preview للزوار)</span>
                        </label>
                        <label class="cc-check">
                            <input type="checkbox" wire:model="lessonCompletionRequired">
                            <span>إكبار المتدرب على تأكيد إكمال الدرس للمتابعة</span>
                        </label>
                    @elseif ($lessonFormTab === 'seo')
                        <div class="admin-field">
                            <label>Meta Title (عربي)</label>
                            <input type="text" class="admin-control" wire:model="lessonMetaTitleAr">
                        </div>
                        <div class="admin-field">
                            <label>Meta Title (English)</label>
                            <input type="text" class="admin-control" wire:model="lessonMetaTitleEn" dir="ltr">
                        </div>
                        <div class="admin-field">
                            <label>Meta Description (عربي)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="lessonMetaDescriptionAr" rows="3"></textarea>
                        </div>
                        <div class="admin-field">
                            <label>Meta Description (English)</label>
                            <textarea class="admin-control cc-textarea cc-textarea--sm" wire:model="lessonMetaDescriptionEn" rows="3" dir="ltr"></textarea>
                        </div>
                    @elseif ($lessonFormTab === 'notes')
                        <div class="admin-field">
                            <label>ملاحظات داخلية</label>
                            <textarea class="admin-control cc-textarea" wire:model="lessonNotesInternal" rows="6" placeholder="ملاحظات للفريق — لا تظهر للمتدرب"></textarea>
                        </div>
                    @endif

                    <div class="cc-form__actions">
                        <button type="submit" class="admin-btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveLesson">حفظ الدرس</span>
                            <span wire:loading wire:target="saveLesson">جاري الحفظ…</span>
                        </button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closePanel">إلغاء</button>
                    </div>
                </form>
            </section>
        @else
            <section class="admin-crud-card cc-form-card cc-form-card--hint">
                <div class="cc-hint">
                    <div class="cc-hint__icon">📚</div>
                    <h3>إدارة المحتوى</h3>
                    <p>اختر «وحدة جديدة» أو «+ درس» لتعديل المحتوى هنا. يظهر للمتدرب في صفحة متابعة التعلم.</p>
                    <ul>
                        <li><strong>HTML</strong> — نص تعليمي منسّق</li>
                        <li><strong>فيديو</strong> — رابط embed (YouTube/Vimeo)</li>
                        <li><strong>قراءة</strong> — محتوى نصي للقراءة</li>
                    </ul>
                </div>
            </section>
        @endif
    </aside>
</div>

<style>
    .cc-flash { margin-bottom: 0.75rem; }
    .cc-hero__row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; }
    .cc-back-link { display: inline-block; font-size: 0.78rem; font-weight: 600; color: var(--sa-green, #1b8354); margin-bottom: 0.35rem; text-decoration: none; }
    .cc-back-link:hover { text-decoration: underline; }
    .cc-hero__title { margin: 0; font-size: 1.15rem; font-weight: 800; }
    .cc-hero__stats { display: flex; gap: 0.65rem; }
    .cc-stat { background: var(--sa-mist, #f7faf8); border: 1px solid var(--sa-border, rgba(22,93,49,.1)); border-radius: 10px; padding: 0.55rem 0.85rem; text-align: center; min-width: 4.5rem; }
    .cc-stat strong { display: block; font-size: 1.1rem; color: var(--sa-green-dark, #135f3d); }
    .cc-stat span { font-size: 0.65rem; color: var(--sa-muted, #5c6b64); font-weight: 600; }
    .cc-layout { display: grid; grid-template-columns: 1fr minmax(280px, 360px); gap: 1rem; align-items: start; }
    .cc-layout--module { grid-template-columns: 1fr minmax(340px, 480px); }
    .cc-layout--lesson { grid-template-columns: 1fr minmax(340px, 480px); }
    .cc-empty { text-align: center; padding: 2.5rem 1rem; color: var(--sa-muted, #5c6b64); }
    .cc-modules { display: flex; flex-direction: column; gap: 0.75rem; }
    .cc-curriculum-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; }
    .cc-module__toggle { border: none; background: transparent; cursor: pointer; padding: 0.1rem 0.25rem; line-height: 1; color: var(--sa-green-dark, #135f3d); }
    .cc-module__chevron { font-size: 0.95rem; font-weight: 800; display: inline-block; min-width: 0.85rem; }
    .cc-module__title-btn { border: none; background: transparent; padding: 0; text-align: right; cursor: pointer; min-width: 0; flex: 1; }
    .cc-module__title-btn:hover .cc-module__title { color: var(--sa-green, #1b8354); }
    .cc-module--collapsed .cc-module__head { border-bottom: none; }
    .cc-module__body { border-top: 1px solid var(--sa-border, rgba(22,93,49,.08)); }
    .cc-module { border: 1px solid var(--sa-border, rgba(22,93,49,.1)); border-radius: 12px; overflow: hidden; background: #fff; }
    .cc-module--open { border-color: var(--sa-green, #1b8354); box-shadow: 0 0 0 2px rgba(27,131,84,.08); }
    .cc-module__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.65rem; padding: 0.75rem 1rem; background: var(--sa-mist, #f7faf8); border-bottom: 1px solid var(--sa-border, rgba(22,93,49,.08)); }
    .cc-module__title-wrap { display: flex; align-items: flex-start; gap: 0.65rem; min-width: 0; flex: 1; }
    .cc-drag-handle { border: none; background: transparent; cursor: grab; color: var(--sa-muted, #5c6b64); font-size: 1rem; padding: 0.15rem 0.25rem; line-height: 1; }
    .cc-drag-handle--sm { font-size: 0.85rem; padding: 0; }
    .cc-drag-handle:active { cursor: grabbing; }
    .cc-lesson { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.55rem 0.65rem; border-radius: 8px; border: 1px solid transparent; }
    .cc-lesson__file { color: var(--sa-green-dark, #135f3d); }
    .cc-file-current { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.45rem 0.65rem; background: var(--sa-mist, #f7faf8); border-radius: 8px; margin-bottom: 0.35rem; font-size: 0.78rem; }
    .sortable-ghost { opacity: 0.45; background: var(--sa-green-soft, #e8f4ee); }
    .cc-module__order { width: 1.75rem; height: 1.75rem; border-radius: 8px; background: var(--sa-green, #1b8354); color: #fff; display: grid; place-items: center; font-size: 0.75rem; font-weight: 800; flex-shrink: 0; }
    .cc-module__title { margin: 0; font-size: 0.88rem; font-weight: 800; display: flex; align-items: center; gap: 0.35rem; }
    .cc-module__icon { color: var(--sa-green, #1b8354); font-size: 0.82rem; }
    .cc-module__summary { margin: 0.25rem 0 0; font-size: 0.72rem; color: var(--sa-muted, #5c6b64); line-height: 1.45; }
    .cc-module__badges { display: flex; flex-wrap: wrap; gap: 0.3rem; margin-top: 0.35rem; }
    .cc-badge { font-size: 0.6rem; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 999px; }
    .cc-badge--published { background: #e8f4ee; color: #135f3d; }
    .cc-badge--draft { background: #fef3c7; color: #b45309; }
    .cc-badge--hidden { background: #fee2e2; color: #b91c1c; }
    .cc-badge--optional { background: #ede9fe; color: #6d28d9; }
    .cc-badge--drip { background: #dbeafe; color: #1d4ed8; }
    .cc-badge--preview { background: #fce7f3; color: #be185d; }
    .cc-form-card--module { max-height: calc(100vh - 2rem); overflow: auto; }
    .cc-form-tabs { display: flex; flex-wrap: wrap; gap: 0.25rem; margin-bottom: 0.75rem; padding-bottom: 0.65rem; border-bottom: 1px solid var(--sa-border, rgba(22,93,49,.1)); }
    .cc-form-tabs__btn { border: 1px solid var(--sa-border, rgba(22,93,49,.12)); background: #fff; border-radius: 999px; padding: 0.2rem 0.55rem; font-size: 0.65rem; font-weight: 700; cursor: pointer; color: var(--sa-muted, #5c6b64); }
    .cc-form-tabs__btn--active { background: var(--sa-green, #1b8354); border-color: var(--sa-green, #1b8354); color: #fff; }
    .cc-field-hint { display: block; font-size: 0.68rem; color: var(--sa-muted, #5c6b64); margin-top: 0.2rem; }
    .cc-check { display: flex; align-items: flex-start; gap: 0.45rem; font-size: 0.78rem; cursor: pointer; }
    .cc-check input { margin-top: 0.15rem; }
    .cc-checklist { display: flex; flex-direction: column; gap: 0.35rem; max-height: 10rem; overflow: auto; padding: 0.5rem; background: var(--sa-mist, #f7faf8); border-radius: 8px; }
    .cc-textarea--sm { min-height: 3rem; }
    .cc-module__meta { font-size: 0.68rem; color: var(--sa-muted, #5c6b64); font-weight: 600; }
    .cc-module__actions, .cc-lesson__actions { display: flex; flex-wrap: wrap; gap: 0.3rem; }
    .cc-module__empty { margin: 0; padding: 0.85rem 1rem; font-size: 0.78rem; color: var(--sa-muted, #5c6b64); }
    .cc-lessons { list-style: none; margin: 0; padding: 0.45rem; display: flex; flex-direction: column; gap: 0.35rem; }
    .cc-lesson:hover { background: var(--sa-mist, #f7faf8); }
    .cc-lesson--active { background: var(--sa-green-soft, #e8f4ee); border-color: rgba(27,131,84,.2); }
    .cc-lesson__main { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; min-width: 0; flex: 1; }
    .cc-lesson__main strong { font-size: 0.8rem; }
    .cc-lesson__main small { font-size: 0.65rem; color: var(--sa-muted, #5c6b64); }
    .cc-lesson__type { font-size: 0.62rem; font-weight: 700; padding: 0.12rem 0.45rem; border-radius: 999px; }
    .cc-lesson__type--video { background: #dbeafe; color: #1d4ed8; }
    .cc-lesson__type--document { background: #fef3c7; color: #b45309; }
    .cc-lesson__type--html { background: #e8f4ee; color: #135f3d; }
    .cc-icon-btn { border: 1px solid var(--sa-border, rgba(22,93,49,.15)); background: #fff; border-radius: 6px; padding: 0.2rem 0.45rem; font-size: 0.68rem; font-weight: 700; cursor: pointer; color: var(--sa-ink, #1a1a1a); }
    .cc-icon-btn:hover:not(:disabled) { border-color: var(--sa-green, #1b8354); color: var(--sa-green-dark, #135f3d); }
    .cc-icon-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .cc-icon-btn--edit { color: var(--sa-green-dark, #135f3d); }
    .cc-icon-btn--add { color: var(--sa-green, #1b8354); }
    .cc-icon-btn--danger { color: #b91c1c; border-color: #fecaca; }
    .cc-form-card { position: sticky; top: 1rem; }
    .cc-form-card--hint { background: var(--sa-mist, #f7faf8); }
    .cc-form { display: flex; flex-direction: column; gap: 0.65rem; }
    .cc-textarea { min-height: 5rem; resize: vertical; font-family: inherit; }
    .cc-form__actions { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.35rem; }
    .cc-hint { padding: 0.5rem; text-align: center; }
    .cc-hint__icon { font-size: 2rem; margin-bottom: 0.35rem; }
    .cc-hint h3 { margin: 0 0 0.35rem; font-size: 0.92rem; }
    .cc-hint p { margin: 0 0 0.75rem; font-size: 0.78rem; color: var(--sa-muted, #5c6b64); line-height: 1.5; }
    .cc-hint ul { text-align: right; margin: 0; padding: 0 1.1rem; font-size: 0.75rem; color: var(--sa-muted, #5c6b64); }
    .cc-hint li { margin-bottom: 0.25rem; }
    .admin-field-error { display: block; font-size: 0.72rem; color: #b91c1c; margin-top: 0.2rem; }
    @media (max-width: 960px) {
        .cc-layout { grid-template-columns: 1fr; }
        .cc-form-card { position: static; }
        .cc-panel { order: -1; }
    }
</style>

@script
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function initCatalogSortables() {
        const modulesList = document.getElementById('cc-modules-list');
        if (modulesList && !modulesList.dataset.sortableInit) {
            modulesList.dataset.sortableInit = '1';
            Sortable.create(modulesList, {
                handle: '.cc-module__head .cc-drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd() {
                    const ids = [...modulesList.querySelectorAll('.cc-module')].map(el => el.dataset.id);
                    $wire.reorderModules(ids);
                },
            });
        }

        document.querySelectorAll('.cc-lessons[data-module-id]').forEach(list => {
            if (list.dataset.sortableInit) return;
            list.dataset.sortableInit = '1';
            const moduleId = list.dataset.moduleId;
            Sortable.create(list, {
                handle: '.cc-drag-handle--sm',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd() {
                    const ids = [...list.querySelectorAll('.cc-lesson')].map(el => el.dataset.id);
                    $wire.reorderLessons(parseInt(moduleId, 10), ids);
                },
            });
        });
    }

    initCatalogSortables();
    Livewire.hook('morph.updated', () => {
        initCatalogSortables();
        if (window.domainWysiwyg) {
            window.domainWysiwyg.initAll(document);
        }
    });
</script>
@endscript

@include('partials.admin.shell-end')
