<?php

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Services\MediaLibraryService;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'مكتبة الوسائط',
    'adminPageDesc' => 'إدارة الملفات والمجلدات، تحسين الصور، واختيار الوسائط مباشرة في نماذج المنصة',
    'adminLayout' => 'app',
])]
#[Title('مكتبة الوسائط | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    #[Url(as: 'folder')]
    public ?int $folderId = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'log')]
    public ?string $log = null;

    public string $folderName = '';

    public string $renameFolderName = '';

    public ?int $renamingFolderId = null;

    public bool $showCreateFolder = false;

    /** @var array<int, mixed> */
    public array $uploads = [];

    public ?int $selectedId = null;

    public string $editName = '';

    public string $altText = '';

    public int $optScale = 100;

    public ?int $optMaxWidth = null;

    public ?int $optMaxHeight = null;

    public int $optQuality = 85;

    public string $optFormat = 'keep';

    public string $viewMode = 'grid';

    public string $typeFilter = 'all';

    public string $sortBy = 'newest';

    /** @var array<int, int> */
    public array $bulkIds = [];

    public bool $bulkMode = false;

    public ?string $message = null;

    public string $messageKind = 'success';

    public string $copiedUrl = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('media.view'), 403);

        if ($this->isServerMode()) {
            abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        }

        if ($this->folderId) {
            MediaFolder::query()->findOrFail($this->folderId);
        }
    }

    public function isServerMode(): bool
    {
        return $this->log === 'truee';
    }

    public function updatedSearch(): void
    {
        // keep selection if still visible
    }

    public function updatedUploads(): void
    {
        $this->uploadFiles();
    }

    public function createFolder(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);

        try {
            $service->createFolder($this->folderName, $this->folderId, auth()->user());
            $this->folderName = '';
            $this->showCreateFolder = false;
            $this->flash('تم إنشاء المجلد.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function openCreateFolder(): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $this->showCreateFolder = true;
        $this->folderName = '';
    }

    public function cancelCreateFolder(): void
    {
        $this->showCreateFolder = false;
        $this->folderName = '';
    }

    public function openFolder(int $id): void
    {
        MediaFolder::query()->findOrFail($id);
        $this->folderId = $id;
        $this->selectedId = null;
        $this->search = '';
    }

    public function goUp(): void
    {
        if (! $this->folderId) {
            return;
        }

        $current = MediaFolder::query()->find($this->folderId);
        $this->folderId = $current?->parent_id;
        $this->selectedId = null;
    }

    public function goRoot(): void
    {
        $this->folderId = null;
        $this->selectedId = null;
    }

    public function startRenameFolder(int $id): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $folder = MediaFolder::query()->findOrFail($id);
        $this->renamingFolderId = $folder->id;
        $this->renameFolderName = $folder->name;
    }

    public function applyRenameFolder(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);

        if (! $this->renamingFolderId) {
            return;
        }

        try {
            $folder = MediaFolder::query()->findOrFail($this->renamingFolderId);
            $service->renameFolder($folder, $this->renameFolderName);
            $this->renamingFolderId = null;
            $this->renameFolderName = '';
            $this->flash('تم تحديث اسم المجلد.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function cancelRenameFolder(): void
    {
        $this->renamingFolderId = null;
        $this->renameFolderName = '';
    }

    public function deleteFolder(int $id, MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $folder = MediaFolder::query()->findOrFail($id);
        $service->deleteFolder($folder);

        if ($this->folderId === $id) {
            $this->folderId = $folder->parent_id;
        }

        $this->flash('تم حذف المجلد ومحتوياته.');
    }

    public function uploadFiles(): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);

        if ($this->uploads === []) {
            return;
        }

        $this->validate([
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['file', 'max:51200'],
        ], [
            'uploads.required' => 'اختر ملفاً للرفع.',
            'uploads.*.max' => 'حجم الملف يتجاوز 50 م.ب.',
        ]);

        $service = app(MediaLibraryService::class);
        $count = 0;
        foreach ($this->uploads as $file) {
            if ($file) {
                $service->upload($file, $this->folderId, auth()->user());
                $count++;
            }
        }

        $this->uploads = [];
        $this->flash($count === 1 ? 'تم رفع ملف واحد.' : "تم رفع {$count} ملفات.");
    }

    public function selectAsset(int $id): void
    {
        $asset = MediaAsset::query()->findOrFail($id);
        $this->selectedId = $asset->id;
        $this->editName = $asset->name;
        $this->altText = (string) ($asset->alt_text ?? '');
        $this->optScale = 100;
        $this->optMaxWidth = $asset->width;
        $this->optMaxHeight = $asset->height;
        $this->optQuality = 85;
        $this->optFormat = 'keep';
    }

    public function clearSelection(): void
    {
        $this->selectedId = null;
    }

    public function applyRename(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();

        try {
            $service->renameAsset($asset, $this->editName);
            $this->flash('تم تحديث اسم الملف.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function saveAlt(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();
        $service->saveAltText($asset, $this->altText);
        $this->flash('تم حفظ النص البديل.');
    }

    public function applyPreset(string $preset): void
    {
        match ($preset) {
            'light' => [$this->optQuality = 82, $this->optScale = 100],
            'strong' => [$this->optQuality = 65, $this->optScale = 85],
            'hq' => [$this->optQuality = 92, $this->optScale = 100],
            'webp' => [$this->optFormat = 'webp', $this->optQuality = 80],
            default => null,
        };
    }

    public function applyOptimize(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();

        try {
            $updated = $service->optimize($asset, [
                'scale' => $this->optScale,
                'max_width' => $this->optMaxWidth,
                'max_height' => $this->optMaxHeight,
                'quality' => $this->optQuality,
                'format' => $this->optFormat,
            ]);
            $this->selectAsset($updated->id);
            $this->flash('تم تطبيق التحسين وحفظ الملف.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function enablePublicLink(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();
        $service->enablePublicLink($asset);
        $this->flash('تم إنشاء رابط المشاركة.');
    }

    public function revokePublicLink(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();
        $service->revokePublicLink($asset);
        $this->flash('تم إلغاء رابط المشاركة.');
    }

    public function deleteAsset(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();
        $service->deleteAsset($asset);
        $this->selectedId = null;
        $this->flash('تم حذف الملف.');
    }

    public function deleteAssetById(int $id, MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = MediaAsset::query()->findOrFail($id);
        $service->deleteAsset($asset);

        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }

        $this->bulkIds = array_values(array_filter($this->bulkIds, fn ($x) => $x !== $id));
        $this->flash('تم حذف الملف.');
    }

    public function toggleBulkMode(): void
    {
        $this->bulkMode = ! $this->bulkMode;
        $this->bulkIds = [];
    }

    public function toggleBulkId(int $id): void
    {
        if (in_array($id, $this->bulkIds, true)) {
            $this->bulkIds = array_values(array_filter($this->bulkIds, fn ($x) => $x !== $id));
        } else {
            $this->bulkIds[] = $id;
        }
    }

    public function selectAllVisible(): void
    {
        $this->bulkIds = $this->assets->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function clearBulk(): void
    {
        $this->bulkIds = [];
    }

    public function deleteBulk(MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);

        if ($this->bulkIds === []) {
            return;
        }

        $count = $service->deleteMany($this->bulkIds);
        $this->bulkIds = [];
        $this->selectedId = null;
        $this->flash($count === 1 ? 'تم حذف ملف واحد.' : "تم حذف {$count} ملفات.");
    }

    public function copyAssetUrl(int $id): void
    {
        $asset = MediaAsset::query()->findOrFail($id);
        $this->copiedUrl = $asset->formValue();
        $this->dispatch('media-url-copied', url: $this->copiedUrl);
        $this->flash('تم نسخ رابط الملف.');
    }

    public function moveSelectedToFolder(?int $folderId, MediaLibraryService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        $asset = $this->requireSelected();
        $service->moveAsset($asset, $folderId);
        $this->flash('تم نقل الملف.');
    }

    protected function requireSelected(): MediaAsset
    {
        return MediaAsset::query()->findOrFail($this->selectedId);
    }

    protected function flash(string $message, string $kind = 'success'): void
    {
        $this->message = $message;
        $this->messageKind = $kind;
    }

    #[Computed]
    public function stats(): array
    {
        return app(MediaLibraryService::class)->stats();
    }

    #[Computed]
    public function currentFolder(): ?MediaFolder
    {
        return $this->folderId
            ? MediaFolder::query()->find($this->folderId)
            : null;
    }

    /** @return Collection<int, MediaFolder> */
    #[Computed]
    public function breadcrumbs(): Collection
    {
        $crumbs = collect();
        $folder = $this->currentFolder;

        while ($folder) {
            $crumbs->prepend($folder);
            $folder = $folder->parent;
        }

        return $crumbs;
    }

    /** @return Collection<int, MediaFolder> */
    #[Computed]
    public function folders(): Collection
    {
        $query = MediaFolder::query()
            ->where('parent_id', $this->folderId)
            ->withCount('assets')
            ->orderBy('name');

        if (filled($this->search)) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->get();
    }

    /** @return Collection<int, MediaAsset> */
    #[Computed]
    public function assets(): Collection
    {
        $query = MediaAsset::query();

        if (filled($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('original_name', 'like', $term)
                    ->orWhere('alt_text', 'like', $term)
                    ->orWhere('mime_type', 'like', $term)
                    ->orWhere('extension', 'like', $term);
            });
        } else {
            $query->where('folder_id', $this->folderId);
        }

        match ($this->typeFilter) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'video' => $query->where('mime_type', 'like', 'video/%'),
            'doc' => $query->where(function ($q) {
                $q->where('mime_type', 'like', 'application/%')
                    ->orWhere('mime_type', 'like', 'text/%');
            }),
            default => null,
        };

        match ($this->sortBy) {
            'oldest' => $query->orderBy('created_at'),
            'name' => $query->orderBy('name'),
            'size' => $query->orderByDesc('size_bytes'),
            default => $query->orderByDesc('created_at'),
        };

        return $query->limit(300)->get();
    }

    #[Computed]
    public function selected(): ?MediaAsset
    {
        if (! $this->selectedId) {
            return null;
        }

        return MediaAsset::query()->find($this->selectedId);
    }

    public function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' بايت';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' ك.ب';
        }

        return number_format($bytes / (1024 * 1024), 1).' م.ب';
    }
};
?>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/media-library.css') }}?v=5">
<link rel="stylesheet" href="{{ asset('css/media-picker.css') }}?v=1">
<link rel="stylesheet" href="{{ asset('css/site-file-manager.css') }}?v=1">
@endpush

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.media-library'),
    'shellBreadcrumb' => [
        ['label' => 'لوحة التحكم', 'href' => route('admin.dashboard')],
        ['label' => $this->isServerMode() ? 'الملفات' : 'مكتبة الوسائط'],
    ],
])

