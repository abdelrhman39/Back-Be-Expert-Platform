<?php

use App\Services\InstructorService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('شعبي | لوحة المدرب')]
class extends Component
{
    #[Url]
    public string $search = '';

    public function mount(InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.sections.view');
    }

    #[Computed]
    public function allSections()
    {
        return app(InstructorService::class)->sectionsFor(auth()->user());
    }

    #[Computed]
    public function sections()
    {
        $sections = $this->allSections;
        $term = trim($this->search);
        if ($term === '') {
            return $sections;
        }

        return $sections->filter(function ($section) use ($term) {
            $haystack = mb_strtolower(implode(' ', array_filter([
                $section->name,
                $section->code,
                $section->course?->name_ar,
                $section->program?->name_ar,
            ])));

            return str_contains($haystack, mb_strtolower($term));
        })->values();
    }

    #[Computed]
    public function stats(): array
    {
        $sections = $this->allSections;

        return [
            'sections' => $sections->count(),
            'students' => (int) $sections->sum('students_count'),
            'programs' => $sections->pluck('program_id')->filter()->unique()->count(),
        ];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.instructor.shell-start', ['instructorActive' => 'sections', 'instructorTitle' => 'شعبي'])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'شعبي الدراسية',
        'desc' => 'كل الشعب المسندة إليك مع اختصارات للطلاب والحصص والاختبارات.',
        'icon' => 'fa-users-rectangle',
        'stats' => [
            ['value' => $this->stats['sections'], 'label' => 'شعب'],
            ['value' => $this->stats['students'], 'label' => 'طلاب'],
            ['value' => $this->stats['programs'], 'label' => 'برامج'],
        ],
        'actions' => array_filter([
            auth()->user()?->canInstructor('instructor.exams.create') && $this->allSections->isNotEmpty()
                ? ['href' => route('instructor.exams.create', ['locale' => $locale, 'section' => $this->allSections->first()->id]), 'label' => 'اختبار جديد', 'icon' => 'fa-plus', 'class' => 'btn-primary']
                : null,
            ['href' => route('instructor.dashboard', ['locale' => $locale]), 'label' => 'العودة للوحة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ]),
    ])

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-magnifying-glass"></i> بحث وفلترة</h2>
            <span class="portal-panel__meta">{{ $this->sections->count() }} نتيجة</span>
        </div>
        <div class="portal-panel__body portal-panel__body--padded">
            <label class="portal-inst-search portal-inst-search--wide">
                <span>ابحث في الشعب</span>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="اسم الشعبة، الكود، المقرر أو البرنامج">
            </label>
        </div>
    </section>

    <section class="portal-panel">
        <div class="portal-panel__head">
            <h2 class="portal-panel__title"><i class="fa-solid fa-users-rectangle"></i> قائمة الشعب</h2>
        </div>
        <div class="portal-panel__body">
            @if ($this->sections->isEmpty())
                <div class="portal-empty">
                    <div class="portal-empty__icon"><i class="fa-solid fa-users-slash"></i></div>
                    <p>{{ $search !== '' ? 'لا توجد شعب مطابقة لبحثك' : 'لا توجد شعب مسندة إليك بعد' }}</p>
                    <span class="portal-empty__hint">{{ $search !== '' ? 'جرّب كلمات بحث مختلفة' : 'تواصل مع الإدارة لربط حسابك بجدول تدريبي' }}</span>
                </div>
            @else
                <div class="portal-inst-sec-grid">
                    @foreach ($this->sections as $section)
                        <div wire:key="sec-list-{{ $section->id }}">
                            @include('partials.instructor.section-card', ['section' => $section, 'locale' => $locale, 'showActions' => true])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

@push('styles')
<style>
.portal-hero--page .portal-hero__banner--compact{min-height:auto}
.portal-hero--page .portal-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.78);margin-bottom:.35rem}
.portal-hero--page .portal-hero__body--compact{margin-top:-1.1rem;padding-top:0;padding-bottom:1rem}
.portal-hero--page .portal-hero__actions--start{justify-content:flex-start}
.portal-panel__meta{font-size:.78rem;font-weight:800;color:#0f766e;background:#ecfdf5;padding:.3rem .7rem;border-radius:999px}
.portal-inst-search--wide{display:grid;gap:.35rem;width:100%}
.portal-inst-search--wide span{font-size:.72rem;font-weight:800;color:#64748b}
.portal-inst-search--wide input{width:100%;border:1px solid #dbe4ee;border-radius:12px;padding:.85rem 1rem;background:#fff;font-size:.92rem}
</style>
@endpush

@include('partials.instructor.shell-end')
