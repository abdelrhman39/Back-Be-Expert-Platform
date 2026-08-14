<?php

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamIntegrityService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مراقبة نزاهة الاختبار | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public Exam $exam;

    #[Url]
    public string $search = '';

    #[Url]
    public string $risk = '';

    #[Url]
    public string $reviewStatus = '';

    public ?int $selectedAttemptId = null;
    public string $reviewNotes = '';
    public string $flashMessage = '';

    public function mount(Exam $exam): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $this->exam = $exam->load(['section', 'course']);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRisk(): void
    {
        $this->resetPage();
    }

    public function updatedReviewStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function attempts()
    {
        return ExamAttempt::query()
            ->with(['student.user', 'integrityReviewer'])
            ->withCount(['events as integrity_events_count' => fn ($query) => $query
                ->whereIn('event_type', array_keys(ExamIntegrityService::EVENT_WEIGHTS))])
            ->where('exam_id', $this->exam->id)
            ->when($this->search, fn ($query) => $query->whereHas('student', function ($student) {
                $student->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('academic_id', 'like', '%'.$this->search.'%');
            }))
            ->when($this->reviewStatus, fn ($query) => $query->where('integrity_review_status', $this->reviewStatus))
            ->when($this->risk, fn ($query) => match ($this->risk) {
                'clean' => $query->where('integrity_flags', 0),
                'low' => $query->whereBetween('integrity_flags', [1, 2]),
                'medium' => $query->whereBetween('integrity_flags', [3, 5]),
                'high' => $query->whereBetween('integrity_flags', [6, 9]),
                'critical' => $query->where('integrity_flags', '>=', 10),
                default => $query,
            })
            ->latest('started_at')
            ->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        $base = ExamAttempt::query()->where('exam_id', $this->exam->id);

        return [
            'total' => (clone $base)->count(),
            'flagged' => (clone $base)->where('integrity_flags', '>', 0)->count(),
            'high' => (clone $base)->where('integrity_flags', '>=', 6)->count(),
            'unreviewed' => (clone $base)
                ->where('integrity_flags', '>', 0)
                ->where('integrity_review_status', 'unreviewed')
                ->count(),
        ];
    }

    #[Computed]
    public function selectedAttempt(): ?ExamAttempt
    {
        if (! $this->selectedAttemptId) {
            return null;
        }

        return ExamAttempt::query()
            ->with([
                'student.user',
                'integrityReviewer',
                'events' => fn ($query) => $query->latest('occurred_at'),
            ])
            ->where('exam_id', $this->exam->id)
            ->find($this->selectedAttemptId);
    }

    public function openAttempt(int $attemptId): void
    {
        $attempt = ExamAttempt::query()
            ->where('exam_id', $this->exam->id)
            ->findOrFail($attemptId);
        $this->selectedAttemptId = $attempt->id;
        $this->reviewNotes = $attempt->integrity_review_notes ?? '';
        unset($this->selectedAttempt);
    }

    public function closeAttempt(): void
    {
        $this->selectedAttemptId = null;
        $this->reviewNotes = '';
        unset($this->selectedAttempt);
    }

    public function review(string $status, ExamIntegrityService $integrity): void
    {
        abort_unless(auth()->user()?->canAdmin('exams.manage'), 403);
        $attempt = ExamAttempt::query()
            ->where('exam_id', $this->exam->id)
            ->findOrFail($this->selectedAttemptId);
        $this->validate(['reviewNotes' => ['nullable', 'string', 'max:5000']]);
        $integrity->review($attempt, auth()->user(), $status, $this->reviewNotes);
        unset($this->attempts, $this->stats, $this->selectedAttempt);
        $this->flashMessage = $status === 'cleared'
            ? 'تمت مراجعة المحاولة واعتماد سلامتها.'
            : 'تم تأكيد ملاحظة النزاهة وحفظ قرار المراجع.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.exams'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.exams'), 'label' => 'الاختبارات'],
        ['label' => 'مراقبة النزاهة'],
    ],
])

