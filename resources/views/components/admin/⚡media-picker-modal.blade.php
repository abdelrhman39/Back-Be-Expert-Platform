<?php

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Services\MediaLibraryService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public bool $open = false;

    public string $target = '';

    /** image|file|any */
    public string $accept = 'image';

    public string $title = 'اختيار من مكتبة الوسائط';

    public ?int $folderId = null;

    public string $search = '';

    public string $typeFilter = 'all';

    public ?int $highlightId = null;

    /** @var array<int, mixed> */
    public array $uploads = [];

    public function mount(): void
    {
        // Available on all authenticated admin pages; mutations check permission.
    }

    #[On('open-media-picker')]
    public function openPicker(string $target, string $accept = 'image', ?string $title = null): void
    {
        abort_unless(auth()->user()?->canAdmin('media.view'), 403);

        $this->resetPickerState();
        $this->open = true;
        $this->target = $target;
        $this->accept = in_array($accept, ['image', 'file', 'any'], true) ? $accept : 'image';
        $this->title = $title ?: ($this->accept === 'image' ? 'اختيار صورة من المكتبة' : 'اختيار ملف من المكتبة');
        $this->typeFilter = $this->accept === 'image' ? 'image' : 'all';
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetPickerState();
    }

    public function openFolder(?int $id): void
    {
        if ($id) {
            MediaFolder::query()->findOrFail($id);
        }
        $this->folderId = $id;
        $this->highlightId = null;
        $this->search = '';
    }

    public function goUp(): void
    {
        if (! $this->folderId) {
            return;
        }
        $current = MediaFolder::query()->find($this->folderId);
        $this->folderId = $current?->parent_id;
        $this->highlightId = null;
    }

    public function updatedUploads(): void
    {
        $this->uploadAndSelect();
    }

    public function uploadAndSelect(): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);

        if ($this->uploads === []) {
            return;
        }

        $rules = [
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['file', 'max:51200'],
        ];

        if ($this->accept === 'image') {
            $rules['uploads.*'] = ['image', 'max:51200'];
        }

        $this->validate($rules, [
            'uploads.*.image' => 'يُسمح بالصور فقط لهذا الحقل.',
            'uploads.*.max' => 'حجم الملف يتجاوز 50 م.ب.',
        ]);

        $service = app(MediaLibraryService::class);
        $last = null;
        foreach ($this->uploads as $file) {
            if ($file) {
                $last = $service->upload($file, $this->folderId, auth()->user());
            }
        }

        $this->uploads = [];

        if ($last) {
            $this->confirmSelection($last->id);
        }
    }

    public function confirmSelection(int $id): void
    {
        abort_unless(auth()->user()?->canAdmin('media.view'), 403);

        $asset = MediaAsset::query()->findOrFail($id);

        if ($this->accept === 'image' && ! $asset->isImage()) {
            $this->highlightId = $id;

            return;
        }

        $url = $asset->formValue();

        $this->dispatch('media-picker-selected', target: $this->target, url: $url, id: $asset->id, name: $asset->name);
        $this->close();
    }

    public function highlight(int $id): void
    {
        $this->highlightId = $id;
    }

    protected function resetPickerState(): void
    {
        $this->folderId = null;
        $this->search = '';
        $this->highlightId = null;
        $this->uploads = [];
        $this->typeFilter = 'all';
    }

    #[Computed]
    public function currentFolder(): ?MediaFolder
    {
        return $this->folderId ? MediaFolder::query()->find($this->folderId) : null;
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
        if (filled($this->search)) {
            return collect();
        }

        return MediaFolder::query()
            ->where('parent_id', $this->folderId)
            ->withCount('assets')
            ->orderBy('name')
            ->get();
    }

    /** @return Collection<int, MediaAsset> */
    #[Computed]
    public function assets(): Collection
    {
        $query = MediaAsset::query()->orderByDesc('created_at');

        if (filled($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('original_name', 'like', $term)
                    ->orWhere('alt_text', 'like', $term);
            });
        } else {
            $query->where('folder_id', $this->folderId);
        }

        $filter = $this->accept === 'image' ? 'image' : $this->typeFilter;
        if ($filter === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($filter === 'video') {
            $query->where('mime_type', 'like', 'video/%');
        } elseif ($filter === 'doc') {
            $query->where(function ($q) {
                $q->where('mime_type', 'like', 'application/%')
                    ->orWhere('mime_type', 'like', 'text/%');
            });
        }

        return $query->limit(120)->get();
    }

    #[Computed]
    public function highlighted(): ?MediaAsset
    {
        return $this->highlightId ? MediaAsset::query()->find($this->highlightId) : null;
    }
};
?>

