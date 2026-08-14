<?php

use App\Models\AcademicSection;
use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Services\AssignmentService;
use App\Support\AssignmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('واجب | لوحة التحكم')]
class extends Component
{
    public ?int $assignmentId = null;

    public ?int $sectionId = null;

    public ?int $sessionId = null;

    public string $scope = 'section';

    public string $title = '';

    public string $instructions = '';

    public int $maxScore = 100;

    public string $dueAt = '';

    public bool $allowLateSubmission = true;

    public int $latePenaltyPercent = 0;

    public int $maxAttempts = 1;

    public int $maxFiles = 5;

    public bool $allowTextSubmission = true;

    public string $status = 'draft';

    public ?string $savedMessage = null;

    public function mount(?Assignment $assignment = null, ?int $section = null, ?int $session = null): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.manage'), 403);

        if ($assignment) {
            $this->assignmentId = $assignment->id;
            $this->sectionId = $assignment->section_id;
            $this->sessionId = $assignment->attendance_session_id;
            $this->scope = $assignment->scope;
            $this->title = $assignment->title;
            $this->instructions = $assignment->instructions ?? '';
            $this->maxScore = $assignment->max_score;
            $this->dueAt = $assignment->due_at?->format('Y-m-d\TH:i') ?? '';
            $this->allowLateSubmission = $assignment->allow_late_submission;
            $this->latePenaltyPercent = $assignment->late_penalty_percent;
            $this->maxAttempts = $assignment->max_attempts;
            $this->maxFiles = $assignment->max_files;
            $this->allowTextSubmission = $assignment->allow_text_submission;
            $this->status = $assignment->status;

            return;
        }

        $this->sectionId = $section;
        $this->sessionId = $session;