<div class="integrity-hero">
    <div><span><i class="fa-solid fa-shield-halved"></i> مركز المتابعة</span><h1>نزاهة اختبار «{{ $exam->title }}»</h1><p>{{ $exam->course?->name_ar }} · {{ $exam->section?->name }}</p></div>
    <div class="integrity-hero__actions">
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-integrity-help'))" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-regular fa-circle-question"></i> شرح آلية العمل</button>
        <a href="{{ route('admin.exams.preview', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-eye"></i> المعاينة والطلاب</a>
        <a href="{{ route('admin.exams.builder', $exam) }}" class="admin-btn-secondary admin-btn-secondary--sm">الأسئلة</a>
    </div>
</div>

@if($flashMessage)<div class="admin-alert admin-alert--success is-visible">{{ $flashMessage }}</div>@endif

<div class="integrity-kpis">
    <div><i class="fa-solid fa-users-viewfinder"></i><strong>{{ $this->stats['total'] }}</strong><span>إجمالي المحاولات</span></div>
    <div class="is-info"><i class="fa-solid fa-flag"></i><strong>{{ $this->stats['flagged'] }}</strong><span>بها إشارات</span></div>
    <div class="is-danger"><i class="fa-solid fa-triangle-exclamation"></i><strong>{{ $this->stats['high'] }}</strong><span>خطورة مرتفعة</span></div>
    <div class="is-warning"><i class="fa-solid fa-clipboard-question"></i><strong>{{ $this->stats['unreviewed'] }}</strong><span>بانتظار المراجعة</span></div>
</div>

<section class="admin-crud-card integrity-filters">
    <label><span>البحث عن طالب</span><input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="الاسم أو الرقم الأكاديمي"></label>
    <label><span>مستوى الخطورة</span><select class="admin-control" wire:model.live="risk"><option value="">كل المستويات</option><option value="clean">سليم</option><option value="low">منخفض</option><option value="medium">متوسط</option><option value="high">مرتفع</option><option value="critical">حرج</option></select></label>
    <label><span>قرار المراجعة</span><select class="admin-control" wire:model.live="reviewStatus"><option value="">كل الحالات</option><option value="unreviewed">غير مراجع</option><option value="cleared">سليم بعد المراجعة</option><option value="confirmed">مخالفة مؤكدة</option></select></label>
</section>

<section class="admin-crud-card integrity-table-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table integrity-table">
            <thead><tr><th>الطالب</th><th>المحاولة</th><th>الوقت</th><th>الإشارات</th><th>الخطورة</th><th>قرار المراجعة</th><th>التفاصيل</th></tr></thead>
            <tbody>
                @forelse($this->attempts as $attempt)
                    @php
                        $riskInfo = app(ExamIntegrityService::class)->risk((int) $attempt->integrity_flags);
                    @endphp
                    <tr wire:key="integrity-attempt-{{ $attempt->id }}">
                        <td><strong>{{ $attempt->student?->name_ar ?? '—' }}</strong><small>{{ $attempt->student?->academic_id }}</small></td>
                        <td><strong>#{{ $attempt->attempt_number }}</strong><small>{{ match($attempt->status){'in_progress'=>'جارية','pending_grading'=>'بانتظار التصحيح','graded'=>'مصححة',default=>$attempt->status} }}</small></td>
                        <td>{{ $attempt->started_at?->format('Y/m/d H:i') }}<small>{{ $attempt->ip_address ?: 'IP غير متاح' }}</small></td>
                        <td><strong>{{ $attempt->integrity_events_count }}</strong><small>درجة الإشارة {{ $attempt->integrity_flags }}</small></td>
                        <td><span class="integrity-risk is-{{ $riskInfo['key'] }}">{{ $riskInfo['label'] }}</span></td>
                        <td><span class="integrity-review is-{{ $attempt->integrity_review_status }}">{{ match($attempt->integrity_review_status){'cleared'=>'سليم','confirmed'=>'مؤكدة',default=>'غير مراجع'} }}</span>@if($attempt->integrityReviewer)<small>{{ $attempt->integrityReviewer->displayName() }}</small>@endif</td>
                        <td><button type="button" wire:click="openAttempt({{ $attempt->id }})" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-timeline"></i> السجل</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="integrity-empty"><i class="fa-solid fa-shield-circle-check"></i><span>لا توجد محاولات مطابقة.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="integrity-pagination">{{ $this->attempts->links() }}</div>
