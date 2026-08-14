<?php

use App\Models\AcademicSection;
use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('قائمة طلاب الشعبة | لوحة المدرب')]
class extends Component
{
    public AcademicSection $section;

    #[Url]
    public string $search = '';

    public function mount(AcademicSection $section, InstructorService $instructors): void
    {
        $instructors->authorizeSection(auth()->user(), $section);
        $instructors->authorizePermission(auth()->user(), 'instructor.sections.view_all_students');
        $this->section = $section->load(['course', 'program', 'batch'])->loadCount('students');
    }

    #[Computed]
    public function allStudents()
    {
        return app(InstructorService::class)->rosterForSection($this->section);
    }

    #[Computed]
    public function students()
    {
        $students = $this->allStudents;
        $term = trim($this->search);
        if ($term === '') {
            return $students;
        }

        return $students->filter(function ($student) use ($term) {
            $haystack = mb_strtolower(implode(' ', array_filter([
                $student->name_ar,
                $student->academic_id,
                $student->email,
                $student->mobile,
                $student->national_id,
            ])));

            return str_contains($haystack, mb_strtolower($term));
        })->values();
    }

    #[Computed]
    public function stats(): array
    {
        $students = $this->allStudents;

        return [
            'total' => $students->count(),
            'linked' => $students->filter(fn ($student) => (bool) $student->user)->count(),
            'unlinked' => $students->filter(fn ($student) => ! $student->user)->count(),
        ];
    }

    public function exportRoster(): mixed
    {
        app(InstructorService::class)->authorizePermission(auth()->user(), 'instructor.sections.export_roster');
        $rows = $this->allStudents;

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['الاسم', 'الرقم الأكاديمي', 'الهوية', 'الجوال', 'البريد', 'حالة الدراسة', 'حساب البوابة']);
            foreach ($rows as $student) {
                fputcsv($out, [
                    $student->name_ar,
                    $student->academic_id,
                    $student->national_id,
                    $student->mobile,
                    $student->email,
                    $student->study_status ?: $student->academic_status,
                    $student->user?->email ?: 'غير مرتبط',
                ]);
            }
            fclose($out);
        }, 'roster-'.$this->section->code.'-'.today()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
};
?>

