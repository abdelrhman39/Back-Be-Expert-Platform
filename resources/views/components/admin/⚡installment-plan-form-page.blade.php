<?php

use App\Models\AcademicProgram;
use App\Models\CatalogCourse;
use App\Models\InstallmentPlanTemplate;
use App\Models\InstallmentPlanTemplateItem;
use App\Services\CatalogCourseService;
use App\Support\InstallmentOptions;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('خطة تقسيط | لوحة التحكم')]
class extends Component
{
    public ?int $planId = null;

    public string $slug = '';

    public string $nameAr = '';

    public string $nameEn = '';

    public string $programType = '';

    public int $maxInstallments = 12;

    public float $minDownPercent = 25;

    public bool $isActive = true;

    public string $descriptionAr = '';

    /** @var array<int, string> */
    public array $linkedProgramIds = [];

    /** @var array<int, string> */
    public array $linkedCourseIds = [];

    public string $programSearch = '';

    public string $courseSearch = '';

    /** @var array<int, array{sequence: int, percent: float, due_rule: string, month_offset: int|null, label_ar: string}> */
    public array $items = [];

    public ?string $savedMessage = null;

    public string $toastKey = '';

    public function mount(?InstallmentPlanTemplate $plan = null): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        if ($plan) {
            $this->planId = $plan->id;
            $this->slug = $plan->slug;
            $this->nameAr = $plan->name_ar;
            $this->nameEn = $plan->name_en ?? '';
            $this->programType = $plan->program_type ?? '';
            $this->maxInstallments = $plan->max_installments;
            $this->isActive = $plan->is_active;
            $this->descriptionAr = $plan->description_ar ?? '';
            $this->linkedProgramIds = $plan->academicPrograms()->pluck('academic_programs.id')->map(fn ($id) => (string) $id)->all();
            $this->linkedCourseIds = $plan->catalogCourses()->pluck('catalog_courses.id')->map(fn ($id) => (string) $id)->all();
            $this->items = $plan->items->map(fn ($i) => [
                'sequence' => $i->sequence,
                'percent' => (float) $i->percent,
                'due_rule' => $i->due_rule,
                'month_offset' => $i->month_offset,
                'label_ar' => $i->label_ar ?? '',
            ])->values()->all();
            $this->minDownPercent = (float) $plan->min_down_payment_percent;
        } else {
            $this->items = [
                ['sequence' => 1, 'percent' => 40, 'due_rule' => 'at_enrollment', 'month_offset' => null, 'label_ar' => 'الدفعة الأولى'],
                ['sequence' => 2, 'percent' => 30, 'due_rule' => 'month_offset', 'month_offset' => 1, 'label_ar' => 'القسط الثاني'],
                ['sequence' => 3, 'percent' => 30, 'due_rule' => 'month_offset', 'month_offset' => 2, 'label_ar' => 'القسط الثالث'],
            ];
            $this->minDownPercent = 40;
        }
    }

    public function updatedNameAr(string $value): void
    {
        if ($this->planId) {
            return;
        }

        $this->slug = Str::slug($value, '-') ?: $this->slug;
    }

    public function addItem(): void
    {
        if (count($this->items) >= 24) {
            return;
        }

        $this->items[] = [
            'sequence' => count($this->items) + 1,
            'percent' => 0,
            'due_rule' => 'month_offset',
            'month_offset' => max(0, count($this->items)),
            'label_ar' => 'قسط '.(count($this->items) + 1),
        ];
        $this->renumberItems();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->renumberItems();
    }

    public function distributeEvenly(): void
    {
        $count = count($this->items);
        if ($count < 1) {
            return;
        }

        $base = floor((100 / $count) * 100) / 100;
        $allocated = 0.0;

        foreach ($this->items as $i => &$item) {
            if ($i === $count - 1) {
                $item['percent'] = round(100 - $allocated, 2);
            } else {
                $item['percent'] = $base;
                $allocated += $base;
            }
        }
        unset($item);

        $this->minDownPercent = (float) ($this->items[0]['percent'] ?? $this->minDownPercent);
    }

    /** @param array<int, float> $percents */
    public function applyPreset(string $key): void
    {
        $presets = [
            '40-30-30' => [40, 30, 30],
            '50-25-25' => [50, 25, 25],
            '30-35-35' => [30, 35, 35],
            '25x4' => [25, 25, 25, 25],
            '20x5' => [20, 20, 20, 20, 20],
        ];

        $percents = $presets[$key] ?? null;
        if ($percents === null) {
            return;
        }

        $this->items = [];
        foreach ($percents as $i => $percent) {
            $this->items[] = [
                'sequence' => $i + 1,
                'percent' => (float) $percent,
                'due_rule' => $i === 0 ? 'at_enrollment' : 'month_offset',
                'month_offset' => $i === 0 ? null : $i,
                'label_ar' => $i === 0 ? 'الدفعة الأولى' : ('القسط '.($i + 1)),
            ];
        }

        $this->minDownPercent = (float) $percents[0];
        $this->maxInstallments = max($this->maxInstallments, count($percents));
    }

    public function selectAllPrograms(): void
    {
        $this->linkedProgramIds = $this->allPrograms->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function selectVisiblePrograms(): void
    {
        $visible = $this->filteredPrograms->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->linkedProgramIds = collect($this->linkedProgramIds)
            ->merge($visible)
            ->unique()
            ->values()
            ->all();
    }

    public function clearPrograms(): void
    {
        $this->linkedProgramIds = [];
    }

    public function toggleProgram(string $id): void
    {
        if (in_array($id, $this->linkedProgramIds, true)) {
            $this->linkedProgramIds = array_values(array_filter(
                $this->linkedProgramIds,
                fn ($value) => (string) $value !== $id
            ));
        } else {
            $this->linkedProgramIds[] = $id;
        }
    }

    public function selectAllCourses(): void
    {
        $this->linkedCourseIds = $this->allCourses->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function selectVisibleCourses(): void
    {
        $visible = $this->filteredCourses->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->linkedCourseIds = collect($this->linkedCourseIds)
            ->merge($visible)
            ->unique()
            ->values()
            ->all();
    }

    public function clearCourses(): void
    {
        $this->linkedCourseIds = [];
    }

    public function toggleCourse(string $id): void
    {
        if (in_array($id, $this->linkedCourseIds, true)) {
            $this->linkedCourseIds = array_values(array_filter(
                $this->linkedCourseIds,
                fn ($value) => (string) $value !== $id
            ));
        } else {
            $this->linkedCourseIds[] = $id;
        }
    }

    public function updatedItems(): void
    {
        $this->savedMessage = null;
        $this->resetErrorBag('items');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('installments.manage'), 403);

        $this->validate([
            'slug' => ['required', 'string', 'max:64', 'alpha_dash'],
            'nameAr' => ['required', 'string', 'max:255'],
            'maxInstallments' => ['required', 'integer', 'min:1', 'max:36'],
            'minDownPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.percent' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'items.*.due_rule' => ['required', 'in:at_enrollment,month_offset'],
            'linkedProgramIds' => ['array'],
            'linkedProgramIds.*' => ['integer', 'exists:academic_programs,id'],
            'linkedCourseIds' => ['array'],
            'linkedCourseIds.*' => ['integer', 'exists:catalog_courses,id'],
        ], [], ['nameAr' => 'اسم الخطة', 'slug' => 'المعرّف']);

        $totalPercent = collect($this->items)->sum('percent');

        if (abs($totalPercent - 100) > 0.05) {
            $this->addError('items', 'مجموع النسب يجب أن يساوي 100% (الحالي: '.number_format($totalPercent, 2).'%)');

            return;
        }

        $firstPercent = (float) ($this->items[0]['percent'] ?? 0);
        if ($firstPercent + 0.001 < (float) $this->minDownPercent) {
            $this->addError('items', 'نسبة الدفعة الأولى ('.number_format($firstPercent, 2).'%) أقل من الحد الأدنى المحدد ('.number_format($this->minDownPercent, 2).'%).');

            return;
        }

        $data = [
            'slug' => $this->slug,
            'name_ar' => $this->nameAr,
            'name_en' => $this->nameEn ?: null,
            'program_type' => $this->programType ?: null,
            'max_installments' => $this->maxInstallments,
            'min_down_payment_percent' => $this->minDownPercent,
            'is_active' => $this->isActive,
            'description_ar' => $this->descriptionAr ?: null,
        ];

        if ($this->planId) {
            $plan = InstallmentPlanTemplate::query()->findOrFail($this->planId);
            $plan->update($data);
        } else {
            $plan = InstallmentPlanTemplate::query()->create($data);
            $this->planId = $plan->id;
        }

        InstallmentPlanTemplateItem::query()->where('template_id', $plan->id)->delete();

        foreach ($this->items as $item) {
            $plan->items()->create([
                'sequence' => $item['sequence'],
                'percent' => $item['percent'],
                'due_rule' => $item['due_rule'],
                'month_offset' => $item['due_rule'] === 'month_offset' ? ($item['month_offset'] ?? 0) : null,
                'label_ar' => $item['label_ar'] ?: null,
            ]);
        }

        $plan->academicPrograms()->sync(
            collect($this->linkedProgramIds)->map(fn ($id) => (int) $id)->filter()->unique()->all()
        );
        $plan->catalogCourses()->sync(
            collect($this->linkedCourseIds)->map(fn ($id) => (int) $id)->filter()->unique()->all()
        );

        $this->savedMessage = 'تم حفظ خطة التقسيط. يمكن ربط أكثر من خطة بنفس البرنامج أو الدبلوم.';
        $this->toastKey = 'plan-saved-'.uniqid();
        $this->js('setTimeout(() => $wire.set("savedMessage", null), 7000)');
    }

    #[Computed]
    public function allPrograms()
    {
        return AcademicProgram::query()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'code']);
    }

    #[Computed]
    public function filteredPrograms()
    {
        $q = trim($this->programSearch);

        return $this->allPrograms
            ->when($q !== '', fn ($c) => $c->filter(function ($program) use ($q) {
                return str_contains(mb_strtolower((string) $program->name_ar), mb_strtolower($q))
                    || str_contains(mb_strtolower((string) ($program->code ?? '')), mb_strtolower($q));
            }))
            ->values();
    }

    #[Computed]
    public function allCourses()
    {
        return CatalogCourse::query()
            ->with('categories:id')
            ->where('status', 'published')
            ->orderBy('title_ar')
            ->get(['id', 'title_ar', 'slug', 'academic_program_id']);
    }

    #[Computed]
    public function filteredCourses()
    {
        $q = trim($this->courseSearch);

        return $this->allCourses
            ->when($q !== '', fn ($c) => $c->filter(function ($course) use ($q) {
                return str_contains(mb_strtolower((string) $course->title_ar), mb_strtolower($q))
                    || str_contains((string) $course->id, $q)
                    || str_contains(mb_strtolower((string) ($course->slug ?? '')), mb_strtolower($q));
            }))
            ->values();
    }

    #[Computed]
    public function percentTotal(): float
    {
        return round((float) collect($this->items)->sum('percent'), 2);
    }

    protected function renumberItems(): void
    {
        foreach ($this->items as $i => &$item) {
            $item['sequence'] = $i + 1;
            if (($item['label_ar'] ?? '') === '' || preg_match('/^قسط\s+\d+$/u', (string) $item['label_ar'])) {
                $item['label_ar'] = $i === 0 ? 'الدفعة الأولى' : ('قسط '.($i + 1));
            }
        }
        unset($item);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.installment-plans'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.installment-plans'), 'label' => 'خطط التقسيط'],
        ['label' => $planId ? 'تعديل' : 'جديد'],
    ],
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-installment-plan-form.css') }}?v=2">
@endpush

