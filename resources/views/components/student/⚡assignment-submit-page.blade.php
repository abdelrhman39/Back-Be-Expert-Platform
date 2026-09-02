<?php

use App\Models\Assignment;
use App\Models\SubmissionFile;
use App\Services\AssignmentService;
use App\Support\AssignmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new #[Layout('layouts.app-user')]
#[Title('تسليم الواجب | مركز التعلم المستمر')]
class extends Component
{
    use WithFileUploads;

    public Assignment $assignment;

    public string $bodyText = '';

    public string $submissionUrl = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $submissionFiles = [];

    public ?string $portalMessage = null;

    public function mount(Assignment $assignment, AssignmentService $service): void
    {
        abort_unless($service->studentCanAccess(auth()->user(), $assignment), 404);

        $this->assignment = $assignment->load(['section', 'session']);

        $student = auth()->user()?->academicStudent;
        $submission = $student ? $service->latestSubmission($assignment, $student) : null;

        if ($submission) {
            $this->bodyText = $submission->body_text ?? '';
            $this->submissionUrl = $submission->submission_url ?? '';
        }
    }

    #[Computed]
    public function submission()
    {
        $student = auth()->user()?->academicStudent;

        if (! $student) {
            return null;
        }

        return app(AssignmentService::class)->latestSubmission($this->assignment, $student)?->load('files');
    }

    #[Computed]
    public function canSubmit(): bool
    {
        $sub = $this->submission;

        if (! $this->assignment->acceptsSubmissions()) {
            return false;
        }

        if (! $sub) {
            return true;
        }

        return ! in_array($sub->status, ['submitted', 'late', 'graded'], true);
    }

    public function submitFinal(AssignmentService $service): void
    {
        $student = auth()->user()?->academicStudent;

        abort_unless($student, 403);

        $service->submit(
            $this->assignment,
            $student,
            $this->bodyText ?: null,
            $this->submissionUrl ?: null,
            $this->submissionFiles,
            true,
        );

        $this->submissionFiles = [];
        $this->portalMessage = 'تم تسليم الواجب بنجاح.';
        unset($this->submission);
    }

    public function saveDraft(AssignmentService $service): void
    {
        $student = auth()->user()?->academicStudent;

        abort_unless($student, 403);

        $service->submit(
            $this->assignment,
            $student,
            $this->bodyText ?: null,
            $this->submissionUrl ?: null,
            $this->submissionFiles,
            false,
        );

        $this->submissionFiles = [];
        $this->portalMessage = 'تم حفظ المسودة.';
        unset($this->submission);
    }

    public function removeFile(int $fileId, AssignmentService $service): void
    {
        $file = SubmissionFile::query()
            ->where('id', $fileId)
            ->whereHas('submission', fn ($q) => $q
                ->where('assignment_id', $this->assignment->id)
                ->where('student_id', auth()->user()?->academicStudent?->id))
            ->first();

        if ($file) {
            $service->deleteSubmissionFile($file);
            $this->portalMessage = 'تم حذف الملف.';
            unset($this->submission);
        }
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'assignments', 'portalTitle' => $this->assignment->title])