<div class="mp-host" @if ($open) data-open="1" @endif>
@if ($open)
    <div class="mp" wire:key="media-picker-open" role="dialog" aria-modal="true" aria-label="{{ $title }}">
        <div class="mp__backdrop" wire:click="close"></div>
        <div class="mp__dialog">
            <header class="mp__head">
                <div>
                    <p class="mp__eyebrow">مكتبة الوسائط</p>
                    <h2>{{ $title }}</h2>
                </div>
                <button type="button" class="mp__close" wire:click="close" aria-label="إغلاق">×</button>
            </header>

            <div class="mp__toolbar">
                <nav class="mp__crumbs" aria-label="المجلدات">
                    <button type="button" wire:click="openFolder(null)">المكتبة</button>
                    @foreach ($this->breadcrumbs as $crumb)
                        <span>/</span>
                        <button type="button" wire:click="openFolder({{ $crumb->id }})">{{ $crumb->name }}</button>
                    @endforeach
                    @if ($folderId)
                        <button type="button" class="mp__up" wire:click="goUp">رجوع</button>
                    @endif
                </nav>
                <div class="mp__search">
                    <input type="search" class="admin-control" wire:model.live.debounce.250ms="search" placeholder="بحث…">
                </div>
                @if ($accept !== 'image')
                    <select class="admin-control mp__filter" wire:model.live="typeFilter">
                        <option value="all">الكل</option>
                        <option value="image">صور</option>
                        <option value="video">فيديو</option>
                        <option value="doc">مستندات</option>
                    </select>
                @endif
            </div>

            @canAdmin('media.manage')
                <label class="mp__drop" wire:loading.class="is-busy" for="mp-upload">
                    <input id="mp-upload" type="file" class="mp__drop-input" wire:model="uploads"
                        @if ($accept === 'image') accept="image/*" @else accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.mp4,.webm,.zip" @endif
                        multiple>
                    <strong>رفع ملف جديد واختياره مباشرة</strong>
                    <span>يُحفظ في المجلد الحالي ثم يُطبَّق على الحقل تلقائياً</span>
                    <span wire:loading wire:target="uploads">جاري الرفع…</span>
                </label>
                @error('uploads.*') <p class="mp__error">{{ $message }}</p> @enderror
            @endcanAdmin

            <div class="mp__body">
                <div class="mp__grid-wrap">
                    @if ($this->folders->isNotEmpty())
                        <div class="mp__folders">
                            @foreach ($this->folders as $folder)
                                <button type="button" class="mp__folder" wire:click="openFolder({{ $folder->id }})">
                                    <span class="mp__folder-icon" aria-hidden="true">📁</span>
                                    <span>{{ $folder->name }}</span>
                                    <small>{{ $folder->assets_count }}</small>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($this->assets->isEmpty())
                        <p class="mp__empty">لا توجد ملفات مطابقة. ارفع ملفاً جديداً أو غيّر المجلد.</p>
                    @else
                        <div class="mp__grid">
                            @foreach ($this->assets as $asset)
                                <button
                                    type="button"
                                    class="mp__item @if ($highlightId === $asset->id) is-active @endif"
                                    wire:key="mp-asset-{{ $asset->id }}"
                                    wire:click="highlight({{ $asset->id }})"
                                    wire:dblclick="confirmSelection({{ $asset->id }})"
                                    title="{{ $asset->name }}"
                                >
                                    <span class="mp__thumb">
                                        @if ($asset->isImage())
                                            <img src="{{ $asset->url() }}" alt="" loading="lazy">
                                        @else
                                            <span class="mp__ext">{{ strtoupper($asset->extension ?: 'FILE') }}</span>
                                        @endif
                                    </span>
                                    <span class="mp__name">{{ $asset->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="mp__side">
                    @if ($this->highlighted)
                        @php($sel = $this->highlighted)
                        <div class="mp__preview">
                            @if ($sel->isImage())
                                <img src="{{ $sel->url() }}" alt="">
                            @else
                                <div class="mp__preview-file">{{ strtoupper($sel->extension ?: 'FILE') }}</div>
                            @endif
                        </div>
                        <p class="mp__side-name">{{ $sel->name }}</p>
                        <dl class="mp__meta">
                            <div><dt>الحجم</dt><dd>{{ $sel->humanSize() }}</dd></div>
                            @if ($sel->width)
                                <div><dt>الأبعاد</dt><dd>{{ $sel->width }}×{{ $sel->height }}</dd></div>
                            @endif
                            <div><dt>النوع</dt><dd>{{ $sel->mime_type }}</dd></div>
                        </dl>
                        <button type="button" class="admin-btn-primary mp__confirm" wire:click="confirmSelection({{ $sel->id }})">
                            استخدام هذا الملف
                        </button>
                        <p class="mp__hint">نقرة مزدوجة على أي ملف للاختيار السريع</p>
                    @else
                        <div class="mp__side-empty">
                            <p>اختر ملفاً من الشبكة للمعاينة ثم اضغط «استخدام هذا الملف».</p>
                        </div>
                    @endif
                </aside>
            </div>

            <footer class="mp__foot">
                <a href="{{ route('admin.media-library') }}" target="_blank" rel="noopener" class="mp__manage">إدارة المكتبة ↗</a>
                <button type="button" class="mp__cancel" wire:click="close">إلغاء</button>
            </footer>
        </div>
    </div>
@endif
</div>