@if ($savedMessage)
    @include('partials.admin.toast', [
        'message' => $savedMessage,
        'type' => 'success',
        'title' => 'تم الحفظ بنجاح',
        'key' => $toastKey ?: 'plan-saved',
        'duration' => 6500,
    ])
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head"><h2>{{ $planId ? 'تعديل خطة التقسيط' : 'خطة تقسيط جديدة' }}</h2></div>

    <div class="admin-form-grid admin-form-grid--2">
        <div class="admin-field">
            <label>اسم الخطة *</label>
            <input type="text" class="admin-control" wire:model.live.debounce.400ms="nameAr" placeholder="مثال: خطة دبلوم الذكاء الاصطناعي">
            @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>المعرّف (slug) *</label>
            <input type="text" class="admin-control" wire:model="slug" dir="ltr" @disabled($planId)>
            @error('slug')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>تصنيف الخطة</label>
            <select class="admin-control" wire:model="programType">
                @foreach (InstallmentOptions::programTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>حد أقصى للأقساط</label>
            <input type="number" class="admin-control" wire:model="maxInstallments" min="1" max="36">
        </div>
        <div class="admin-field">
            <label>الحد الأدنى للدفعة الأولى (%)</label>
            <input type="number" class="admin-control" wire:model.blur="minDownPercent" min="0" max="100" step="0.01">
        </div>
        <div class="admin-field">
            <label class="admin-check"><input type="checkbox" wire:model="isActive"> خطة نشطة ومتاحة في صفحة الدفع</label>
        </div>
        <div class="admin-field admin-field--wide">
            <label>الوصف</label>
            <textarea class="admin-control" rows="2" wire:model="descriptionAr" placeholder="يظهر للمتدرب عند اختيار الخطة"></textarea>
        </div>
    </div>
</section>

<section class="admin-crud-card inst-plan-scope" style="margin-top:1rem;">
    <div class="admin-crud-card__head">
        <div>
            <h2>ربط البرامج والدبلومات</h2>
            <p class="admin-crud-card__meta mb-0">
                يمكن ربط <strong>أكثر من خطة</strong> بنفس البرنامج أو الدبلوم.
                بدون تحديد تكون الخطة عامة لكل السلة.
            </p>
        </div>
    </div>

    <div class="inst-plan-pickers">
        <div class="inst-picker">
            <div class="inst-picker__head">
                <div>
                    <h3>البرامج الأكاديمية</h3>
                    <span class="inst-picker__count">{{ count($linkedProgramIds) }} محدد من {{ $this->allPrograms->count() }}</span>
                </div>
                <div class="inst-picker__actions">
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectAllPrograms">تحديد الكل</button>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectVisiblePrograms">تحديد الظاهر</button>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="clearPrograms">إلغاء تحديد</button>
                </div>
            </div>
            <div class="inst-picker__search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" class="admin-control" wire:model.live.debounce.250ms="programSearch" placeholder="ابحث باسم البرنامج أو الرمز...">
            </div>
            <div class="inst-picker__list" role="listbox" aria-label="البرامج الأكاديمية">
                @forelse ($this->filteredPrograms as $program)
                    @php
                        $selected = in_array((string) $program->id, $linkedProgramIds, true);
                    @endphp
                    <label class="inst-picker__item {{ $selected ? 'is-selected' : '' }}" wire:key="prog-{{ $program->id }}" wire:click.prevent="toggleProgram('{{ $program->id }}')">
                        <input type="checkbox" value="{{ $program->id }}" @checked($selected) tabindex="-1">
                        <span class="inst-picker__check" aria-hidden="true"></span>
                        <span class="inst-picker__text">
                            <strong>{{ $program->name_ar }}</strong>
                            @if ($program->code)
                                <small dir="ltr">{{ $program->code }}</small>
                            @endif
                        </span>
                    </label>
                @empty
                    <div class="inst-picker__empty">لا توجد نتائج مطابقة للبحث.</div>
                @endforelse
            </div>
        </div>

        <div class="inst-picker">
            <div class="inst-picker__head">
                <div>
                    <h3>الدبلومات والدورات</h3>
                    <span class="inst-picker__count">{{ count($linkedCourseIds) }} محدد من {{ $this->allCourses->count() }}</span>
                </div>
                <div class="inst-picker__actions">
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectAllCourses">تحديد الكل</button>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="selectVisibleCourses">تحديد الظاهر</button>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="clearCourses">إلغاء تحديد</button>
                </div>
            </div>
            <div class="inst-picker__search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" class="admin-control" wire:model.live.debounce.250ms="courseSearch" placeholder="ابحث باسم الدبلوم أو رقم الدورة...">
            </div>
            <div class="inst-picker__list" role="listbox" aria-label="الدبلومات والدورات">
                @forelse ($this->filteredCourses as $course)
                    @php
                        $selected = in_array((string) $course->id, $linkedCourseIds, true);
                        $isDiploma = $course->categories->contains('id', CatalogCourseService::CATEGORY_DIPLOMAS);
                    @endphp
                    <label class="inst-picker__item {{ $selected ? 'is-selected' : '' }}" wire:key="course-{{ $course->id }}" wire:click.prevent="toggleCourse('{{ $course->id }}')">
                        <input type="checkbox" value="{{ $course->id }}" @checked($selected) tabindex="-1">
                        <span class="inst-picker__check" aria-hidden="true"></span>
                        <span class="inst-picker__text">
                            <strong>{{ $course->title_ar }}</strong>
                            <small>
                                <span class="inst-picker__tag {{ $isDiploma ? 'inst-picker__tag--diploma' : '' }}">
                                    {{ $isDiploma ? 'دبلوم' : 'دورة' }}
                                </span>
                                #{{ $course->id }}
                            </small>
                        </span>
                    </label>
                @empty
                    <div class="inst-picker__empty">لا توجد نتائج مطابقة للبحث.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="admin-crud-card inst-plan-schedule" style="margin-top:1rem;">
    <div class="admin-crud-card__head admin-crud-card__head--split">
        <div>
            <h2>نسب التقسيط</h2>
            <p class="admin-crud-card__meta mb-0">يجب أن يساوي مجموع النسب 100%. الدفعة الأولى تُسدَّد عند التسجيل.</p>
        </div>
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="addItem">
            <i class="fa-solid fa-plus"></i> إضافة قسط
        </button>
    </div>

    <div class="inst-presets">
        <span class="inst-presets__label">قوالب سريعة:</span>
        <button type="button" class="inst-presets__btn" wire:click="applyPreset('40-30-30')">40 / 30 / 30</button>
        <button type="button" class="inst-presets__btn" wire:click="applyPreset('50-25-25')">50 / 25 / 25</button>
        <button type="button" class="inst-presets__btn" wire:click="applyPreset('30-35-35')">30 / 35 / 35</button>
        <button type="button" class="inst-presets__btn" wire:click="applyPreset('25x4')">4 × 25%</button>
        <button type="button" class="inst-presets__btn" wire:click="applyPreset('20x5')">5 × 20%</button>
        <button type="button" class="inst-presets__btn" wire:click="distributeEvenly">توزيع متساوٍ</button>
    </div>

    @php
        $percentSeed = collect($items)->map(fn ($item) => (float) ($item['percent'] ?? 0))->values()->all();
    @endphp

    <div
        class="inst-schedule-editor"
        wire:key="schedule-editor-{{ count($items) }}-{{ collect($items)->pluck('sequence')->implode('-') }}"
        x-data="{
            percents: {{ json_encode($percentSeed) }},
            get minDown() {
                return Number($wire.minDownPercent) || 0;
            },
            get total() {
                return this.percents.reduce((sum, value) => sum + (Number(value) || 0), 0);
            },
            get totalOk() {
                return Math.abs(this.total - 100) <= 0.05;
            },
            get firstOk() {
                return (Number(this.percents[0]) || 0) + 0.001 >= this.minDown;
            },
            get canSave() {
                return this.totalOk && this.firstOk;
            },
            clamp(index) {
                const next = Math.min(100, Math.max(0, Number(this.percents[index]) || 0));
                this.percents[index] = next;
            },
            async commitAndSave() {
                this.percents.forEach((_, index) => this.clamp(index));
                for (let index = 0; index < this.percents.length; index++) {
                    await $wire.set('items.' + index + '.percent', Number(this.percents[index]) || 0);
                }
                await $wire.save();
            }
        }"
    >
        <div
            class="inst-percent-meter"
            :class="totalOk ? 'is-ok' : 'is-bad'"
            role="status"
            aria-live="polite"
        >
            <div class="inst-percent-meter__bar" :style="'--pct:' + Math.min(100, total) + '%'"></div>
            <div class="inst-percent-meter__meta">
                <div class="inst-percent-meter__total">
                    <i class="fa-solid" :class="totalOk ? 'fa-circle-check' : 'fa-triangle-exclamation'" aria-hidden="true"></i>
                    <strong x-text="'المجموع: ' + total.toFixed(2) + '%'"></strong>
                </div>
                <span class="inst-percent-meter__badge" x-text="totalOk ? 'مكتمل' : (total > 100 ? 'يتجاوز 100%' : 'يجب أن يصل إلى 100%')"></span>
            </div>
        </div>

        @error('items')
            <div class="inst-callout inst-callout--danger" role="alert">
                <span class="inst-callout__icon" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></span>
                <div class="inst-callout__body">
                    <strong>تعذّر الحفظ</strong>
                    <p>{{ $message }}</p>
                </div>
            </div>
        @enderror

        <div class="inst-schedule-list">
            @foreach ($items as $index => $item)
                <div class="inst-schedule-card {{ $index === 0 ? 'is-first' : '' }}" wire:key="item-{{ $index }}">
                    <div class="inst-schedule-card__seq">
                        <span>{{ $item['sequence'] }}</span>
                        @if ($index === 0)
                            <em>أولى</em>
                        @endif
                    </div>
                    <div class="inst-schedule-card__fields">
                        <div class="admin-field">
                            <label>التسمية</label>
                            <input type="text" class="admin-control" wire:model.blur="items.{{ $index }}.label_ar">
                        </div>
                        <div class="admin-field">
                            <label>النسبة %</label>
                            <div class="inst-percent-input">
                                <input
                                    type="number"
                                    class="admin-control"
                                    x-model.number="percents[{{ $index }}]"
                                    @change="clamp({{ $index }})"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                >
                                <input
                                    type="range"
                                    min="0"
                                    max="100"
                                    step="0.5"
                                    x-model.number="percents[{{ $index }}]"
                                    aria-label="شريط النسبة"
                                >
                            </div>
                        </div>
                        <div class="admin-field">
                            <label>قاعدة الاستحقاق</label>
                            <select class="admin-control" wire:model.change="items.{{ $index }}.due_rule">
                                @foreach (InstallmentOptions::dueRules() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="admin-field">
                            <label>بعد (أشهر)</label>
                            <input type="number" class="admin-control" wire:model.blur="items.{{ $index }}.month_offset" min="0" @disabled(($item['due_rule'] ?? '') === 'at_enrollment')>
                        </div>
                    </div>
                    <button type="button" class="inst-schedule-card__remove" wire:click="removeItem({{ $index }})" @disabled(count($items) <= 1) title="حذف القسط">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="inst-schedule-hints">
            <div class="inst-callout" :class="firstOk ? 'inst-callout--success' : 'inst-callout--warn'">
                <span class="inst-callout__icon" aria-hidden="true">
                    <i class="fa-solid" :class="firstOk ? 'fa-circle-check' : 'fa-triangle-exclamation'"></i>
                </span>
                <div class="inst-callout__body">
                    <strong x-text="firstOk ? 'الدفعة الأولى مستوفية' : 'الدفعة الأولى أقل من المطلوب'"></strong>
                    <p x-text="'الآن ' + (Number(percents[0]) || 0).toFixed(2) + '% — الحد الأدنى المطلوب ' + minDown.toFixed(2) + '%'"></p>
                </div>
            </div>
            <div class="inst-callout inst-callout--info">
                <span class="inst-callout__icon" aria-hidden="true"><i class="fa-solid fa-info-circle"></i></span>
                <div class="inst-callout__body">
                    <strong>بعد الربط</strong>
                    <p>بعد حفظ الخطة وربطها بالدبلوم، تظهر تلقائياً في صفحة الدفع لذلك البرنامج.</p>
                </div>
            </div>
        </div>

        <div class="inst-plan-actions">
            <button
                type="button"
                class="inst-plan-actions__save"
                x-on:click="commitAndSave()"
                x-bind:disabled="!canSave"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <i class="fa-solid fa-floppy-disk" wire:loading.remove wire:target="save" aria-hidden="true"></i>
                <span wire:loading.remove wire:target="save">حفظ الخطة</span>
                <span wire:loading wire:target="save" class="inst-plan-actions__loading">
                    <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
                    جاري الحفظ...
                </span>
            </button>
            <a href="{{ route('admin.installment-plans') }}" class="inst-plan-actions__cancel">إلغاء</a>
        </div>
    </div>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-form-grid--2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .admin-field--wide { grid-column:1/-1; }
    @media (max-width:767px) { .admin-form-grid--2 { grid-template-columns:1fr; } }
</style>
@endpush