<div class="portal-dashboard portal-assignment-submit">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <a href="{{ route('assignments', ['locale' => $locale]) }}" class="portal-panel__link">← العودة للواجبات</a>
            <h1 class="portal-orders-intro__title">{{ $this->assignment->title }}</h1>
            <p class="portal-orders-intro__desc">
                {{ $this->assignment->section?->name }}
                @if ($this->assignment->due_at)
                    · الموعد النهائي: <span dir="ltr">{{ $this->assignment->due_at->translatedFormat('d M Y — H:i') }}</span>
                @endif
                · الدرجة العظمى: {{ $this->assignment->max_score }}
            </p>
        </div>
    </div>

    @if ($portalMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ $portalMessage }}</div>
        </div>
    @endif

    @if ($this->submission?->isGraded())
        <div class="portal-panel portal-panel--graded">
            <div class="portal-panel__body portal-panel__body--padded">
                <h2 class="portal-panel__title"><i class="fa-solid fa-star"></i> نتيجة التقييم</h2>
                <p class="portal-grade-display">
                    <strong>{{ $this->submission->finalScore() }}</strong>
                    <span>/ {{ $this->assignment->max_score }}</span>
                </p>
                @if ($this->submission->feedback)
                    <div class="portal-grade-feedback">
                        <strong>ملاحظات المُقيِّم:</strong>
                        <p>{{ $this->submission->feedback }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            @if ($this->assignment->instructions)
                <section class="portal-panel">
                    <div class="portal-panel__head"><h2 class="portal-panel__title">التعليمات</h2></div>
                    <div class="portal-panel__body portal-panel__body--padded" style="white-space:pre-wrap;">{{ $this->assignment->instructions }}</div>
                </section>
            @endif

            @if ($this->canSubmit)
                <section class="portal-panel">
                    <div class="portal-panel__head"><h2 class="portal-panel__title"><i class="fa-solid fa-upload"></i> تسليم الإجابة</h2></div>
                    <div class="portal-panel__body portal-panel__body--padded">
                        @if ($this->assignment->allow_text_submission)
                            <div class="mb-3">
                                <label class="form-label">الإجابة (نص)</label>
                                <textarea class="form-control" rows="8" wire:model="bodyText"></textarea>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">رابط (اختياري)</label>
                            <input type="url" class="form-control" wire:model="submissionUrl" dir="ltr" placeholder="https://">
                            @error('submissionUrl')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ملفات (حد {{ $this->assignment->max_files }})</label>
                            <input type="file" class="form-control" wire:model="submissionFiles" multiple>
                            @error('submissionFiles')<div class="text-danger small">{{ $message }}</div>@enderror
                            @error('submissionFiles.*')<div class="text-danger small">{{ $message }}</div>@enderror
                            <div class="form-text">PDF, Word, PowerPoint, صور, ZIP — حتى 50MB</div>
                        </div>

                        @if ($this->submission?->files->isNotEmpty())
                            <ul class="portal-material-list mb-3">
                                @foreach ($this->submission->files as $file)
                                    <li class="portal-material-item">
                                        <span>{{ $file->original_name }}</span>
                                        @if ($this->canSubmit)
                                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeFile({{ $file->id }})">حذف</button>
                                        @else
                                            <a href="{{ $file->downloadUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm">تحميل</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" wire:click="submitFinal" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submitFinal">تسليم نهائي</span>
                                <span wire:loading wire:target="submitFinal">جاري التسليم…</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" wire:click="saveDraft" wire:loading.attr="disabled">حفظ مسودة</button>
                        </div>

                        @if ($this->assignment->isOverdue() && $this->assignment->allow_late_submission)
                            <p class="text-warning small mt-2 mb-0"><i class="fa-solid fa-triangle-exclamation"></i> تجاوزت الموعد — سيُسجَّل تسليمك كمتأخر.</p>
                        @endif
                    </div>
                </section>
            @elseif ($this->submission && in_array($this->submission->status, ['submitted', 'late'], true))
                <div class="portal-alert portal-alert--compact">
                    <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
                    <div class="portal-alert__content">
                        <strong>تم التسليم</strong>
                        — {{ AssignmentOptions::submissionStatusLabel($this->submission->status) }}
                        @if ($this->submission->submitted_at)
                            · {{ $this->submission->submitted_at->translatedFormat('d M Y H:i') }}
                        @endif
                    </div>
                </div>
            @elseif ($this->assignment->isClosed())
                <div class="portal-alert portal-alert--compact">
                    <div class="portal-alert__content">هذا الواجب مغلق ولا يقبل تسليمات جديدة.</div>
                </div>
            @endif
        </div>

        <aside class="portal-side-col">
            <div class="portal-widget">
                <h3 class="portal-widget__title">معلومات الواجب</h3>
                <div class="portal-academic-list">
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">الحالة</span>
                        <strong>{{ AssignmentOptions::statusLabel($this->assignment->status) }}</strong>
                    </div>
                    @if ($this->submission)
                        <div class="portal-academic-item">
                            <span class="portal-academic-item__label">تسليمك</span>
                            <strong>{{ AssignmentOptions::submissionStatusLabel($this->submission->status) }}</strong>
                        </div>
                    @endif
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">المحاولات</span>
                        <strong>{{ $this->assignment->max_attempts }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    .portal-grade-display { font-size: 2rem; margin: 0.5rem 0; color: var(--sa-green-dark); }
    .portal-grade-display span { font-size: 1rem; color: var(--sa-muted); }
    .portal-grade-feedback { margin-top: 0.75rem; padding: 0.75rem; background: #f6fbf8; border-radius: 8px; }
    .portal-panel--graded { border-color: rgba(22,93,49,0.2); }
</style>
@endpush
