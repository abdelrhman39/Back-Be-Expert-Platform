<?php

use App\Models\AcademicBatch;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicSchedule;
use App\Models\AcademicScheduleDocument;
use App\Models\AcademicSection;
use App\Models\AcademicStaff;
use App\Support\AcademicBatchOptions;
use App\Support\AcademicScheduleOptions;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الجداول الدراسية | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    #[Url]
    public string $tab = 'documents';

    #[Url]
    public string $semesterKey = '';

    #[Url]
    public string $batchId = '';

    #[Url]
    public string $levelId = '';

    #[Url]
    public string $period = '';

    /** @var array<int, array<string, mixed>> */
    public array $scheduleRows = [];

    public bool $tableLoaded = false;

    public ?int $editingDocumentId = null;

    public string $docProgramId = '';

    public string $docBatchId = '';

    public string $docSemesterKey = '';

    public string $docTitle = '';

    public string $docDescription = '';

    public bool $docIsPublished = true;

    public bool $docIsFeatured = true;

    public string $docSortOrder = '100';

    public $docFile = null;

    public string $docFilterProgramId = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.view'), 403);

        if (! in_array($this->tab, ['documents', 'weekly'], true)) {
            $this->tab = 'documents';
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['documents', 'weekly'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function updatedSemesterKey(): void
    {
        $this->resetTable();
    }

    public function updatedBatchId(): void
    {
        $this->resetTable();
    }

    public function updatedLevelId(): void
    {
        $this->resetTable();
    }

    public function updatedPeriod(): void
    {
        $this->resetTable();
    }

    public function updatedDocProgramId(): void
    {
        $this->docBatchId = '';
        unset($this->docBatches);
    }

    public function updatedDocFilterProgramId(): void
    {
        unset($this->documents);
    }

    public function loadTable(): void
    {
        $this->validate([
            'semesterKey' => ['required', 'string'],
            'batchId' => ['required', 'exists:academic_batches,id'],
            'levelId' => ['required', 'exists:academic_levels,id'],
            'period' => ['required', 'in:morning,evening'],
        ], [], [
            'semesterKey' => 'الفصل الدراسي',
            'batchId' => 'الدفعة',
            'levelId' => 'المستوى',
            'period' => 'الفترة',
        ]);

        $sections = AcademicSection::query()
            ->with(['course', 'schedule'])
            ->where('semester_key', $this->semesterKey)
            ->where('batch_id', (int) $this->batchId)
            ->where('level_id', (int) $this->levelId)
            ->where('period', $this->period)
            ->orderBy('code')
            ->get();

        $this->scheduleRows = $sections->map(function (AcademicSection $section) {
            $schedule = $section->schedule;

            return [
                'section_id' => $section->id,
                'section_code' => $section->code,
                'section_name' => $section->name,
                'course_name' => $section->course?->name_ar ?? $section->subtitle ?? '—',
                'staff_id' => $schedule?->staff_id ?? '',
                'trainer_name' => $schedule?->trainer_name ?? $section->supervisor ?? '',
                'day_of_week' => $schedule?->day_of_week ?? '',
                'time_start' => $schedule?->time_start ? substr((string) $schedule->time_start, 0, 5) : '',
                'time_end' => $schedule?->time_end ? substr((string) $schedule->time_end, 0, 5) : '',
            ];
        })->values()->all();

        $this->tableLoaded = true;
    }

    public function saveSchedules(): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        if (! $this->tableLoaded) {
            $this->loadTable();
        }

        foreach ($this->scheduleRows as $row) {
            $staffId = $row['staff_id'] ?: null;
            $trainerName = $row['trainer_name'] ?: null;

            if ($staffId) {
                $trainerName = AcademicStaff::query()->find($staffId)?->name_ar ?? $trainerName;
            }

            AcademicSchedule::query()->updateOrCreate(
                ['section_id' => $row['section_id']],
                [
                    'semester_key' => $this->semesterKey,
                    'batch_id' => (int) $this->batchId,
                    'level_id' => (int) $this->levelId,
                    'period' => $this->period,
                    'staff_id' => $staffId,
                    'trainer_name' => $trainerName,
                    'day_of_week' => $row['day_of_week'] ?: null,
                    'time_start' => $row['time_start'] ?: null,
                    'time_end' => $row['time_end'] ?: null,
                ]
            );
        }

        session()->flash('admin_message', 'تم حفظ جدول الشعب بنجاح ('.count($this->scheduleRows).' شعبة).');
    }

    public function updatedScheduleRows($value, $key): void
    {
        if (str_ends_with($key, '.staff_id') && $value) {
            $index = (int) explode('.', $key)[0];
            $staff = AcademicStaff::query()->find($value);
            if ($staff && isset($this->scheduleRows[$index])) {
                $this->scheduleRows[$index]['trainer_name'] = $staff->name_ar;
            }
        }
    }

    public function saveDocument(): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        $rules = [
            'docProgramId' => ['required', 'exists:academic_programs,id'],
            'docBatchId' => ['nullable', 'exists:academic_batches,id'],
            'docSemesterKey' => ['nullable', 'string', 'max:32'],
            'docTitle' => ['required', 'string', 'max:255'],
            'docDescription' => ['nullable', 'string', 'max:2000'],
            'docIsPublished' => ['boolean'],
            'docIsFeatured' => ['boolean'],
            'docSortOrder' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'docFile' => [$this->editingDocumentId ? 'nullable' : 'required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ];

        $this->validate($rules, [], [
            'docProgramId' => 'البرنامج / الدبلوم',
            'docBatchId' => 'الدفعة',
            'docSemesterKey' => 'الفصل',
            'docTitle' => 'عنوان الجدول',
            'docDescription' => 'الوصف',
            'docFile' => 'ملف الجدول',
            'docSortOrder' => 'الترتيب',
        ]);

        if ($this->docBatchId) {
            $batchProgramId = AcademicBatch::query()->whereKey($this->docBatchId)->value('program_id');
            if ((int) $batchProgramId !== (int) $this->docProgramId) {
                $this->addError('docBatchId', 'الدفعة المختارة لا تتبع هذا البرنامج.');

                return;
            }
        }

        $payload = [
            'program_id' => (int) $this->docProgramId,
            'batch_id' => $this->docBatchId !== '' ? (int) $this->docBatchId : null,
            'semester_key' => $this->docSemesterKey !== '' ? $this->docSemesterKey : null,
            'title' => trim($this->docTitle),
            'description' => trim($this->docDescription) !== '' ? trim($this->docDescription) : null,
            'is_published' => $this->docIsPublished,
            'is_featured' => $this->docIsFeatured,
            'sort_order' => (int) ($this->docSortOrder !== '' ? $this->docSortOrder : 100),
        ];

        if ($this->editingDocumentId) {
            $document = AcademicScheduleDocument::query()->findOrFail($this->editingDocumentId);

            if ($this->docFile) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $path = $this->docFile->store('academic-schedules', 'public');
                $payload['file_path'] = $path;
                $payload['original_name'] = $this->docFile->getClientOriginalName();
                $payload['mime_type'] = $this->docFile->getMimeType();
                $payload['file_size'] = (int) $this->docFile->getSize();
            }

            $document->update($payload);
            session()->flash('admin_message', 'تم تحديث جدول الدبلوم بنجاح.');
        } else {
            $path = $this->docFile->store('academic-schedules', 'public');

            AcademicScheduleDocument::query()->create([
                ...$payload,
                'file_path' => $path,
                'original_name' => $this->docFile->getClientOriginalName(),
                'mime_type' => $this->docFile->getMimeType(),
                'file_size' => (int) $this->docFile->getSize(),
                'uploaded_by' => auth()->id(),
            ]);

            session()->flash('admin_message', 'تم رفع جدول الدبلوم بنجاح. سيظهر للطلاب بعد النشر.');
        }

        $this->resetDocumentForm();
        unset($this->documents);
    }

    public function editDocument(int $documentId): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        $document = AcademicScheduleDocument::query()->findOrFail($documentId);

        $this->editingDocumentId = $document->id;
        $this->docProgramId = (string) $document->program_id;
        $this->docBatchId = $document->batch_id ? (string) $document->batch_id : '';
        $this->docSemesterKey = (string) ($document->semester_key ?? '');
        $this->docTitle = $document->title;
        $this->docDescription = (string) ($document->description ?? '');
        $this->docIsPublished = (bool) $document->is_published;
        $this->docIsFeatured = (bool) $document->is_featured;
        $this->docSortOrder = (string) $document->sort_order;
        $this->docFile = null;
        $this->tab = 'documents';
        unset($this->docBatches);
    }

    public function cancelDocumentEdit(): void
    {
        $this->resetDocumentForm();
    }

    public function deleteDocument(int $documentId): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        $document = AcademicScheduleDocument::query()->findOrFail($documentId);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        if ($this->editingDocumentId === $documentId) {
            $this->resetDocumentForm();
        }

        unset($this->documents);
        session()->flash('admin_message', 'تم حذف ملف الجدول.');
    }

    public function toggleDocumentPublished(int $documentId): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        $document = AcademicScheduleDocument::query()->findOrFail($documentId);
        $document->update(['is_published' => ! $document->is_published]);
        unset($this->documents);
    }

    public function toggleDocumentFeatured(int $documentId): void
    {
        abort_unless(auth()->user()?->canAdmin('schedules.manage'), 403);

        $document = AcademicScheduleDocument::query()->findOrFail($documentId);
        $document->update(['is_featured' => ! $document->is_featured]);
        unset($this->documents);
    }

    protected function resetDocumentForm(): void
    {
        $this->editingDocumentId = null;
        $this->docProgramId = '';
        $this->docBatchId = '';
        $this->docSemesterKey = '';
        $this->docTitle = '';
        $this->docDescription = '';
        $this->docIsPublished = true;
        $this->docIsFeatured = true;
        $this->docSortOrder = '100';
        $this->docFile = null;
        $this->resetValidation();
        unset($this->docBatches);
    }

    protected function resetTable(): void
    {
        $this->tableLoaded = false;
        $this->scheduleRows = [];
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'code', 'type']);
    }

    #[Computed]
    public function docBatches()
    {
        if (! $this->docProgramId) {
            return collect();
        }

        return AcademicBatch::query()
            ->where('program_id', (int) $this->docProgramId)
            ->orderByDesc('id')
            ->get(['id', 'name', 'code', 'semester_key']);
    }

    #[Computed]
    public function documents()
    {
        return AcademicScheduleDocument::query()
            ->with(['program:id,name_ar,code', 'batch:id,name,code', 'uploader:id,name'])
            ->when($this->docFilterProgramId !== '', fn ($q) => $q->where('program_id', (int) $this->docFilterProgramId))
            ->ordered()
            ->get();
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()
            ->when($this->semesterKey, fn ($q) => $q->where('semester_key', $this->semesterKey))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function levels()
    {
        if (! $this->batchId) {
            return collect();
        }

        $programId = AcademicBatch::query()->whereKey($this->batchId)->value('program_id');

        return AcademicLevel::query()
            ->when($programId, fn ($q) => $q->where('program_id', $programId))
            ->orderBy('sort_order')
            ->get(['id', 'name_ar']);
    }

    #[Computed]
    public function staffList()
    {
        return AcademicStaff::query()->where('status', 'active')->orderBy('name_ar')->get(['id', 'name_ar']);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.schedules'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الجداول الدراسية'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif

<div class="admin-view-tabs" style="margin-bottom:1rem;">
    <button type="button" @class(['admin-view-tab', 'is-active' => $tab === 'documents']) wire:click="setTab('documents')">
        جداول الدبلومات (ملفات)
    </button>
    <button type="button" @class(['admin-view-tab', 'is-active' => $tab === 'weekly']) wire:click="setTab('weekly')">
        جداول الشعب الأسبوعية
    </button>
</div>

@if ($tab === 'documents')
    @if (auth()->user()?->canAdmin('schedules.manage'))
        <section class="admin-crud-card">
            <div class="admin-crud-card__head admin-crud-card__head--row">
                <h2>{{ $editingDocumentId ? 'تعديل جدول الدبلوم' : 'رفع جدول دراسي للدبلوم' }}</h2>
                @if ($editingDocumentId)
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelDocumentEdit">إلغاء التعديل</button>
                @endif
            </div>

            <form wire:submit="saveDocument" class="admin-filter-grid" style="padding:1rem 1.1rem 1.25rem;grid-template-columns:repeat(2,minmax(0,1fr));">
                <div class="admin-field">
                    <label for="docProgramId">البرنامج / الدبلوم</label>
                    <select id="docProgramId" class="admin-control" wire:model.live="docProgramId">
                        <option value="">— اختر البرنامج —</option>
                        @foreach ($this->programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name_ar }}@if ($program->code) ({{ $program->code }})@endif</option>
                        @endforeach
                    </select>
                    @error('docProgramId') <span class="admin-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="admin-field">
                    <label for="docBatchId">الدفعة (اختياري)</label>
                    <select id="docBatchId" class="admin-control" wire:model="docBatchId" @disabled(! $docProgramId)>
                        <option value="">كل دفعات البرنامج</option>
                        @foreach ($this->docBatches as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->code }})</option>
                        @endforeach
                    </select>
                    @error('docBatchId') <span class="admin-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="admin-field">
                    <label for="docSemesterKey">الفصل (اختياري)</label>
                    <select id="docSemesterKey" class="admin-control" wire:model="docSemesterKey">
                        <option value="">— بدون تحديد —</option>
                        @foreach (AcademicBatchOptions::semesters() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-field">
                    <label for="docTitle">عنوان الجدول</label>
                    <input id="docTitle" type="text" class="admin-control" wire:model="docTitle" placeholder="مثال: الجدول الدراسي — الفصل الأول">
                    @error('docTitle') <span class="admin-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="admin-field" style="grid-column:1/-1;">
                    <label for="docDescription">وصف مختصر (اختياري)</label>
                    <textarea id="docDescription" class="admin-control" rows="2" wire:model="docDescription" placeholder="يظهر للطالب مع ملف الجدول"></textarea>
                    @error('docDescription') <span class="admin-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="admin-field">
                    <label for="docFile">ملف الجدول {{ $editingDocumentId ? '(اتركه فارغاً للإبقاء على الملف الحالي)' : '' }}</label>
                    <input id="docFile" type="file" class="admin-control" wire:model="docFile" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                    <small style="color:#64748b;">PDF أو صورة أو Word/Excel — حتى 20MB</small>
                    <div wire:loading wire:target="docFile" style="color:#166534;font-size:.8rem;margin-top:.35rem;">جاري رفع الملف...</div>
                    @error('docFile') <span class="admin-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="admin-field">
                    <label for="docSortOrder">الترتيب</label>
                    <input id="docSortOrder" type="number" min="0" max="9999" class="admin-control" wire:model="docSortOrder">
                </div>

                <div class="admin-field" style="display:flex;flex-direction:column;gap:.55rem;justify-content:flex-end;">
                    <label style="display:flex;align-items:center;gap:.5rem;font-weight:700;">
                        <input type="checkbox" wire:model="docIsPublished"> منشور للطلاب
                    </label>
                    <label style="display:flex;align-items:center;gap:.5rem;font-weight:700;">
                        <input type="checkbox" wire:model="docIsFeatured"> تحديده كجدول رئيسي (أعلى تفاصيل الدبلوم)
                    </label>
                </div>

                <div class="admin-filter-actions" style="grid-column:1/-1;">
                    <button type="submit" class="admin-btn-primary" wire:loading.attr="disabled" wire:target="saveDocument,docFile">
                        <span wire:loading.remove wire:target="saveDocument">{{ $editingDocumentId ? 'حفظ التعديلات' : 'رفع الجدول' }}</span>
                        <span wire:loading wire:target="saveDocument">جاري الحفظ...</span>
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="admin-crud-card">
        <div class="admin-crud-card__head admin-crud-card__head--row">
            <h2>ملفات الجداول ({{ $this->documents->count() }})</h2>
            <div class="admin-field" style="min-width:14rem;margin:0;">
                <select class="admin-control" wire:model.live="docFilterProgramId">
                    <option value="">كل البرامج</option>
                    @foreach ($this->programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name_ar }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>البرنامج</th>
                        <th>الدفعة</th>
                        <th>الملف</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->documents as $document)
                        <tr wire:key="schedule-doc-{{ $document->id }}">
                            <td>
                                <div class="admin-section-cell">
                                    <span class="admin-section-cell__title">{{ $document->title }}</span>
                                    @if ($document->is_featured)
                                        <span style="display:inline-block;margin-top:.25rem;padding:.15rem .45rem;border-radius:999px;background:#fef3c7;color:#92400e;font-size:.68rem;font-weight:800;">رئيسي</span>
                                    @endif
                                    @if ($document->description)
                                        <small style="display:block;color:#64748b;margin-top:.2rem;">{{ \Illuminate\Support\Str::limit($document->description, 80) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $document->program?->name_ar ?? '—' }}</td>
                            <td>{{ $document->batch?->name ?? 'كل الدفعات' }}</td>
                            <td>
                                <a href="{{ $document->url() }}" target="_blank" rel="noopener" class="admin-code admin-code--block" dir="ltr">
                                    {{ $document->original_name ?: 'عرض الملف' }}
                                </a>
                                <small style="color:#94a3b8;">{{ $document->humanSize() }}</small>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:.35rem;">
                                    <span style="display:inline-block;padding:.2rem .55rem;border-radius:999px;font-size:.68rem;font-weight:800;{{ $document->is_published ? 'background:#dcfce7;color:#166534;' : 'background:#f1f5f9;color:#64748b;' }}">
                                        {{ $document->is_published ? 'منشور' : 'مسودة' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:.35rem;">
                                    <a href="{{ $document->url() }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                                    @if (auth()->user()?->canAdmin('schedules.manage'))
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="editDocument({{ $document->id }})">تعديل</button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleDocumentPublished({{ $document->id }})">
                                            {{ $document->is_published ? 'إخفاء' : 'نشر' }}
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleDocumentFeatured({{ $document->id }})">
                                            {{ $document->is_featured ? 'إلغاء التحديد' : 'تحديد' }}
                                        </button>
                                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" style="color:#b91c1c;border-color:#fecaca;" wire:click="deleteDocument({{ $document->id }})" wire:confirm="حذف ملف الجدول نهائياً؟">حذف</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-table-empty" style="text-align:center;padding:2rem;">
                                لا توجد ملفات جداول بعد — ارفع جدولاً للدبلوم ليظهر للطلاب أعلى تفاصيل البرنامج.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@else
    <section class="admin-crud-card admin-crud-card--filter">
        <div class="admin-crud-card__head">
            <h2>اختر الفصل ثم الدفعة ثم المستوى ثم الفترة</h2>
        </div>
        <div class="admin-filter-grid admin-filter-grid--schedules">
            <div class="admin-field">
                <label for="semesterKey">الفصل الدراسي (المقبول)</label>
                <select id="semesterKey" class="admin-control" wire:model.live="semesterKey">
                    <option value="">— اختر الفصل —</option>
                    @foreach (AcademicBatchOptions::semesters() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="batchId">الدفعة</label>
                <select id="batchId" class="admin-control" wire:model.live="batchId" @disabled(! $semesterKey)>
                    <option value="">— اختر الدفعة —</option>
                    @foreach ($this->batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="levelId">المستوى</label>
                <select id="levelId" class="admin-control" wire:model.live="levelId" @disabled(! $batchId)>
                    <option value="">— اختر المستوى —</option>
                    @foreach ($this->levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="period">الفترة</label>
                <select id="period" class="admin-control" wire:model.live="period" @disabled(! $levelId)>
                    <option value="">— اختر الفترة —</option>
                    @foreach (AcademicScheduleOptions::periods() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions admin-filter-actions--schedules">
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="loadTable"
                    wire:loading.attr="disabled" @disabled(! $semesterKey || ! $batchId || ! $levelId || ! $period)>
                    <span wire:loading.remove wire:target="loadTable">عرض جدول الشعب</span>
                    <span wire:loading wire:target="loadTable">جاري التحميل...</span>
                </button>
            </div>
        </div>
    </section>

    @if ($tableLoaded)
        <section class="admin-crud-card">
            <div class="admin-crud-card__head admin-crud-card__head--row">
                <h2>جداول الشعب ({{ count($scheduleRows) }})</h2>
                @if (count($scheduleRows) && auth()->user()?->canAdmin('schedules.manage'))
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="saveSchedules" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveSchedules">حفظ جدول الشعب</span>
                        <span wire:loading wire:target="saveSchedules">جاري الحفظ...</span>
                    </button>
                @endif
            </div>
            <div class="admin-table-wrap">
                <table class="admin-data-table admin-data-table--schedules">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الشعبة</th>
                            <th>المقرر</th>
                            <th>المدرب</th>
                            <th>اليوم</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scheduleRows as $index => $row)
                            <tr wire:key="schedule-row-{{ $row['section_id'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="admin-section-cell">
                                        <code class="admin-code admin-code--block">{{ $row['section_code'] }}</code>
                                        <span class="admin-section-cell__title">{{ $row['section_name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $row['course_name'] }}</td>
                                <td>
                                    <select class="admin-control admin-control--table" wire:model.live="scheduleRows.{{ $index }}.staff_id">
                                        <option value="">-- اختر --</option>
                                        @foreach ($this->staffList as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name_ar }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="admin-control admin-control--table" wire:model="scheduleRows.{{ $index }}.day_of_week">
                                        @foreach (AcademicScheduleOptions::days() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="admin-time-range">
                                        <input type="time" class="admin-control admin-control--time" wire:model="scheduleRows.{{ $index }}.time_start">
                                        <span class="admin-time-range__sep" aria-hidden="true">–</span>
                                        <input type="time" class="admin-control admin-control--time" wire:model="scheduleRows.{{ $index }}.time_end">
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-table-empty" style="text-align:center;padding:2rem;">
                                    لا توجد شعب لهذا الاختيار — غيّر الفلاتر أو <a href="{{ route('admin.sections.create', ['batch' => $batchId]) }}">أضف شعباً</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (count($scheduleRows))
                <div class="admin-filter-actions" style="margin-top:1rem;">
                    <a href="{{ route('admin.sections', ['batch' => $batchId]) }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة الشعب</a>
                </div>
            @endif
        </section>
    @endif
@endif

@include('partials.admin.shell-end')
