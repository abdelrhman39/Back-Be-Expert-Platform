<?php

use App\Models\AcademicBatch;
use App\Models\AcademicSection;
use App\Models\AcademicStudent;
use App\Services\AcademicEnrollmentLifecycleService;
use App\Support\AcademicStudentOptions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('طالب | لوحة التحكم')]
class extends Component
{
    public ?int $studentId = null;

    public string $nameAr = '';

    public string $nameEn = '';

    public string $academicId = '';

    public string $nationalId = '';

    public string $mobile = '';

    public string $email = '';

    public string $gender = '';

    public string $city = '';

    public ?int $batchId = null;

    public ?int $sectionId = null;

    public string $academicStatus = 'studying';

    public string $studyStatus = '';

    public bool $loginAllowed = true;

    public string $statusChangeReason = '';

    public bool $requiresStatusReason = false;

    public function mount(?AcademicStudent $student = null, ?int $batch = null, ?int $section = null): void
    {
        if ($student) {
            $this->studentId = $student->id;
            $this->nameAr = $student->name_ar;
            $this->nameEn = $student->name_en ?? '';
            $this->academicId = $student->academic_id ?? '';
            $this->nationalId = $student->national_id ?? '';
            $this->mobile = $student->mobile ?? '';
            $this->email = $student->email ?? '';
            $this->gender = $student->gender ?? '';
            $this->city = $student->city ?? '';
            $this->batchId = $student->batch_id;
            $this->sectionId = $student->section_id;
            $this->academicStatus = $student->academic_status ?? 'studying';
            $this->studyStatus = $student->study_status ?? '';
            $this->loginAllowed = $student->login_allowed;
            $this->refreshStatusReasonRequirement();

            return;
        }

        if ($batch) {
            $this->batchId = $batch;
        }

        if ($section) {
            $sectionModel = AcademicSection::query()->find($section);
            if ($sectionModel) {
                $this->sectionId = $sectionModel->id;
                $this->batchId = $sectionModel->batch_id;
            }
        }
    }

    public function updatedBatchId(): void
    {
        if (! $this->sectionId) {
            return;
        }

        $belongs = AcademicSection::query()
            ->whereKey($this->sectionId)
            ->where('batch_id', $this->batchId)
            ->exists();

        if (! $belongs) {
            $this->sectionId = null;
        }
    }

    public function updatedAcademicStatus(string $value): void
    {
        if (! $this->studyStatus || $this->studyStatus === AcademicStudentOptions::academicStatusLabel($this->academicStatus)) {
            $this->studyStatus = AcademicStudentOptions::academicStatusLabel($value);
        }

        $this->refreshStatusReasonRequirement();
    }

    protected function refreshStatusReasonRequirement(): void
    {
        $this->requiresStatusReason = false;

        if (! $this->studentId) {
            return;
        }

        $student = AcademicStudent::query()->find($this->studentId);

        if (! $student) {
            return;
        }

        $lifecycle = app(AcademicEnrollmentLifecycleService::class);

        $this->requiresStatusReason = $student->academic_status !== $this->academicStatus
            && in_array($this->academicStatus, $lifecycle->sensitivePostPaymentStatuses(), true)
            && $lifecycle->studentHasConfirmedPayment($student);
    }

    #[Computed]
    public function batches()
    {
        return AcademicBatch::query()->with('program')->orderBy('name')->get();
    }