@php
    $locale = app()->getLocale();
    $breadcrumb = [
        ['href' => route('instructor.sections', ['locale' => $locale]), 'label' => 'شعبي'],
        ['href' => route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]), 'label' => $section->name],
        ['label' => 'الطلاب'],
    ];
    $actions = [
        ['href' => route('instructor.sections.show', ['locale' => $locale, 'section' => $section->id]), 'label' => 'عودة للشعبة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ['href' => route('instructor.sections', ['locale' => $locale]), 'label' => 'كل الشعب', 'icon' => 'fa-users-rectangle', 'class' => 'btn-outline-primary'],
    ];
@endphp

@include('partials.instructor.shell-start', [
    'instructorActive' => 'sections',
    'instructorTitle' => 'طلاب '.$section->name,
    'instructorBreadcrumb' => $breadcrumb,
])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'قائمة طلاب الشعبة',
        'desc' => $section->name.' — '.($section->course?->name_ar ?: ($section->program?->name_ar ?: 'بدون مقرر')),
        'icon' => 'fa-id-card',
        'stats' => [
            ['value' => $this->stats['total'], 'label' => 'طلاب'],
            ['value' => $this->stats['linked'], 'label' => 'حساب بوابة'],
            ['value' => $this->stats['unlinked'], 'label' => 'غير مرتبط'],
        ],
        'actions' => $actions,
    ])

    <div class="portal-kpi-strip portal-kpi-strip--3">
        <div class="portal-kpi-v2 portal-kpi-v2--students">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-users"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['total'] }}</span>
                <span class="portal-kpi-v2__label">إجمالي الطلاب</span>
            </span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--week">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-link"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['linked'] }}</span>
                <span class="portal-kpi-v2__label">مرتبط بالبوابة</span>
            </span>
        </div>
        <div class="portal-kpi-v2 portal-kpi-v2--grades">
            <span class="portal-kpi-v2__icon"><i class="fa-solid fa-link-slash"></i></span>
            <span class="portal-kpi-v2__body">
                <span class="portal-kpi-v2__value">{{ $this->stats['unlinked'] }}</span>
                <span class="portal-kpi-v2__label">بدون حساب</span>
            </span>
        </div>
    </div>

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-magnifying-glass"></i> بحث وتصدير</h2>
            <div class="portal-inst-roster-head-actions">
                <span class="portal-panel__meta">{{ $this->students->count() }} نتيجة</span>
                @canInstructor('instructor.sections.export_roster')
                    <button type="button" wire:click="exportRoster" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-file-csv"></i> تصدير CSV
                    </button>
                @endcanInstructor
            </div>
        </div>
        <div class="portal-panel__body portal-panel__body--padded">
            <label class="portal-inst-search portal-inst-search--wide">
                <span>ابحث في الطلاب</span>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="الاسم، الرقم الأكاديمي، الجوال، البريد أو الهوية">
            </label>
            <div class="portal-inst-roster-meta">
                @if ($section->code)
                    <span><i class="fa-solid fa-hashtag"></i> {{ $section->code }}</span>
                @endif
                @if ($section->program?->name_ar)
                    <span><i class="fa-solid fa-graduation-cap"></i> {{ $section->program->name_ar }}</span>
                @endif
                @if ($section->batch?->name)
                    <span><i class="fa-solid fa-layer-group"></i> {{ $section->batch->name }}</span>
                @endif
                @if ($section->displaySemester())
                    <span><i class="fa-solid fa-calendar"></i> {{ $section->displaySemester() }}</span>
                @endif
            </div>
        </div>
    </section>

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-users"></i> الطلاب</h2>
        </div>
        <div class="portal-panel__body">
            @if ($this->students->isEmpty())
                <div class="portal-empty">
                    <div class="portal-empty__icon"><i class="fa-solid fa-user-slash"></i></div>
                    <p>{{ $search !== '' ? 'لا يوجد طلاب مطابقون لبحثك' : 'لا يوجد طلاب في هذه الشعبة' }}</p>
                    <span class="portal-empty__hint">{{ $search !== '' ? 'جرّب كلمات بحث مختلفة' : 'سيظهر الطلاب هنا بعد إضافتهم من الإدارة' }}</span>
                </div>
            @else
                <div class="portal-inst-roster-grid">
                    @foreach ($this->students as $student)
                        <article class="portal-inst-roster-card" wire:key="stu-{{ $student->id }}">
                            <div class="portal-inst-roster-card__head">
                                <span class="portal-inst-roster-card__avatar">{{ mb_substr($student->name_ar ?: 'ط', 0, 1) }}</span>
                                <div>
                                    <h3>{{ $student->name_ar }}</h3>
                                    <span dir="ltr">{{ $student->academic_id ?: 'بدون رقم أكاديمي' }}</span>
                                </div>
                                @if ($student->user)
                                    <span class="portal-inst-badge portal-inst-badge--att-present">مرتبط</span>
                                @else
                                    <span class="portal-inst-badge">غير مرتبط</span>
                                @endif
                            </div>
                            <div class="portal-inst-roster-card__meta">
                                @if ($student->mobile)
                                    <span dir="ltr"><i class="fa-solid fa-phone"></i> {{ $student->mobile }}</span>
                                @endif
                                @if ($student->email)
                                    <span dir="ltr"><i class="fa-solid fa-envelope"></i> {{ $student->email }}</span>
                                @endif
                                @if ($student->national_id)
                                    <span dir="ltr"><i class="fa-solid fa-id-card"></i> {{ $student->national_id }}</span>
                                @endif
                                <span><i class="fa-solid fa-circle-info"></i> {{ $student->study_status ?: ($student->academic_status ?: 'بدون حالة') }}</span>
                                @if ($student->user?->email)
                                    <span dir="ltr"><i class="fa-solid fa-user"></i> {{ $student->user->email }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

@push('styles')
<style>
.portal-kpi-strip--3{grid-template-columns:repeat(3,minmax(0,1fr))}
.portal-dashboard--instructor .portal-kpi-v2--students{border-right-color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--week{border-right-color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--grades{border-right-color:#d97706}
.portal-dashboard--instructor .portal-kpi-v2--students .portal-kpi-v2__icon{background:#eff6ff;color:#2563eb}
.portal-dashboard--instructor .portal-kpi-v2--week .portal-kpi-v2__icon{background:#ecfdf5;color:#059669}
.portal-dashboard--instructor .portal-kpi-v2--grades .portal-kpi-v2__icon{background:#fffbeb;color:#d97706}
.portal-inst-roster-head-actions{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}
.portal-inst-search--wide{display:grid;gap:.35rem;width:100%}
.portal-inst-search--wide span{font-size:.72rem;font-weight:800;color:#64748b}
.portal-inst-search--wide input{width:100%;border:1px solid #dbe4ee;border-radius:12px;padding:.85rem 1rem;background:#fff;font-size:.92rem}
.portal-inst-roster-meta{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.85rem}
.portal-inst-roster-meta span{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .6rem;border-radius:999px;background:#f8fafc;border:1px solid #e8eef3;color:#334155;font-size:.72rem;font-weight:700}
.portal-inst-roster-meta i{color:#1b8354;font-size:.68rem}
.portal-inst-roster-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.85rem}
.portal-inst-roster-card{display:grid;gap:.75rem;padding:1rem;border:1px solid rgba(27,131,84,.14);border-radius:16px;background:#fff;box-shadow:0 4px 16px rgba(15,81,50,.05)}
.portal-inst-roster-card__head{display:flex;align-items:center;gap:.75rem}
.portal-inst-roster-card__avatar{width:2.6rem;height:2.6rem;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0f5132,#1b8354);color:#fff;font-weight:900;flex-shrink:0}
.portal-inst-roster-card__head h3{margin:0;font-size:.92rem;font-weight:900;color:#0f172a;line-height:1.35}
.portal-inst-roster-card__head>div{flex:1;min-width:0}
.portal-inst-roster-card__head>div>span{display:block;font-size:.72rem;color:#64748b;font-weight:700;margin-top:.15rem}
.portal-inst-roster-card__meta{display:flex;flex-wrap:wrap;gap:.4rem}
.portal-inst-roster-card__meta span{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .55rem;border-radius:999px;background:#f8fafc;border:1px solid #e8eef3;color:#475569;font-size:.7rem;font-weight:700;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.portal-inst-roster-card__meta i{color:#1b8354;font-size:.65rem}
@media(max-width:767.98px){.portal-kpi-strip--3{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.instructor.shell-end')
