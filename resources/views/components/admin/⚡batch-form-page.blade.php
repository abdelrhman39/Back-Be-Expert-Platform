<?php

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Support\AcademicBatchOptions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('دفعة دراسية | لوحة التحكم')]
class extends Component
{
    public ?int $batchId = null;

    public string $name = '';

    public string $code = '';

    public ?int $programId = null;

    public string $status = 'planned';

    public string $semesterKey = '';

    public string $semester = '';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $studyMode = '';

    public string $coordinator = '';

    public ?int $capacity = null;

    public ?string $tuitionAmount = null;

    public bool $installmentAllowed = true;

    public bool $enrollmentOpen = true;

    public string $notes = '';

    public function mount(?AcademicBatch $batch = null): void
    {
        if (! $batch) {
            return;
        }

        $this->batchId = $batch->id;
        $this->name = $batch->name;
        $this->code = $batch->code ?? '';
        $this->programId = $batch->program_id;
        $this->status = $batch->status;
        $this->semesterKey = $batch->semester_key ?? '';
        $this->semester = $batch->semester ?? '';
        $this->startDate = $batch->start_date?->format('Y-m-d');
        $this->endDate = $batch->end_date?->format('Y-m-d');
        $this->studyMode = $batch->study_mode ?? '';
        $this->coordinator = $batch->coordinator ?? '';
        $this->capacity = $batch->capacity;
        $this->tuitionAmount = $batch->tuition_amount !== null ? (string) $batch->tuition_amount : null;
        $this->installmentAllowed = $batch->installment_allowed;
        $this->enrollmentOpen = $batch->enrollment_open;
        $this->notes = $batch->notes ?? '';
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
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [], $this->attributeNames());

        $data = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'program_id' => $validated['programId'],
            'status' => $validated['status'],
            'semester_key' => $validated['semesterKey'] ?: null,
            'semester' => $validated['semester'] ?: null,
            'start_date' => $validated['startDate'] ?: null,
            'end_date' => $validated['endDate'] ?: null,
            'study_mode' => $validated['studyMode'] ?: null,
            'coordinator' => $validated['coordinator'] ?: null,
            'capacity' => $validated['capacity'] ?: null,
            'tuition_amount' => $validated['tuitionAmount'] !== null && $validated['tuitionAmount'] !== '' ? $validated['tuitionAmount'] : null,
            'installment_allowed' => $validated['installmentAllowed'],
            'enrollment_open' => $validated['enrollmentOpen'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->batchId) {
            $batch = AcademicBatch::query()->findOrFail($this->batchId);
            $batch->update($data);
            session()->flash('admin_message', 'تم تحديث الدفعة بنجاح.');
            $this->redirect(route('admin.batches.show', $batch));
        } else {
            $batch = AcademicBatch::query()->create($data);
            session()->flash('admin_message', 'تم إنشاء الدفعة بنجاح.');
            $this->redirect(route('admin.batches.show', $batch));
        }
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('academic_batches', 'code')->ignore($this->batchId),
            ],
            'programId' => ['required', 'exists:academic_programs,id'],
            'status' => ['required', Rule::in(array_keys(AcademicBatchOptions::statuses()))],
            'semesterKey' => ['nullable', 'string', 'max:32'],
            'semester' => ['nullable', 'string', 'max:255'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'studyMode' => ['nullable', Rule::in(array_keys(AcademicBatchOptions::studyModes()))],
            'coordinator' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'tuitionAmount' => ['nullable', 'numeric', 'min:0'],
            'installmentAllowed' => ['boolean'],
            'enrollmentOpen' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    protected function attributeNames(): array
    {
        return [
            'name' => 'اسم الدفعة',
            'code' => 'كود الدفعة',
            'programId' => 'البرنامج',
        ];
    }
};
?>

@php $isEdit = (bool) $batchId; $pageTitle = $isEdit ? 'تعديل الدفعة' : 'دفعة جديدة'; @endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.batches'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.batches'), 'label' => 'الدفعات الدراسية'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.batches') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>
    <form wire:submit="save">
        @include('partials.admin.batch-form-fields', ['programs' => $this->programs])
        <div class="admin-filter-actions" style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الدفعة' }}</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