@if ($this->isServerMode())
    <livewire:admin.site-file-manager />
@else
<div class="ml" @if ($this->selected) data-has-panel="1" @endif>
    @if ($message)
        <div class="ml-flash ml-flash--{{ $messageKind }}" wire:key="flash-{{ md5($message) }}">
            {{ $message }}
            <button type="button" wire:click="$set('message', null)" aria-label="إغلاق">×</button>
        </div>
    @endif

    <div class="ml-stats">
        <div class="ml-stat">
            <span class="ml-stat__label">الملفات</span>
            <strong class="ml-stat__value">{{ number_format($this->stats['files']) }}</strong>
        </div>
        <div class="ml-stat">
            <span class="ml-stat__label">الصور</span>
            <strong class="ml-stat__value">{{ number_format($this->stats['images']) }}</strong>
        </div>
        <div class="ml-stat">
            <span class="ml-stat__label">التخزين</span>
            <strong class="ml-stat__value">{{ $this->humanBytes($this->stats['storage_bytes']) }}</strong>
        </div>
        <div class="ml-stat">
            <span class="ml-stat__label">روابط عامة</span>
            <strong class="ml-stat__value">{{ number_format($this->stats['public_links']) }}</strong>
        </div>
    </div>

    <nav class="ml-crumbs" aria-label="مسار المجلدات">
        <button type="button" class="ml-crumb" wire:click="goRoot">المكتبة</button>
        @foreach ($this->breadcrumbs as $crumb)
            <span class="ml-crumb__sep">›</span>
            <button type="button" class="ml-crumb" wire:click="openFolder({{ $crumb->id }})">{{ $crumb->name }}</button>
        @endforeach
    </nav>

    @canAdmin('media.manage')
        <label class="ml-drop" wire:loading.class="is-busy" for="ml-upload-input">
            <input
                id="ml-upload-input"
                type="file"
                multiple
                wire:model="uploads"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.webm,.mp3,.wav,.zip"
                class="ml-drop__input"
            >
            <span class="ml-drop__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0l-4 4m4-4l4 4"/><path d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            </span>
            <strong>أسقط الملفات هنا أو انقر للرفع</strong>
            <span>صور، PDF، فيديو، صوت، ومستندات أوفيس — تُحفظ في المجلد الحالي</span>
            <span class="ml-drop__hint" wire:loading wire:target="uploads">جاري الرفع…</span>
        </label>
    @endcanAdmin

    <div class="ml-toolbar">
        @canAdmin('media.manage')
            <button type="button" class="ml-create-folder-btn" wire:click="openCreateFolder">
                <span class="ml-create-folder-btn__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                        <path d="M12 11v6M9 14h6"/>
                    </svg>
                </span>
                <span class="ml-create-folder-btn__text">مجلد جديد</span>
            </button>
        @endcanAdmin
        <div class="ml-toolbar__search">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="بحث بالاسم أو النص البديل أو النوع…">
        </div>
        <select class="admin-control ml-toolbar__select" wire:model.live="typeFilter" aria-label="تصفية النوع">
            <option value="all">كل الأنواع</option>
            <option value="image">صور</option>
            <option value="video">فيديو</option>
            <option value="doc">مستندات</option>
        </select>
        <select class="admin-control ml-toolbar__select" wire:model.live="sortBy" aria-label="ترتيب">
            <option value="newest">الأحدث</option>
            <option value="oldest">الأقدم</option>
            <option value="name">الاسم</option>
            <option value="size">الحجم</option>
        </select>
        <div class="ml-toolbar__views" role="group" aria-label="طريقة العرض">
            <button type="button" class="@if ($viewMode === 'grid') is-active @endif" wire:click="$set('viewMode', 'grid')" title="شبكة">▦</button>
            <button type="button" class="@if ($viewMode === 'list') is-active @endif" wire:click="$set('viewMode', 'list')" title="قائمة">☰</button>
        </div>
        @canAdmin('media.manage')
            <button type="button" class="ml-toolbar__bulk @if ($bulkMode) is-active @endif" wire:click="toggleBulkMode">
                {{ $bulkMode ? 'إنهاء التحديد' : 'تحديد متعدد' }}
            </button>
        @endcanAdmin
        <span class="ml-toolbar__count">{{ $this->assets->count() }} ملف · {{ $this->folders->count() }} مجلد</span>
    </div>

    @if ($bulkMode)
        <div class="ml-bulkbar">
            <span>محدّد: {{ count($bulkIds) }}</span>
            <button type="button" wire:click="selectAllVisible">تحديد الظاهر</button>
            <button type="button" wire:click="clearBulk">مسح التحديد</button>
            @canAdmin('media.manage')
                <button type="button" class="is-danger" wire:click="deleteBulk" wire:confirm="حذف الملفات المحددة نهائياً؟" @disabled($bulkIds === [])>حذف المحدد</button>
            @endcanAdmin
        </div>
    @endif

    <div class="ml-layout">
        <div class="ml-main">
            @if ($this->folders->isNotEmpty())
                <section class="ml-section">
                    <h2 class="ml-section__title">المجلدات</h2>
                    <div class="ml-folders">
                        @foreach ($this->folders as $folder)
                            <article class="ml-folder" wire:key="folder-{{ $folder->id }}">
                                <div class="ml-folder__menu">
                                    <details>
                                        <summary aria-label="خيارات المجلد">⋮</summary>
                                        <div class="ml-menu">
                                            <button type="button" wire:click="openFolder({{ $folder->id }})">فتح</button>
                                            @canAdmin('media.manage')
                                                <button type="button" wire:click="startRenameFolder({{ $folder->id }})">إعادة تسمية</button>
                                                <button
                                                    type="button"
                                                    class="is-danger"
                                                    wire:click="deleteFolder({{ $folder->id }})"
                                                    wire:confirm="حذف المجلد «{{ $folder->name }}» وكل محتوياته نهائياً؟"
                                                >حذف المجلد</button>
                                            @endcanAdmin
                                        </div>
                                    </details>
                                </div>
                                @canAdmin('media.manage')
                                    <button
                                        type="button"
                                        class="ml-folder__delete"
                                        title="حذف المجلد"
                                        aria-label="حذف المجلد"
                                        wire:click.stop="deleteFolder({{ $folder->id }})"
                                        wire:confirm="حذف المجلد «{{ $folder->name }}» وكل محتوياته نهائياً؟"
                                    >
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                    </button>
                                @endcanAdmin
                                <button type="button" class="ml-folder__body" wire:dblclick="openFolder({{ $folder->id }})" wire:click="openFolder({{ $folder->id }})">
                                    <span class="ml-folder__icon" aria-hidden="true">
                                        <svg viewBox="0 0 64 52" width="44" height="36"><path fill="#fbbf24" d="M0 10c0-3.3 2.7-6 6-6h16l6 6h30c3.3 0 6 2.7 6 6v30c0 3.3-2.7 6-6 6H6c-3.3 0-6-2.7-6-6V10z"/><path fill="#f59e0b" d="M0 18h64v28c0 3.3-2.7 6-6 6H6c-3.3 0-6-2.7-6-6V18z"/></svg>
                                    </span>
                                    <strong>{{ $folder->name }}</strong>
                                    <span class="ml-folder__hint">انقر للفتح · {{ $folder->assets_count }} ملف</span>
                                </button>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="ml-section">
                <h2 class="ml-section__title">الملفات</h2>
                @if ($this->assets->isEmpty())
                    <div class="ml-empty-state">
                        <strong>لا توجد ملفات هنا</strong>
                        <p>ارفع صوراً أو مستندات عبر منطقة السحب أعلاه، أو أنشئ مجلداً لتنظيم المحتوى.</p>
                    </div>
                @else
                    <div class="ml-files @if ($viewMode === 'list') ml-files--list @endif">
                        @foreach ($this->assets as $asset)
                            <div
                                class="ml-file @if ($selectedId === $asset->id) is-selected @endif @if (in_array($asset->id, $bulkIds, true)) is-bulk @endif"
                                wire:key="asset-{{ $asset->id }}"
                            >
                                @if ($bulkMode)
                                    <label class="ml-file__check">
                                        <input type="checkbox" @checked(in_array($asset->id, $bulkIds, true)) wire:click.stop="toggleBulkId({{ $asset->id }})">
                                    </label>
                                @endif
                                @canAdmin('media.manage')
                                    <div class="ml-file__menu">
                                        <details>
                                            <summary aria-label="خيارات الملف">⋮</summary>
                                            <div class="ml-menu">
                                                <button type="button" wire:click="selectAsset({{ $asset->id }})">تفاصيل</button>
                                                <button type="button" wire:click="copyAssetUrl({{ $asset->id }})"
                                                    x-data
                                                    x-on:media-url-copied.window="navigator.clipboard?.writeText($event.detail.url)">نسخ الرابط</button>
                                                <button
                                                    type="button"
                                                    class="is-danger"
                                                    wire:click="deleteAssetById({{ $asset->id }})"
                                                    wire:confirm="حذف الملف «{{ $asset->name }}» نهائياً؟"
                                                >حذف الملف</button>
                                            </div>
                                        </details>
                                    </div>
                                    <button
                                        type="button"
                                        class="ml-file__delete"
                                        title="حذف الملف"
                                        aria-label="حذف الملف"
                                        wire:click.stop="deleteAssetById({{ $asset->id }})"
                                        wire:confirm="حذف الملف «{{ $asset->name }}» نهائياً؟"
                                    >
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                    </button>
                                @endcanAdmin
                                <button type="button" class="ml-file__hit" wire:click="selectAsset({{ $asset->id }})">
                                    <span class="ml-file__thumb">
                                        @if ($asset->isImage())
                                            <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->name }}" loading="lazy">
                                        @else
                                            <span class="ml-file__ext">{{ strtoupper($asset->extension ?: 'FILE') }}</span>
                                        @endif
                                    </span>
                                    <span class="ml-file__meta">
                                        <span class="ml-file__name" title="{{ $asset->name }}">{{ $asset->name }}</span>
                                        @if ($viewMode === 'list')
                                            <span class="ml-file__sub">{{ $asset->humanSize() }}@if($asset->width) · {{ $asset->width }}×{{ $asset->height }}@endif · {{ $asset->created_at?->diffForHumans() }}</span>
                                        @endif
                                    </span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        @if ($this->selected)
            @php($selected = $this->selected)
            <aside class="ml-panel" wire:key="panel-{{ $selected->id }}">
                <header class="ml-panel__head">
                    <div>
                        <p class="ml-panel__eyebrow">الملف المختار</p>
                        <h3>{{ $selected->name }}</h3>
                    </div>
                    <button type="button" class="ml-panel__close" wire:click="clearSelection" aria-label="إغلاق">×</button>
                </header>

                <div class="ml-panel__preview">
                    @if ($selected->isImage())
                        <img src="{{ $selected->url() }}?v={{ $selected->updated_at?->timestamp }}" alt="{{ $selected->alt_text ?: $selected->name }}">
                    @else
                        <div class="ml-panel__file-badge">{{ strtoupper($selected->extension ?: 'FILE') }}</div>
                    @endif
                </div>

                <div class="ml-panel__links">
                    <a href="{{ $selected->url() }}" target="_blank" rel="noopener">فتح في تبويب جديد</a>
                    <button type="button" class="ml-copy-btn" wire:click="copyAssetUrl({{ $selected->id }})"
                        x-data
                        x-on:media-url-copied.window="navigator.clipboard?.writeText($event.detail.url)">
                        نسخ الرابط
                    </button>
                </div>

                <div class="ml-panel__block">
                    <label class="ml-label">مسار النموذج</label>
                    <input type="text" class="admin-control" readonly dir="ltr" value="{{ $selected->formValue() }}">
                </div>

                <dl class="ml-meta">
                    <div><dt>الحجم</dt><dd>{{ $selected->humanSize() }}</dd></div>
                    <div><dt>النوع</dt><dd>{{ $selected->mime_type ?: '—' }}</dd></div>
                    @if ($selected->width)
                        <div><dt>العرض</dt><dd>{{ $selected->width }} px</dd></div>
                        <div><dt>الارتفاع</dt><dd>{{ $selected->height }} px</dd></div>
                    @endif
                </dl>

                @canAdmin('media.manage')
                    <div class="ml-panel__block">
                        <label class="ml-label" for="ml-edit-name">اسم الملف</label>
                        <div class="ml-inline">
                            <input id="ml-edit-name" type="text" class="admin-control" wire:model="editName">
                            <button type="button" class="admin-btn-primary" wire:click="applyRename">تطبيق</button>
                        </div>
                    </div>

                    @if ($selected->isImage())
                        <div class="ml-panel__block">
                            <p class="ml-label">تحسين الصورة</p>
                            <div class="ml-badges">
                                <span>{{ $selected->width }}×{{ $selected->height }}</span>
                                <span>{{ $selected->aspectRatioLabel() }}</span>
                                <span>{{ $selected->humanSize() }}</span>
                            </div>
                            <div class="ml-presets">
                                <button type="button" wire:click="applyPreset('light')">ضغط خفيف</button>
                                <button type="button" wire:click="applyPreset('strong')">ضغط قوي</button>
                                <button type="button" wire:click="applyPreset('hq')">جودة أعلى</button>
                                <button type="button" wire:click="applyPreset('webp')">WebP</button>
                            </div>
                            <label class="ml-label">المقياس %</label>
                            <input type="range" min="10" max="100" wire:model.live="optScale">
                            <div class="ml-dims">
                                <div>
                                    <label class="ml-label">أقصى عرض</label>
                                    <input type="number" class="admin-control" wire:model="optMaxWidth" min="1" placeholder="px">
                                </div>
                                <div>
                                    <label class="ml-label">أقصى ارتفاع</label>
                                    <input type="number" class="admin-control" wire:model="optMaxHeight" min="1" placeholder="px">
                                </div>
                            </div>
                            <label class="ml-label">جودة JPEG / WebP ({{ $optQuality }})</label>
                            <input type="range" min="50" max="100" wire:model.live="optQuality">
                            <p class="ml-hint">أقل = ملف أصغر · أعلى = أوضح (50–100)</p>
                            <label class="ml-label" for="ml-format">صيغة الإخراج</label>
                            <select id="ml-format" class="admin-control" wire:model="optFormat">
                                <option value="keep">الإبقاء على الصيغة الحالية</option>
                                <option value="jpg">JPEG</option>
                                <option value="png">PNG</option>
                                <option value="webp">WebP</option>
                            </select>
                            <button type="button" class="admin-btn-primary ml-btn-block" wire:click="applyOptimize" wire:loading.attr="disabled">
                                تطبيق التحسين والحفظ
                            </button>
                        </div>
                    @endif

                    <div class="ml-panel__block">
                        <p class="ml-label">مشاركة خارجية</p>
                        @if ($selected->publicShareUrl())
                            <div class="ml-inline">
                                <input type="text" class="admin-control" readonly value="{{ $selected->publicShareUrl() }}">
                                <a class="admin-btn-primary" href="{{ $selected->publicShareUrl() }}" target="_blank" rel="noopener">فتح</a>
                            </div>
                            <button type="button" class="ml-btn-ghost" wire:click="revokePublicLink">إلغاء الرابط</button>
                        @else
                            <button type="button" class="admin-btn-primary ml-btn-block" wire:click="enablePublicLink">إنشاء رابط عام</button>
                        @endif
                    </div>

                    <div class="ml-panel__block">
                        <label class="ml-label" for="ml-alt">النص البديل</label>
                        <textarea id="ml-alt" class="admin-control" rows="3" wire:model="altText" placeholder="لقرّاء الشاشة وعند تعذّر تحميل الصورة"></textarea>
                        <button type="button" class="admin-btn-primary ml-btn-block" wire:click="saveAlt">حفظ النص البديل</button>
                    </div>

                    <button type="button" class="ml-btn-danger" wire:click="deleteAsset" wire:confirm="حذف هذا الملف نهائياً؟">حذف الملف</button>
                @endcanAdmin
            </aside>
        @endif
    </div>