</section>

@if($this->selectedAttempt)
    @php
        $selected = $this->selectedAttempt;
        $selectedRisk = app(ExamIntegrityService::class)->risk((int)$selected->integrity_flags);
    @endphp
    <div class="integrity-drawer-backdrop" wire:click.self="closeAttempt">
        <aside class="integrity-drawer">
            <header><div><span>سجل المحاولة #{{ $selected->attempt_number }}</span><h2>{{ $selected->student?->name_ar }}</h2></div><button type="button" wire:click="closeAttempt">×</button></header>
            <div class="integrity-drawer__summary">
                <div><span>مستوى الخطورة</span><strong class="integrity-risk is-{{ $selectedRisk['key'] }}">{{ $selectedRisk['label'] }}</strong></div>
                <div><span>درجة الإشارات</span><strong>{{ $selected->integrity_flags }}</strong></div>
                <div><span>عنوان IP</span><strong>{{ $selected->ip_address ?: '—' }}</strong></div>
            </div>
            <div class="integrity-timeline">
                <h3>التسلسل الزمني</h3>
                @forelse($selected->events as $event)
                    @php
                        $isRiskEvent = array_key_exists($event->event_type, ExamIntegrityService::EVENT_WEIGHTS);
                        $eventMetadata = is_array($event->metadata) ? $event->metadata : [];
                    @endphp
                    <article @class(['is-risk' => $isRiskEvent])>
                        <i class="fa-solid {{ $isRiskEvent ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
                        <div>
                            <strong>{{ app(ExamIntegrityService::class)->eventLabel($event->event_type) }}</strong>
                            <span>{{ $event->occurred_at?->format('H:i:s · Y/m/d') }}</span>
                            @if (array_key_exists('question_index', $eventMetadata))
                                <small>عند السؤال {{ ((int) $eventMetadata['question_index']) + 1 }}</small>
                            @endif
                        </div>
                    </article>
                @empty
                    <p>لا توجد أحداث مسجلة.</p>
                @endforelse
            </div>
            <div class="integrity-review-form">
                <label><span>ملاحظات المراجع</span><textarea class="admin-control" rows="4" wire:model="reviewNotes" placeholder="اكتب سبب القرار أو الملاحظة..."></textarea></label>
                <div><button type="button" wire:click="review('cleared')" class="admin-btn-secondary admin-btn-secondary--sm"><i class="fa-solid fa-shield-circle-check"></i> اعتمادها سليمة</button><button type="button" wire:click="review('confirmed')" class="admin-btn-primary admin-btn-primary--sm integrity-confirm"><i class="fa-solid fa-triangle-exclamation"></i> تأكيد المخالفة</button></div>
            </div>
        </aside>
    </div>
@endif

