<?php

use App\Models\AcademicStudent;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\AssignmentService;
use App\Support\AssignmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('تسليمات الواجب | لوحة التحكم')]
class extends Component
{
    public Assignment $assignment;

    /** @var array<int, array{score: string, feedback: string}> */
    public array $gradeForms = [];

    public ?string $savedMessage = null;

    public function mount(Assignment $assignment): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.view'), 403);

        $this->assignment = $assignment->load(['section', 'session']);
    }

    #[Computed]
    public function submissionRows()
    {
        $submissions = AssignmentSubmission::query()
            ->with(['student', 'files'])
            ->where('assignment_id', $this->assignment->id)
            ->whereIn('status', ['submitted', 'late', 'graded', 'returned'])
            ->get()
            ->keyBy('student_id');

        return $this->assignment->section
            ?->students()
            ->orderBy('name_ar')
            ->get()
            ->map(function (AcademicStudent $student) use ($submissions) {
                $submission = $submissions->get($student->id);

                if ($submission && ! isset($this->gradeForms[$submission->id])) {
                    $this->gradeForms[$submission->id] = [
                        'score' => (string) ($submission->score ?? ''),
                        'feedback' => $submission->feedback ?? '',
                    ];
                }

                return [
                    'student' => $student,
                    'submission' => $submission,
                ];
            }) ?? collect();
    }

    #[Computed]
    public function stats(): array
    {
        $total = $this->assignment->section?->students()->count() ?? 0;
        $submitted = AssignmentSubmission::query()
            ->where('assignment_id', $this->assignment->id)
            ->whereIn('status', ['submitted', 'late', 'graded'])
            ->distinct('student_id')
            ->count('student_id');
        $graded = AssignmentSubmission::query()
            ->where('assignment_id', $this->assignment->id)
            ->where('status', 'graded')
            ->count();

        return compact('total', 'submitted', 'graded');
    }

    public function gradeSubmission(int $submissionId, AssignmentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.manage'), 403);

        $form = $this->gradeForms[$submissionId] ?? null;

        if (! $form) {
            return;
        }

        $this->validate([
            "gradeForms.{$submissionId}.score" => ['required', 'integer', 'min:0', 'max:'.$this->assignment->max_score],
        ], [], [
            "gradeForms.{$submissionId}.score" => 'الدرجة',
        ]);

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $this->assignment->id)
            ->findOrFail($submissionId);

        $service->grade(
            $submission,
            (int) $form['score'],
            $form['feedback'] ?: null,
            auth()->user(),
        );

        $this->savedMessage = 'تم حفظ التقييم.';
        unset($this->submissionRows);
    }

    public function closeAssignment(AssignmentService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('assignments.manage'), 403);

        $service->close($this->assignment);
        $this->assignment->refresh();
        $this->savedMessage = 'تم إغلاق الواجب.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.assignments'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.assignments'), 'label' => 'الواجبات'],
        ['label' => $assignment->title],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>{{ $assignment->title }}</h2>
            <p class="admin-crud-card__meta">
                {{ $assignment->section?->name }}
                · {{ AssignmentOptions::scopeLabel($assignment->scope) }}
                · {{ AssignmentOptions::statusLabel($assignment->status) }}
                @if ($assignment->due_at)
                    · الموعد: <span dir="ltr">{{ $assignment->due_at->format('Y-m-d H:i') }}</span>
                @endif
            </p>
        </div>
        <div class="admin-filter-actions" style="margin:0;">
            <a href="{{ route('admin.assignments.edit', $assignment) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
            @if ($assignment->status === 'published')
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeAssignment">إغلاق الواجب</button>
            @endif
        </div>
    </div>

    <div class="student-academic-stats" style="margin-bottom:1rem;">
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $this->stats['submitted'] }}/{{ $this->stats['total'] }}</span>
            <span class="student-academic-stat__label">تسليمات</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $this->stats['graded'] }}</span>
            <span class="student-academic-stat__label">مُقيَّمة</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $assignment->max_score }}</span>
            <span class="student-academic-stat__label">الدرجة العظمى</span>
        </div>
    </div>

    @if ($assignment->instructions)
        <div class="admin-field admin-field--wide" style="margin-bottom:1rem;">
            <label>التعليمات</label>
            <div class="admin-control" style="min-height:4rem;white-space:pre-wrap;">{{ $assignment->instructions }}</div>
        </div>
    @endif
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head"><h2>تسليمات الطلاب</h2></div>
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الحالة</th>
                    <th>التسليم</th>
                    <th>الدرجة</th>
                    <th>ملاحظات المُقيِّم</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->submissionRows as $row)
                    @php
                        $student = $row['student'];
                        $submission = $row['submission'];
                    @endphp
                    <tr wire:key="sub-row-{{ $student->id }}">
                        <td>
                            <a href="{{ route('admin.students.show', $student) }}" class="dash-inline-link">{{ $student->name_ar }}</a>
                            <div class="admin-crud-card__meta"><code>{{ $student->academic_id }}</code></div>
                        </td>
                        <td>
                            @if ($submission)
                                <span @class(['admin-badge', AssignmentOptions::submissionBadgeClass($submission->status)])>
                                    {{ AssignmentOptions::submissionStatusLabel($submission->status) }}
                                </span>
                            @else
                                <span class="admin-badge admin-badge--muted">لم يُسلِّم</span>
                            @endif
                        </td>
                        <td>
                            @if ($submission)
                                <div>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? '—' }}</div>
                                @if ($submission->body_text)
                                    <div class="admin-crud-card__meta">{{ \Illuminate\Support\Str::limit($submission->body_text, 80) }}</div>
                                @endif
                                @foreach ($submission->files as $file)
                                    <a href="{{ $file->downloadUrl() }}" target="_blank" class="dash-inline-link" dir="ltr">{{ $file->original_name }}</a><br>
                                @endforeach
                                @if ($submission->submission_url)
                                    <a href="{{ $submission->submission_url }}" target="_blank" class="dash-inline-link" dir="ltr">رابط</a>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td style="min-width:6rem;">
                            @if ($submission && in_array($submission->status, ['submitted', 'late', 'graded', 'returned'], true))
                                <input type="number"
                                    class="admin-control"
                                    min="0"
                                    max="{{ $assignment->max_score }}"
                                    wire:model="gradeForms.{{ $submission->id }}.score">
                                @error("gradeForms.{$submission->id}.score")
                                    <div class="admin-field-hint is-visible">{{ $message }}</div>
                                @enderror
                                @if ($submission->isGraded() && $submission->finalScore() !== $submission->score)
                                    <div class="admin-crud-card__meta">بعد الخصم: {{ $submission->finalScore() }}</div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td style="min-width:12rem;">
                            @if ($submission && in_array($submission->status, ['submitted', 'late', 'graded', 'returned'], true))
                                <textarea class="admin-control" rows="2" wire:model="gradeForms.{{ $submission->id }}.feedback"></textarea>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($submission && in_array($submission->status, ['submitted', 'late', 'graded', 'returned'], true))
                                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="gradeSubmission({{ $submission->id }})">حفظ</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;padding:1.5rem">لا يوجد طلاب في هذه الشعبة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-crud-card__head--row { display:flex; flex-wrap:wrap; justify-content:space-between; gap:0.75rem; align-items:flex-start; }
    .student-academic-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(8rem,1fr)); gap:0.65rem; }
    .student-academic-stat { padding:0.75rem; border-radius:var(--radius-md); background:var(--sa-mist); border:1px solid var(--sa-border); text-align:center; }
    .student-academic-stat__value { display:block; font-weight:800; color:var(--sa-green-dark); }
    .student-academic-stat__label { font-size:0.72rem; color:var(--sa-muted); }
    .admin-badge--muted { background:#f1f5f9; color:#64748b; }
    .admin-badge--info { background:#eff6ff; color:#1d4ed8; }
    .admin-badge--warn { background:#fff7ed; color:#c2410c; }
</style>
@endpush
