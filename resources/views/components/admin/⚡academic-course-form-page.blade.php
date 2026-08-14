<?php

use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مقرر دراسي | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;
    public ?int $courseId = null;

    public string $nameAr = '';

    public string $nameEn = '';

    public string $symbolAr = '';

    public string $symbolEn = '';

    public string $code = '';

    public int $creditHours = 0;

    public ?int $programId = null;

    public ?int $levelId = null;

    public string $status = 'active';

    public string $targetGroup = '';

    public string $summary = '';

    public string $imageUrl = '';

    public $imageFile = null;

    public bool $removeImage = false;

    public ?string $existingImageUrl = null;

    public function mount(?AcademicCourse $course = null, ?int $program = null): void
    {
        if ($course) {
            $this->courseId = $course->id;
            $this->nameAr = $course->name_ar;
            $this->nameEn = $course->name_en ?? '';
            $this->symbolAr = $course->symbol_ar ?? '';
            $this->symbolEn = $course->symbol_en ?? '';
            $this->code = $course->code;
            $this->creditHours = $course->credit_hours;
            $this->programId = $course->program_id;
            $this->levelId = $course->level_id;
            $this->status = $course->status;
            $this->targetGroup = $course->target_group ?? '';
            $this->summary = $course->summary ?? '';
            $this->imageUrl = str_starts_with($course->image_url ?? '', 'http') ? $course->image_url : '';
            $this->existingImageUrl = $course->resolved_image_url;

            return;
        }

        if ($program) {
            $this->programId = $program;
        }
    }

    public function updatedProgramId(): void
    {
        $this->levelId = null;
    }

    public function updatedImageFile(): void
    {
        $this->removeImage = false;
        $this->validateOnly('imageFile', $this->rules(), [], $this->attributeNames());
    }

    public function clearImage(): void
    {
        $this->imageFile = null;
        $this->imageUrl = '';
        $this->existingImageUrl = null;
        $this->removeImage = true;
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    #[Computed]
    public function levels()
    {
        if (! $this->programId) {
            return collect();
        }

        return AcademicLevel::query()
            ->where('program_id', $this->programId)
            ->orderBy('sort_order')
            ->get(['id', 'name_ar']);
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [], $this->attributeNames());

        $data = [
            'name_ar' => $validated['nameAr'],
            'name_en' => $validated['nameEn'] ?: null,
            'symbol_ar' => $validated['symbolAr'] ?: null,
            'symbol_en' => $validated['symbolEn'] ?: null,
            'code' => $validated['code'],
            'credit_hours' => $validated['creditHours'],
            'program_id' => $validated['programId'],
            'level_id' => $validated['levelId'] ?: null,
            'status' => $validated['status'],
            'target_group' => $validated['targetGroup'] ?: null,
            'summary' => $validated['summary'] ?: null,
            'added_by' => auth()->user()?->name ?? 'مدير النظام',
        ];

        $data['image_url'] = $this->resolveImageUrlForSave($validated);

        if ($this->courseId) {
            $course = AcademicCourse::query()->findOrFail($this->courseId);
            $this->deleteStoredImageIfReplaced($course->image_url, $data['image_url']);
            $course->update($data);
            session()->flash('admin_message', 'تم تحديث المقرر بنجاح.');
            $this->redirect(route('admin.academic-courses.show', $course));
        } else {
            $course = AcademicCourse::query()->create($data);
            session()->flash('admin_message', 'تم إنشاء المقرر بنجاح.');
            $this->redirect(route('admin.academic-courses.show', $course));
        }
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'nameAr' => ['required', 'string', 'max:255'],
            'nameEn' => ['nullable', 'string', 'max:255'],
            'symbolAr' => ['nullable', 'string', 'max:64'],
            'symbolEn' => ['nullable', 'string', 'max:64'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('academic_courses', 'code')->ignore($this->courseId),
            ],
            'creditHours' => ['nullable', 'integer', 'min:0', 'max:30'],
            'programId' => ['required', 'exists:academic_programs,id'],
            'levelId' => ['nullable', 'exists:academic_levels,id'],
            'status' => ['required', Rule::in(array_keys(\App\Support\AcademicCourseOptions::statuses()))],
            'targetGroup' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'imageUrl' => ['nullable', 'string', 'max:500'],
            'imageFile' => ['nullable', 'image', 'max:2048'],
        ];
    }

  /** @param array<string, mixed> $validated */
    protected function resolveImageUrlForSave(array $validated): ?string
    {
        if ($this->imageFile instanceof TemporaryUploadedFile) {
            return $this->imageFile->store('academic-courses', 'public');
        }

        if ($this->removeImage) {
            return null;
        }

        if (! empty($validated['imageUrl'])) {
            return $validated['imageUrl'];
        }

        if ($this->courseId) {
            return AcademicCourse::query()->find($this->courseId)?->image_url;
        }

        return null;
    }

    protected function deleteStoredImageIfReplaced(?string $previous, ?string $next): void
    {
        if (! $previous || $previous === $next) {
            return;
        }

        if (str_starts_with($previous, 'http') || str_starts_with($previous, 'new-platform/')) {
            return;
        }

        Storage::disk('public')->delete($previous);
    }

    /** @return array<string, string> */
    protected function attributeNames(): array
    {
        return [
            'nameAr' => 'اسم المقرر',
            'code' => 'كود المقرر',
            'programId' => 'البرنامج',
        ];
    }
};
?>

@php
    $isEdit = (bool) $courseId;
    $pageTitle = $isEdit ? 'تعديل المقرر' : 'مقرر جديد';
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.academic-courses'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.academic-courses'), 'label' => 'المقررات الدراسية'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.academic-courses') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة للقائمة</a>
    </div>

    <form wire:submit="save">
        @include('partials.admin.academic-course-form-fields', [
            'programs' => $this->programs,
            'levels' => $this->levels,
        ])

        <div class="admin-filter-actions" style="margin-top: 1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء المقرر' }}</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
            @if ($isEdit)
                <a href="{{ route('admin.academic-courses.show', $courseId) }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
            @endif
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