<div class="integrity-help" x-data="{open:false}" x-show="open" x-cloak @open-integrity-help.window="open=true" @keydown.escape.window="open=false" @click.self="open=false">
    <div class="integrity-help__dialog">
        <header><div><span>دليل آلية العمل</span><h2>كيف تعمل مراقبة نزاهة الاختبار؟</h2></div><button type="button" @click="open=false">×</button></header>
        <div class="integrity-help__content">
            <article><i class="fa-solid fa-eye-slash"></i><div><h3>مغادرة الصفحة</h3><p>تُسجل عند انتقال الطالب لتبويب أو نافذة أخرى، وتضيف نقطة خطورة واحدة.</p></div></article>
            <article><i class="fa-solid fa-expand"></i><div><h3>الخروج من ملء الشاشة</h3><p>يضيف نقطتين عند الخروج من وضع ملء الشاشة أثناء المحاولة.</p></div></article>
            <article><i class="fa-solid fa-copy"></i><div><h3>النسخ واللصق</h3><p>كل محاولة نسخ أو لصق تضيف نقطتين. تُمنع الأحداث المتكررة خلال ثانيتين من تضخيم النتيجة.</p></div></article>
            <article><i class="fa-solid fa-gauge-high"></i><div><h3>مستوى الخطورة</h3><p>1–2 منخفض، 3–5 متوسط، 6–9 مرتفع، و10 فأكثر حرج. النتيجة مؤشر للمراجعة وليست حكماً آلياً.</p></div></article>
            <article><i class="fa-solid fa-clipboard-check"></i><div><h3>قرار المراجع</h3><p>افتح سجل المحاولة، راجع التوقيت والسؤال وعنوان IP، ثم اعتمد المحاولة سليمة أو أكد المخالفة مع كتابة ملاحظات.</p></div></article>
            <article><i class="fa-solid fa-rotate"></i><div><h3>إعادة المراجعة</h3><p>إذا سُجل حدث جديد بعد القرار، تعود المحاولة تلقائياً إلى حالة «غير مراجع».</p></div></article>
        </div>
        <footer><i class="fa-solid fa-circle-info"></i><span>الإشارات التقنية تساعد القرار البشري ولا تثبت الغش وحدها؛ يجب مراجعة السياق قبل اتخاذ أي إجراء.</span></footer>
    </div>
</div>