</div>

@if ($renamingFolderId)
    <div class="ml-modal" wire:click.self="cancelRenameFolder">
        <div class="ml-modal__card">
            <h3>إعادة تسمية المجلد</h3>
            <input type="text" class="admin-control" wire:model="renameFolderName" wire:keydown.enter="applyRenameFolder">
            <div class="ml-modal__actions">
                <button type="button" class="ml-btn-ghost" wire:click="cancelRenameFolder">إلغاء</button>
                <button type="button" class="admin-btn-primary" wire:click="applyRenameFolder">حفظ</button>
            </div>
        </div>
    </div>
@endif

@if ($showCreateFolder)
    <div class="ml-modal" wire:click.self="cancelCreateFolder">
        <div class="ml-modal__card ml-create-modal">
            <div class="ml-create-modal__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    <path d="M12 11v6M9 14h6"/>
                </svg>
            </div>
            <h3>إنشاء مجلد جديد</h3>
            <p class="ml-create-modal__hint">
                @if ($this->currentFolder)
                    داخل: {{ $this->currentFolder->name }}
                @else
                    في جذر المكتبة
                @endif
            </p>
            <label class="ml-label" for="ml-new-folder-name">اسم المجلد</label>
            <input
                id="ml-new-folder-name"
                type="text"
                class="admin-control"
                wire:model="folderName"
                wire:keydown.enter="createFolder"
                placeholder="مثال: الشعارات، البوسترات…"
                autofocus
            >
            <div class="ml-modal__actions">
                <button type="button" class="ml-btn-ghost" wire:click="cancelCreateFolder">إلغاء</button>
                <button type="button" class="admin-btn-primary ml-create-modal__submit" wire:click="createFolder">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    إنشاء المجلد
                </button>
            </div>
        </div>
    </div>
@endif
@endif

@include('partials.admin.shell-end')
