<?php

use App\Models\AcademicBatch;
use App\Models\AcademicCourse;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\AcademicSection;
use App\Support\AcademicBatchOptions;
use App\Support\AcademicSectionOptions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('شعبة دراسية | لوحة التحكم')]
class extends Component
{
    public ?int $sectionId = null;

    public string $name = '';

    public string $code = '';

    public string $subtitle = '';

    public ?int $programId = null;

    public ?int $batchId = null;

    public ?int $courseId = null;

    public ?int $levelId = null;

    public string $semesterKey = '';

    public string $semester = '';

    public string $period = '';

    public string $supervisor = '';

    public int $maxCapacity = 30;

    public int $studentsCount = 0;

    public string $status = 'active';

    public function mount(?AcademicSection $section = null, ?int $batch = null): void
    {
        if ($section) {
            $this->sectionId = $section->id;
            $this->name = $section->name;
            $this->code = $section->code;
            $this->subtitle = $section->subtitle ?? '';
            $this->programId = $section->program_id;
            $this->batchId = $section->batch_id;
            $this->courseId = $section->course_id;
            $this->levelId = $section->level_id;
            $this->semesterKey = $section->semester_key ?? '';
            $this->semester = $section->semester ?? '';
            $this->period = $section->period ?? '';
            $this->supervisor = $section->supervisor ?? '';
            $this->maxCapacity = $section->max_capacity;
            $this->studentsCount = $section->students_count;
            $this->status = $section->status;

            return;
        }

        if ($batch) {
            $this->batchId = $batch;
            $this->updatedBatchId();
        }
    }

    public function updatedBatchId(): void
    {
        if (! $this->batchId) {
            return;
        }

        $batch = AcademicBatch::query()->find($this->batchId);
        if ($batch) {
            $this->programId = $batch->program_id;
            $this->semesterKey = $batch->semester_key ?? $this->semesterKey;
            $this->semester = $batch->semester ?? $this->semester;
        }
    }

    public function updatedProgramId(): void
    {
        $this->courseId = null;
        $this->levelId = null;
    }

    public function updatedSemesterKey(?string $value): void
    {
        if ($value && isset(AcademicBatchOptions::semesters()[$value])) {
            $this->semester = AcademicBatchOptions::semesters()[$value];
        }
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar']);
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()
            ->when($this->programId, fn ($q) => $q->where('program_id', $this->programId))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'program_id']);
    }

    #[Computed]
    public function courses()
    {
        if (! $this->programId) {
            return collect();
        }

        return AcademicCourse::query()->where('program_id', $this->programId)->orderBy('name_ar')->get(['id', 'name_ar']);
    }

    #[Computed]
    public function levels()
    {
        if (! $this->programId) {
            return collect();
        }

        return AcademicLevel::query()->where('program_id', $this->programId)->orderBy('sort_order')->get(['id', 'name_ar']);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', Rule::unique('academic_sections', 'code')->ignore($this->sectionId)],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'programId' => ['nullable', 'exists:academic_programs,id'],
            'batchId' => ['nullable', 'exists:academic_batches,id'],
            'courseId' => ['nullable', 'exists:academic_courses,id'],
            'levelId' => ['nullable', 'exists:academic_levels,id'],
            'semesterKey' => ['nullable', 'string', 'max:32'],
            'semester' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', Rule::in(array_keys(AcademicSectionOptions::periods()))],
            'supervisor' => ['nullable', 'string', 'max:120'],
            'maxCapacity' => ['nullable', 'integer', 'min:1'],
            'studentsCount' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys(AcademicSectionOptions::statuses()))],
        ], [], ['name' => 'اسم الشعبة', 'code' => 'رمز الشعبة']);

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'subtitle' => $validated['subtitle'] ?: null,
            'program_id' => $validated['programId'] ?: null,
            'batch_id' => $validated['batchId'] ?: null,
            'course_id' => $validated['courseId'] ?: null,
            'level_id' => $validated['levelId'] ?: null,
            'semester_key' => $validated['semesterKey'] ?: null,
            'semester' => $validated['semester'] ?: null,
            'period' => $validated['period'] ?: null,
            'supervisor' => $validated['supervisor'] ?: null,
            'max_capacity' => $validated['maxCapacity'] ?: 30,
            'students_count' => $validated['studentsCount'],
            'status' => $validated['status'],
            'added_by' => auth()->user()?->name ?? 'مدير النظام',
        ];

        if ($this->sectionId) {
            $section = AcademicSection::query()->findOrFail($this->sectionId);
            $section->update($data);
            session()->flash('admin_message', 'تم تحديث الشعبة.');
            $this->redirect(route('admin.sections.show', $section));
        } else {
            $section = AcademicSection::query()->create($data);
            session()->flash('admin_message', 'تم إنشاء الشعبة.');
            $this->redirect(route('admin.sections.show', $section));
        }
    }
};
?>

@php $pageTitle = $sectionId ? 'تعديل الشعبة' : 'شعبة جديدة'; @endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.sections'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.sections'), 'label' => 'الشعب الدراسية'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.sections') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>
    <form wire:submit="save">
        @include('partials.admin.section-form-fields', [
            'programs' => $this->programs,
            'batches' => $this->batches,
            'courses' => $this->courses,
            'levels' => $this->levels,
        ])
        <div class="admin-filter-actions" style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