@push('styles')
<style>
    .integrity-hero{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem;padding:1.15rem 1.3rem;border-radius:17px;background:linear-gradient(135deg,#122f26,#1b684a);color:#fff}.integrity-hero>div>span{color:#9ce5bc;font-size:.62rem;font-weight:900}.integrity-hero h1{margin:.25rem 0;color:#fff;font-size:1.2rem}.integrity-hero p{margin:0;color:#c3dbcf;font-size:.66rem}.integrity-hero__actions{display:flex;flex-wrap:wrap;gap:.4rem}.integrity-hero .admin-btn-secondary{border-color:rgba(255,255,255,.25);background:rgba(255,255,255,.09);color:#fff}
    .integrity-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-bottom:1rem}.integrity-kpis>div{display:grid;grid-template-columns:auto 1fr;align-items:center;gap:.1rem .55rem;padding:.8rem;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.integrity-kpis i{grid-row:1/3;display:grid;place-items:center;width:2.2rem;height:2.2rem;border-radius:9px;background:#ecfdf5;color:#166534}.integrity-kpis strong{font-size:1rem}.integrity-kpis span{color:#64748b;font-size:.57rem}.integrity-kpis .is-danger i{background:#fef2f2;color:#b91c1c}.integrity-kpis .is-warning i{background:#fff7ed;color:#c2410c}.integrity-kpis .is-info i{background:#eff6ff;color:#1d4ed8}
    .integrity-filters{display:grid;grid-template-columns:1fr 12rem 13rem;gap:.6rem;margin-bottom:1rem}.integrity-filters label{display:flex;flex-direction:column;gap:.25rem}.integrity-filters label>span{color:#64748b;font-size:.58rem;font-weight:800}.integrity-table-card{padding:0;overflow:hidden}.integrity-table td small{display:block;margin-top:.15rem;color:#64748b;font-size:.54rem}.integrity-risk,.integrity-review{display:inline-flex;padding:.22rem .4rem;border-radius:999px;font-size:.55rem;font-weight:900}.integrity-risk.is-clean,.integrity-review.is-cleared{background:#dcfce7;color:#166534}.integrity-risk.is-low{background:#eff6ff;color:#1d4ed8}.integrity-risk.is-medium{background:#fef3c7;color:#92400e}.integrity-risk.is-high,.integrity-risk.is-critical,.integrity-review.is-confirmed{background:#fee2e2;color:#b91c1c}.integrity-review.is-unreviewed{background:#f1f5f9;color:#64748b}.integrity-empty{text-align:center!important;padding:2rem!important;color:#64748b}.integrity-empty i{margin-inline-end:.35rem}.integrity-pagination{padding:.75rem}
    .integrity-drawer-backdrop{position:fixed;z-index:9999;inset:0;background:rgba(7,23,16,.58);backdrop-filter:blur(3px)}.integrity-drawer{position:absolute;inset-block:0;inset-inline-end:0;width:min(31rem,94vw);overflow-y:auto;background:#fff;box-shadow:-20px 0 50px rgba(0,0,0,.18)}.integrity-drawer>header{display:flex;align-items:center;justify-content:space-between;padding:1rem;background:#123b2a;color:#fff}.integrity-drawer header span{color:#9ce5bc;font-size:.57rem}.integrity-drawer header h2{margin:.15rem 0;color:#fff;font-size:.95rem}.integrity-drawer header button{border:0;background:transparent;color:#fff;font-size:1.3rem}.integrity-drawer__summary{display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem;padding:.75rem;border-bottom:1px solid #e2e8f0}.integrity-drawer__summary>div{display:flex;align-items:center;flex-direction:column;padding:.55rem;border-radius:8px;background:#f8fafc}.integrity-drawer__summary span{color:#64748b;font-size:.52rem}.integrity-drawer__summary strong{margin-top:.2rem;font-size:.63rem}.integrity-timeline{padding:.9rem}.integrity-timeline h3{margin:0 0 .65rem;font-size:.76rem}.integrity-timeline article{display:flex;align-items:flex-start;gap:.5rem;padding:.55rem;border-inline-start:2px solid #bbf7d0}.integrity-timeline article.is-risk{border-color:#fecaca;background:#fffafa}.integrity-timeline article>i{margin-top:.15rem;color:#16a34a}.integrity-timeline article.is-risk>i{color:#dc2626}.integrity-timeline article div{display:flex;flex-direction:column}.integrity-timeline strong{font-size:.63rem}.integrity-timeline span,.integrity-timeline small{color:#64748b;font-size:.52rem}.integrity-review-form{padding:.9rem;border-top:1px solid #e2e8f0}.integrity-review-form label{display:flex;flex-direction:column;gap:.3rem}.integrity-review-form label span{font-size:.62rem;font-weight:900}.integrity-review-form>div{display:flex;justify-content:flex-end;gap:.4rem;margin-top:.55rem}.integrity-confirm{background:#b91c1c!important}
    .integrity-help[x-cloak]{display:none!important}.integrity-help{position:fixed;z-index:10000;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(7,23,16,.72);backdrop-filter:blur(5px)}.integrity-help__dialog{width:min(48rem,96vw);overflow:hidden;border-radius:16px;background:#fff;box-shadow:0 28px 70px rgba(0,0,0,.3)}.integrity-help header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.15rem;background:linear-gradient(135deg,#0d3324,#196044);color:#fff}.integrity-help header span{color:#8fe0b4;font-size:.58rem;font-weight:900}.integrity-help header h2{margin:.15rem 0;color:#fff;font-size:1rem}.integrity-help header button{border:0;background:transparent;color:#fff;font-size:1.3rem}.integrity-help__content{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;padding:1rem}.integrity-help__content article{display:flex;gap:.55rem;padding:.7rem;border:1px solid #e2e8f0;border-radius:10px}.integrity-help__content article>i{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:8px;background:#ecfdf5;color:#166534}.integrity-help__content h3{margin:0;font-size:.7rem}.integrity-help__content p{margin:.2rem 0 0;color:#64748b;font-size:.6rem;line-height:1.65}.integrity-help footer{display:flex;gap:.4rem;padding:.75rem 1rem;background:#fffbeb;color:#854d0e;font-size:.62rem}
    @media(max-width:850px){.integrity-hero{align-items:flex-start;flex-direction:column}.integrity-kpis{grid-template-columns:1fr 1fr}.integrity-filters{grid-template-columns:1fr}.integrity-help__content{grid-template-columns:1fr}.integrity-help__dialog{max-height:94vh;overflow-y:auto}}@media(max-width:520px){.integrity-kpis{grid-template-columns:1fr}.integrity-drawer__summary{grid-template-columns:1fr}.integrity-review-form>div{flex-direction:column}}
</style>
@endpush

@include('partials.admin.shell-end')