        if ($session) {
            $this->scope = 'session';
        }
    }

    #[Computed]
    public function sections()
    {
        return AcademicSection::query()->orderBy('name')->get(['id', 'name', 'code']);
    }

    #[Computed]
    public function sessions()
    {
        if (! $this->sectionId) {
            return collect();
        }

        return AttendanceSession::query()
            ->where('section_id', $this->sectionId)
            ->orderByDesc('session_date')
            ->get();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.manage'), 403);

        $this->validate([
            'sectionId' => ['required', 'exists:academic_sections,id'],
            'scope' => ['required', 'in:section,session'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'maxScore' => ['required', 'integer', 'min:1', 'max:1000'],
            'dueAt' => ['nullable', 'date'],
            'latePenaltyPercent' => ['integer', 'min:0', 'max:100'],
            'maxAttempts' => ['integer', 'min:1', 'max:5'],
            'maxFiles' => ['integer', 'min:1', 'max:10'],
            'sessionId' => ['required_if:scope,session', 'nullable', 'exists:attendance_sessions,id'],
        ], [], [
            'sectionId' => 'الشعبة',
            'title' => 'العنوان',
            'sessionId' => 'الحصة',
        ]);

        if ($this->scope === 'session' && ! $this->sessionId) {
            $this->addError('sessionId', 'اختر الحصة المرتبطة.');

            return;
        }

        $data = [
            'section_id' => $this->sectionId,
            'attendance_session_id' => $this->scope === 'session' ? $this->sessionId : null,
            'scope' => $this->scope,
            'title' => $this->title,
            'instructions' => $this->instructions ?: null,
            'max_score' => $this->maxScore,
            'due_at' => $this->dueAt ?: null,
            'allow_late_submission' => $this->allowLateSubmission,
            'late_penalty_percent' => $this->latePenaltyPercent,
            'max_attempts' => $this->maxAttempts,
            'max_files' => $this->maxFiles,
            'allow_text_submission' => $this->allowTextSubmission,
            'status' => $this->status,
            'created_by' => auth()->id(),
        ];

        if ($this->assignmentId) {
            $assignment = Assignment::query()->findOrFail($this->assignmentId);
            unset($data['created_by']);
            $assignment->update($data);
        } else {
            $assignment = Assignment::query()->create($data);
            $this->assignmentId = $assignment->id;
        }

        $this->savedMessage = 'تم حفظ الواجب.';

        if ($this->status === 'published' && ! $assignment->published_at) {
            app(AssignmentService::class)->publish($assignment);
        }
    }

    public function publish(AssignmentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.manage'), 403);

        $this->status = 'published';

        if (! $this->assignmentId) {
            $this->save();
        }

        $assignment = Assignment::query()->findOrFail($this->assignmentId);
        $service->publish($assignment);
        $this->status = 'published';
        $this->savedMessage = 'تم نشر الواجب للطلاب.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.assignments'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.assignments'), 'label' => 'الواجبات'],
        ['label' => $assignmentId ? 'تعديل' : 'واجب جديد'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>{{ $assignmentId ? 'تعديل الواجب' : 'واجب جديد' }}</h2>
    </div>

    <div class="admin-form-grid admin-form-grid--2">
        <div class="admin-field">
            <label for="sectionId">الشعبة *</label>
            <select id="sectionId" class="admin-control" wire:model.live="sectionId">
                <option value="">— اختر —</option>
                @foreach ($this->sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }} ({{ $section->code }})</option>
                @endforeach
            </select>
            @error('sectionId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>

        <div class="admin-field">
            <label for="scope">نطاق الواجب</label>
            <select id="scope" class="admin-control" wire:model.live="scope">
                @foreach (AssignmentOptions::scopes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if ($scope === 'session')
            <div class="admin-field">
                <label for="sessionId">الحصة *</label>
                <select id="sessionId" class="admin-control" wire:model="sessionId">
                    <option value="">— اختر —</option>
                    @foreach ($this->sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->session_date->format('Y-m-d') }} — {{ $session->displayTitle() }}</option>
                    @endforeach
                </select>
                @error('sessionId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="admin-field admin-field--wide">
            <label for="title">عنوان الواجب *</label>
            <input type="text" id="title" class="admin-control" wire:model="title">
            @error('title')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>

        <div class="admin-field admin-field--wide">
            <label for="instructions">التعليمات</label>
            <textarea id="instructions" class="admin-control" rows="6" wire:model="instructions"></textarea>
        </div>

        <div class="admin-field">
            <label for="maxScore">الدرجة العظمى</label>
            <input type="number" id="maxScore" class="admin-control" wire:model="maxScore" min="1">
        </div>

        <div class="admin-field">
            <label for="dueAt">الموعد النهائي</label>
            <input type="datetime-local" id="dueAt" class="admin-control" wire:model="dueAt" dir="ltr">
        </div>

        <div class="admin-field">
            <label for="maxAttempts">عدد المحاولات</label>
            <input type="number" id="maxAttempts" class="admin-control" wire:model="maxAttempts" min="1" max="5">
        </div>

        <div class="admin-field">
            <label for="maxFiles">حد الملفات</label>
            <input type="number" id="maxFiles" class="admin-control" wire:model="maxFiles" min="1" max="10">
        </div>

        <div class="admin-field">
            <label for="latePenaltyPercent">خصم التأخير (%)</label>
            <input type="number" id="latePenaltyPercent" class="admin-control" wire:model="latePenaltyPercent" min="0" max="100">
        </div>

        <div class="admin-field">
            <label for="status">الحالة</label>
            <select id="status" class="admin-control" wire:model="status">
                @foreach (AssignmentOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="admin-field admin-field--wide">
            <label class="admin-check"><input type="checkbox" wire:model="allowLateSubmission"> السماح بالتسليم المتأخر</label>
            <label class="admin-check"><input type="checkbox" wire:model="allowTextSubmission"> السماح بالتسليم كنص</label>
        </div>
    </div>

    <div class="admin-filter-actions" style="margin-top:1rem;">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save">حفظ</button>
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="publish">حفظ ونشر</button>
        <a href="{{ route('admin.assignments') }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
        @if ($assignmentId)
            <a href="{{ route('admin.assignments.show', $assignmentId) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض التسليمات</a>
        @endif
    </div>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-form-grid--2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .admin-field--wide { grid-column: 1 / -1; }
    @media (max-width: 767.98px) { .admin-form-grid--2 { grid-template-columns: 1fr; } }
</style>
@endpush
