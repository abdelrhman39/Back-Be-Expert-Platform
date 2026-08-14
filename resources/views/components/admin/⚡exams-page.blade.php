<?php

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Services\ExamPublicationService;
use App\Support\ExamOptions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الاختبارات | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public string $flashMessage = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function exams()
    {
        return Exam::query()
            ->with(['section', 'course', 'creator'])
            ->withCount(['attempts', 'attempts as pending_grading_count' => fn ($query) => $query->where('status', 'pending_grading')])
            ->when($this->search, fn ($query) => $query->where(function ($nested) {
                $nested->where('title', 'like', '%'.$this->search.'%')
                    ->orWhereHas('course', fn ($course) => $course->where('name_ar', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('section', fn ($section) => $section->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'exams' => Exam::query()->count(),
            'published' => Exam::query()->where('status', 'published')->count(),
            'questions' => ExamQuestion::query()->where('status', 'published')->count(),
            'attempts' => ExamAttempt::query()->count(),
            'pending' => ExamAttempt::query()->where('status', 'pending_grading')->count(),
        ];
    }

    public function setStatus(int $examId, string $status): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        abort_unless(in_array($status, ['published', 'closed', 'archived'], true), 422);

        $exam = Exam::query()->findOrFail($examId);

        if ($status === 'published') {
            $publication = app(ExamPublicationService::class)->publish($exam, auth()->user());
            unset($this->exams, $this->stats);
            $this->flashMessage = "تم نشر النسخة {$publication->version} بعد اجتياز فحص الجاهزية.";
            return;
        }

        $oldStatus = $exam->status;

        DB::transaction(function () use ($exam, $status, $oldStatus): void {
            $exam->update([
                'status' => $status,
                'published_at' => $exam->published_at,
                'archived_at' => $status === 'archived' ? now() : null,
            ]);
        });

        app(\App\Services\AuditLogService::class)->log(
            action: 'exam.status_changed',
            descriptionAr: 'تغيير حالة اختبار «'.$exam->title.'»',
            group: 'exams',
            subject: $exam,
            subjectLabel: $exam->title,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status],
        );

        unset($this->exams, $this->stats);
        $this->flashMessage = 'تم تحديث حالة الاختبار.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.exams'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الاختبارات وبنوك الأسئلة'],
    ],
])

<div class="admin-page-header">
    <div><h1>الاختبارات وبنوك الأسئلة</h1><p>إشراف مركزي على الاختبارات، المحاولات، التصحيح والنتائج.</p></div>
    @canAdmin('exams.manage')
        <a href="{{ route('admin.exams.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ اختبار جديد</a>
    @endcanAdmin
</div>

@if ($flashMessage)<div class="admin-alert admin-alert--success is-visible">{{ $flashMessage }}</div>@endif
@error('statusChange')<div class="admin-alert admin-alert--danger is-visible">{{ $message }}</div>@enderror

<div class="exam-admin-kpis">
    <div><strong>{{ $this->stats['exams'] }}</strong><span>اختبار</span></div>
    <div><strong>{{ $this->stats['published'] }}</strong><span>منشور</span></div>
    <div><strong>{{ $this->stats['questions'] }}</strong><span>سؤال في البنك</span></div>
    <div><strong>{{ $this->stats['attempts'] }}</strong><span>محاولة</span></div>
    <div class="is-warn"><strong>{{ $this->stats['pending'] }}</strong><span>بانتظار التصحيح</span></div>
</div>

<section class="admin-crud-card">
    <div class="admin-filter-grid" style="grid-template-columns:minmax(15rem,1fr) 14rem">
        <label class="admin-field"><span>البحث</span><input type="search" class="admin-control" wire:model.live.debounce.350ms="search" placeholder="العنوان، المقرر أو الشعبة"></label>
        <label class="admin-field"><span>الحالة</span><select class="admin-control" wire:model.live="status"><option value="">كل الحالات</option>@foreach(ExamOptions::statuses() as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead><tr><th>الاختبار</th><th>المقرر والشعبة</th><th>المنشئ</th><th>الحالة</th><th>الدرجة/المدة</th><th>المحاولات</th><th>التصحيح</th><th>إجراء</th></tr></thead>
            <tbody>
                @forelse($this->exams as $exam)
                    <tr wire:key="admin-exam-{{ $exam->id }}">
                        <td><strong>{{ $exam->title }}</strong><small style="display:block;color:#64748b">{{ ExamOptions::examTypes()[$exam->type] ?? $exam->type }}</small></td>
                        <td>{{ $exam->course?->name_ar ?? '—' }}<small style="display:block;color:#64748b">{{ $exam->section?->name }}</small></td>
                        <td>{{ $exam->creator?->displayName() ?? '—' }}</td>
                        <td><span @class(['admin-badge','admin-badge--success'=>$exam->status==='published','admin-badge--danger'=>in_array($exam->status,['closed','archived'])])>{{ ExamOptions::statusLabel($exam->status) }}</span></td>
                        <td>{{ $exam->total_points }} درجة<br><small>{{ $exam->duration_minutes ? $exam->duration_minutes.' دقيقة' : 'مفتوح' }}</small></td>
                        <td>{{ $exam->attempts_count }}</td>
                        <td>@if($exam->pending_grading_count)<span class="admin-badge admin-badge--warning">{{ $exam->pending_grading_count }} معلقة</span>@else — @endif</td>
                        <td>
                            @canAdmin('exams.manage')
                                <div class="exam-admin-links">
                                    <a href="{{ route('admin.exams.builder', $exam) }}" class="admin-link">الأسئلة</a>
                                    <a href="{{ route('admin.exams.preview', $exam) }}" class="admin-link">المعاينة والفحص</a>
                                    <a href="{{ route('admin.exams.integrity', $exam) }}" class="admin-link">النزاهة</a>
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="admin-link">الإعدادات</a>
                                </div>
                                <select class="admin-control exam-admin-action" wire:change="setStatus({{ $exam->id }}, $event.target.value)">
                                    <option value="">تغيير الحالة</option>
                                    <option value="published">نشر/إعادة فتح</option>
                                    <option value="closed">إغلاق</option>
                                    <option value="archived">أرشفة</option>
                                </select>
                            @else
                                —
                            @endcanAdmin
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;padding:2rem">لا توجد نتائج مطابقة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem">{{ $this->exams->links() }}</div>
</section>

@push('styles')
<style>
    .exam-admin-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.65rem;margin-bottom:1rem}.exam-admin-kpis>div{display:flex;flex-direction:column;padding:.85rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.exam-admin-kpis strong{font-size:1.2rem;color:#123b2a}.exam-admin-kpis span{font-size:.68rem;color:#64748b}.exam-admin-kpis .is-warn{background:#fff7ed}.exam-admin-links{display:flex;gap:.45rem;margin-bottom:.4rem;font-size:.68rem}.exam-admin-action{min-width:8rem;padding:.35rem!important;font-size:.68rem!important}@media(max-width:900px){.exam-admin-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endpush

@include('partials.admin.shell-end')