    #[Computed]
    public function sectionsForBatch()
    {
        if (! $this->batchId) {
            return collect();
        }

        return AcademicSection::query()
            ->where('batch_id', $this->batchId)
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'max_capacity', 'students_count']);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nameAr' => ['required', 'string', 'max:255'],
            'nameEn' => ['nullable', 'string', 'max:255'],
            'academicId' => ['required', 'string', 'max:32', Rule::unique('academic_students', 'academic_id')->ignore($this->studentId)],
            'nationalId' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gender' => ['nullable', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:120'],
            'batchId' => ['nullable', 'exists:academic_batches,id'],
            'sectionId' => $this->batchId
                ? ['nullable', Rule::exists('academic_sections', 'id')->where(fn ($q) => $q->where('batch_id', $this->batchId))]
                : ['nullable', 'prohibited'],
            'academicStatus' => ['required', Rule::in(array_keys(AcademicStudentOptions::academicStatuses()))],
            'studyStatus' => ['nullable', 'string', 'max:255'],
            'loginAllowed' => ['boolean'],
            'statusChangeReason' => [$this->requiresStatusReason ? 'required' : 'nullable', 'string', 'max:1000'],
        ], [], [
            'nameAr' => 'الاسم',
            'academicId' => 'الرقم الأكاديمي',
            'sectionId' => 'الشعبة الدراسية',
            'statusChangeReason' => 'سبب تغيير الحالة',
        ]);

        if (! $validated['batchId']) {
            $validated['sectionId'] = null;
        }

        $previousSectionId = null;
        $previousBatchId = null;
        $lifecycle = app(AcademicEnrollmentLifecycleService::class);

        if ($this->studentId) {
            $existing = AcademicStudent::query()->findOrFail($this->studentId);
            $previousSectionId = $existing->section_id;
            $previousBatchId = $existing->batch_id;

            try {
                $lifecycle->assertAcademicStatusChangeAllowed(
                    $existing,
                    $validated['academicStatus'],
                    $validated['statusChangeReason'] ?? null,
                );
            } catch (ValidationException $e) {
                $this->requiresStatusReason = true;
                throw $e;
            }
        }

        $data = [
            'name_ar' => $validated['nameAr'],
            'name_en' => $validated['nameEn'] ?: null,
            'academic_id' => $validated['academicId'],
            'national_id' => $validated['nationalId'] ?: null,
            'mobile' => $validated['mobile'] ?: null,
            'email' => $validated['email'] ?: null,
            'gender' => $validated['gender'] ?: null,
            'city' => $validated['city'] ?: null,
            'batch_id' => $validated['batchId'] ?: null,
            'section_id' => $validated['sectionId'] ?: null,
            'academic_status' => $validated['academicStatus'],
            'study_status' => $validated['studyStatus'] ?: AcademicStudentOptions::academicStatusLabel($validated['academicStatus']),
            'login_allowed' => $validated['loginAllowed'],
            'joined_at' => $this->studentId ? null : now(),
        ];

        if ($this->studentId) {
            $student = AcademicStudent::query()->findOrFail($this->studentId);
            $oldStatus = $student->academic_status;
            unset($data['joined_at']);
            $student->update($data);

            if ($oldStatus !== $student->academic_status && filled($validated['statusChangeReason'] ?? null)
                && $lifecycle->studentHasConfirmedPayment($student)) {
                $lifecycle->recordPostPaymentStatusChange(
                    $student,
                    $oldStatus,
                    $student->academic_status,
                    $validated['statusChangeReason'],
                    auth()->user(),
                );
            }

            $this->syncEnrollmentCounts($previousSectionId, $previousBatchId, $student->section_id, $student->batch_id);
            session()->flash('admin_message', 'تم تحديث بيانات الطالب.');
            $this->redirect(route('admin.students.show', $student));
        } else {
            $student = AcademicStudent::query()->create($data);
            $this->syncEnrollmentCounts(null, null, $student->section_id, $student->batch_id);
            session()->flash('admin_message', 'تم تسجيل الطالب.');
            $this->redirect(route('admin.students.show', $student));
        }
    }

    private function syncEnrollmentCounts(?int $oldSectionId, ?int $oldBatchId, ?int $newSectionId, ?int $newBatchId): void
    {
        foreach (array_unique(array_filter([$oldSectionId, $newSectionId])) as $sectionId) {
            AcademicSection::query()->find($sectionId)?->refreshStudentsCount();
        }

        foreach (array_unique(array_filter([$oldBatchId, $newBatchId])) as $batchId) {
            AcademicBatch::query()->find($batchId)?->refreshStudentsCount();
        }
    }
};
?>

@php $pageTitle = $studentId ? 'تعديل الطالب' : 'طالب جديد'; @endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.students'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.students'), 'label' => 'الطلاب المشتركين'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.students') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>
    <form wire:submit="save">
        @include('partials.admin.student-form-fields', [
            'batches' => $this->batches,
            'sections' => $this->sectionsForBatch,
            'requiresStatusReason' => $requiresStatusReason,
        ])
        <div class="admin-filter-actions" style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
