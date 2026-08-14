<?php

use App\Services\SiteFileManagerService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Url(as: 'path', except: '')]
    public string $path = '';

    public string $search = '';

    public string $newFolderName = '';

    public string $newFileName = '';

    public bool $showNewFolder = false;

    public bool $showNewFile = false;

    public string $renameFrom = '';

    public string $renameTo = '';

    public bool $showRename = false;

    public string $editPath = '';

    public string $editContents = '';

    public bool $showEditor = false;

    /** @var array<int, mixed> */
    public array $uploads = [];

    public ?string $message = null;

    public string $messageKind = 'success';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('media.manage'), 403);
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function updatedUploads(SiteFileManagerService $files): void
    {
        $this->uploadFiles($files);
    }

    public function openPath(string $path = ''): void
    {
        $this->path = trim(str_replace('\\', '/', $path), '/');
        if ($this->path === '.' || $this->path === '..') {
            $this->path = '';
        }
        $this->search = '';
        $this->closeModals();
    }

    public function goUp(): void
    {
        if ($this->path === '') {
            return;
        }

        $parent = dirname(str_replace('\\', '/', $this->path));
        $this->openPath(($parent === '.' || $parent === '/') ? '' : $parent);
    }

    public function openNewFolder(): void
    {
        $this->showNewFolder = true;
        $this->newFolderName = '';
    }

    public function createFolder(SiteFileManagerService $files): void
    {
        try {
            $files->createDirectory($this->path ?: null, $this->newFolderName);
            $this->showNewFolder = false;
            $this->newFolderName = '';
            $this->flash('تم إنشاء المجلد.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function openNewFile(): void
    {
        $this->showNewFile = true;
        $this->newFileName = '';
    }

    public function createFile(SiteFileManagerService $files): void
    {
        try {
            $rel = $files->createFile($this->path ?: null, $this->newFileName, '');
            $this->showNewFile = false;
            $this->newFileName = '';
            $this->flash('تم إنشاء الملف.');
            $this->openEditor($rel, $files);
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function uploadFiles(SiteFileManagerService $files): void
    {
        if ($this->uploads === []) {
            return;
        }

        $this->validate([
            'uploads' => ['required', 'array', 'min:1'],
            'uploads.*' => ['file', 'max:102400'],
        ], [
            'uploads.*.max' => 'حجم الملف يتجاوز 100 م.ب.',
        ]);

        $count = 0;
        try {
            foreach ($this->uploads as $file) {
                if ($file) {
                    $files->upload($this->path ?: null, $file);
                    $count++;
                }
            }
            $this->uploads = [];
            $this->flash($count === 1 ? 'تم رفع ملف واحد.' : "تم رفع {$count} ملفات.");
        } catch (ValidationException $e) {
            $this->uploads = [];
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function openRename(string $relative): void
    {
        $this->renameFrom = $relative;
        $this->renameTo = basename(str_replace('\\', '/', $relative));
        $this->showRename = true;
    }

    public function applyRename(SiteFileManagerService $files): void
    {
        try {
            $files->rename($this->renameFrom, $this->renameTo);
            $this->showRename = false;
            $this->renameFrom = '';
            $this->renameTo = '';
            $this->flash('تم إعادة التسمية.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function deleteItem(string $relative, SiteFileManagerService $files): void
    {
        try {
            $files->delete($relative);
            if ($this->editPath === $relative) {
                $this->closeEditor();
            }
            $this->flash('تم الحذف.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function openEditor(string $relative, SiteFileManagerService $files): void
    {
        try {
            $this->editContents = $files->read($relative);
            $this->editPath = $relative;
            $this->showEditor = true;
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function saveEditor(SiteFileManagerService $files): void
    {
        try {
            $files->write($this->editPath, $this->editContents);
            $this->flash('تم حفظ الملف.');
        } catch (ValidationException $e) {
            $this->flash($e->validator->errors()->first(), 'error');
        }
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->editPath = '';
        $this->editContents = '';
    }

    public function closeModals(): void
    {
        $this->showNewFolder = false;
        $this->showNewFile = false;
        $this->showRename = false;
    }

    protected function flash(string $message, string $kind = 'success'): void
    {
        $this->message = $message;
        $this->messageKind = $kind;
    }

    #[Computed]
    public function items(): array
    {
        return app(SiteFileManagerService::class)->list($this->path ?: null, $this->search);
    }

    #[Computed]
    public function crumbs(): array
    {
        return app(SiteFileManagerService::class)->breadcrumbs($this->path ?: null);
    }
};
?>

<div class="sfm">
    @if ($message)
        <div class="ml-flash ml-flash--{{ $messageKind }}">
            {{ $message }}
            <button type="button" wire:click="$set('message', null)" aria-label="إغلاق">×</button>
        </div>
    @endif

    <div class="sfm-toolbar">
        <a class="sfm-btn" href="{{ route('admin.media-library') }}">رجوع</a>
        <button type="button" class="ml-create-folder-btn" wire:click="openNewFolder">
            <span class="ml-create-folder-btn__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/><path d="M12 11v6M9 14h6"/></svg>
            </span>
            <span>مجلد جديد</span>
        </button>
        <button type="button" class="sfm-btn" wire:click="openNewFile">+ ملف جديد</button>
        <label class="sfm-upload">
            <input type="file" multiple wire:model="uploads" class="sfm-upload__input">
            <span wire:loading.remove wire:target="uploads">رفع</span>
            <span wire:loading wire:target="uploads">…</span>
        </label>
        <div class="ml-toolbar__search sfm-search">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
            <input type="search" class="admin-control" wire:model.live.debounce.250ms="search" placeholder="بحث…">
        </div>
    </div>

    <nav class="sfm-crumbs" aria-label="مسار المجلد">
        @foreach ($this->crumbs as $i => $crumb)
            @if ($i > 0)<span>/</span>@endif
            <button type="button" wire:click="openPath(@js($crumb['path']))">{{ $crumb['label'] }}</button>
        @endforeach
    </nav>

    <div class="sfm-table-wrap">
        <table class="sfm-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>الحجم</th>
                    <th>آخر تعديل</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @if ($path !== '')
                    <tr>
                        <td colspan="5">
                            <button type="button" class="sfm-up" wire:click="goUp">
                                ← المجلد الأعلى
                            </button>
                        </td>
                    </tr>
                @endif

                @forelse ($this->items as $item)
                    <tr wire:key="sfm-{{ md5($item['path']) }}">
                        <td>
                            @if ($item['type'] === 'dir')
                                <button type="button" class="sfm-name is-dir" wire:click="openPath(@js($item['path']))">
                                    <span aria-hidden="true">📁</span> {{ $item['name'] }}
                                </button>
                            @else
                                <span class="sfm-name">
                                    <span aria-hidden="true">📄</span> {{ $item['name'] }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $item['type'] === 'dir' ? 'مجلد' : strtoupper($item['ext'] ?: 'FILE') }}</td>
                        <td>{{ $item['type'] === 'dir' ? '—' : app(\App\Services\SiteFileManagerService::class)->humanBytes($item['size']) }}</td>
                        <td>{{ $item['mtime'] ? date('Y-m-d H:i', $item['mtime']) : '—' }}</td>
                        <td class="sfm-actions">
                            @if ($item['type'] === 'dir')
                                <button type="button" wire:click="openPath(@js($item['path']))">فتح</button>
                            @elseif ($item['editable'])
                                <button type="button" wire:click="openEditor(@js($item['path']))">تعديل</button>
                            @endif
                            <button type="button" wire:click="openRename(@js($item['path']))">إعادة تسمية</button>
                            <button
                                type="button"
                                class="is-danger"
                                wire:click="deleteItem(@js($item['path']))"
                                wire:confirm="حذف «{{ $item['name'] }}» نهائياً؟"
                            >حذف</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="sfm-empty">—</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showEditor)
        <div class="ml-modal" wire:click.self="closeEditor">
            <div class="ml-modal__card sfm-editor">
                <div class="sfm-editor__head">
                    <div>
                        <strong dir="ltr">{{ $editPath }}</strong>
                    </div>
                    <button type="button" class="ml-btn-ghost" wire:click="closeEditor">إغلاق</button>
                </div>
                <textarea class="sfm-editor__area" dir="ltr" rows="22" wire:model="editContents" spellcheck="false"></textarea>
                <div class="ml-modal__actions">
                    <button type="button" class="ml-btn-ghost" wire:click="closeEditor">إلغاء</button>
                    <button type="button" class="admin-btn-primary" wire:click="saveEditor">حفظ</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showNewFolder)
        <div class="ml-modal" wire:click.self="closeModals">
            <div class="ml-modal__card">
                <h3>مجلد جديد</h3>
                <input type="text" class="admin-control" wire:model="newFolderName" wire:keydown.enter="createFolder" placeholder="اسم المجلد">
                <div class="ml-modal__actions">
                    <button type="button" class="ml-btn-ghost" wire:click="closeModals">إلغاء</button>
                    <button type="button" class="admin-btn-primary" wire:click="createFolder">إنشاء</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showNewFile)
        <div class="ml-modal" wire:click.self="closeModals">
            <div class="ml-modal__card">
                <h3>ملف جديد</h3>
                <input type="text" class="admin-control" wire:model="newFileName" wire:keydown.enter="createFile" placeholder="example.txt" dir="ltr">
                <div class="ml-modal__actions">
                    <button type="button" class="ml-btn-ghost" wire:click="closeModals">إلغاء</button>
                    <button type="button" class="admin-btn-primary" wire:click="createFile">إنشاء</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showRename)
        <div class="ml-modal" wire:click.self="closeModals">
            <div class="ml-modal__card">
                <h3>إعادة تسمية</h3>
                <input type="text" class="admin-control" wire:model="renameTo" wire:keydown.enter="applyRename" dir="ltr">
                <div class="ml-modal__actions">
                    <button type="button" class="ml-btn-ghost" wire:click="closeModals">إلغاء</button>
                    <button type="button" class="admin-btn-primary" wire:click="applyRename">حفظ</button>
                </div>
            </div>
        </div>
    @endif
</div>
