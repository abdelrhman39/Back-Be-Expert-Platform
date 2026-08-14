<?php

use App\Models\AcademicStaff;
use App\Models\AcademicSchedule;
use App\Models\AcademicSection;
use App\Models\AcademicProgram;
use App\Models\AccessRole;
use App\Models\User;
use App\Support\AcademicStaffOptions;
use App\Support\InstructorPermissions;
use App\Support\AccessControl;
use App\Support\UserOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('عضو كادر | لوحة التحكم')]
class extends Component
{
    public ?int $staffId = null;

    public string $nameAr = '';

    public string $nameEn = '';

    public string $role = 'instructor';

    public string $specialty = '';

    public string $gender = '';

    public int $coursesCount = 0;

    public int $hoursPerWeek = 0;

    public string $compensationTotal = '0';

    public string $status = 'active';

    public string $permissionPreset = 'instructor.trainer';

    public bool $portalEnabled = true;

    public ?int $accountUserId = null;

    public string $accountEmail = '';

    public string $accountPhone = '';

    public string $accountStatus = 'active';

    public string $password = '';

    public string $passwordConfirmation = '';

    public array $sectionIds = [];

    public string $sectionSearch = '';

    public string $sectionProgram = '';

    public string $sectionAssignment = '';

    public int $sectionLimit = 24;

    public function mount(?AcademicStaff $staff = null): void
    {
        abort_unless(auth()->user()?->canAdmin('staff.manage'), 403);

        if (! $staff) {
            return;
        }

        $this->staffId = $staff->id;
        $this->nameAr = $staff->name_ar;
        $this->nameEn = $staff->name_en ?? '';
        $this->role = $staff->role;
        $this->specialty = $staff->specialty ?? '';
        $this->gender = $staff->gender ?? '';
        $this->coursesCount = $staff->courses_count;
        $this->hoursPerWeek = $staff->hours_per_week;
        $this->compensationTotal = (string) $staff->compensation_total;
        $this->status = $staff->status;
        $this->permissionPreset = InstructorPermissions::presetFor($staff);
        $this->accountUserId = $staff->user_id;
        $this->portalEnabled = $staff->user_id !== null;
        $this->accountEmail = $staff->user?->email ?? '';
        $this->accountPhone = $staff->user?->phone ?? '';
        $this->accountStatus = $staff->user?->status ?? 'active';
        $this->sectionIds = $staff->schedules()->pluck('section_id')->map(fn ($id) => (string) $id)->all();
    }

    #[Computed]
    public function sections()
    {
        return $this->sectionQuery()
            ->with(['program', 'course', 'batch', 'schedule.staff'])
            ->orderBy('program_id')
            ->orderBy('name')
            ->limit($this->sectionLimit)
            ->get();
    }

    #[Computed]
    public function sectionPrograms()
    {
        return AcademicProgram::query()
            ->where('status', 'active')
            ->whereIn('id', AcademicSection::query()->select('program_id')->where('status', 'active'))
            ->orderBy('name_ar')
            ->get(['id', 'name_ar']);
    }

    #[Computed]
    public function matchingSectionsCount(): int
    {
        return $this->sectionQuery()->count();
    }

    #[Computed]
    public function selectedSections()
    {
        return $this->sectionIds === []
            ? collect()
            : AcademicSection::query()
                ->with(['program', 'course'])
                ->whereKey($this->sectionIds)
                ->orderBy('name')
                ->get();
    }

    public function updatedSectionSearch(): void
    {
        $this->sectionLimit = 24;
    }

    public function updatedSectionProgram(): void
    {
        $this->sectionLimit = 24;
    }

    public function updatedSectionAssignment(): void
    {
        $this->sectionLimit = 24;
    }

    public function loadMoreSections(): void
    {
        $this->sectionLimit += 24;
    }

    public function removeSection(int $sectionId): void
    {
        $this->sectionIds = array_values(array_filter(
            $this->sectionIds,
            fn ($id) => (int) $id !== $sectionId,
        ));
    }

    private function sectionQuery()
    {
        return AcademicSection::query()
            ->where('status', 'active')
            ->when($this->sectionSearch, function ($query): void {
                $term = '%'.trim($this->sectionSearch).'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('subtitle', 'like', $term)
                        ->orWhereHas('program', fn ($program) => $program->where('name_ar', 'like', $term))
                        ->orWhereHas('course', fn ($course) => $course->where('name_ar', 'like', $term));
                });
            })
            ->when($this->sectionProgram, fn ($query) => $query->where('program_id', $this->sectionProgram))
            ->when($this->sectionAssignment === 'unassigned', fn ($query) => $query->whereDoesntHave('schedule', fn ($schedule) => $schedule->whereNotNull('staff_id')))
            ->when($this->sectionAssignment === 'mine' && $this->staffId, fn ($query) => $query->whereHas('schedule', fn ($schedule) => $schedule->where('staff_id', $this->staffId)))
            ->when($this->sectionAssignment === 'assigned', fn ($query) => $query->whereHas('schedule', fn ($schedule) => $schedule->whereNotNull('staff_id')));
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('staff.manage'), 403);

        $validated = $this->validate([
            'nameAr' => ['required', 'string', 'max:255'],
            'nameEn' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(array_keys(AcademicStaffOptions::roles()))],
            'specialty' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(array_keys(AcademicStaffOptions::genders()))],
            'coursesCount' => ['required', 'integer', 'min:0', 'max:99'],
            'hoursPerWeek' => ['required', 'integer', 'min:0', 'max:60'],
            'compensationTotal' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(AcademicStaffOptions::statuses()))],
            'permissionPreset' => ['required', Rule::in(array_keys(InstructorPermissions::presetLabels()))],
            'portalEnabled' => ['boolean'],
            'sectionIds' => ['array'],
            'sectionIds.*' => ['integer', 'distinct', Rule::exists('academic_sections', 'id')->where('status', 'active')],
        ], [], [
            'nameAr' => 'الاسم بالعربية',
            'nameEn' => 'الاسم بالإنجليزية',
            'role' => 'الدور',
            'specialty' => 'التخصص',
            'gender' => 'الجنس',
            'coursesCount' => 'عدد المقررات',
            'hoursPerWeek' => 'الساعات أسبوعياً',
            'compensationTotal' => 'إجمالي المكافآت',
            'status' => 'الحالة',
            'permissionPreset' => 'حزمة صلاحيات المدرب',
            'sectionIds' => 'الشعب المسندة',
        ]);

        if ($this->portalEnabled) {
            $accountRules = [
                'accountEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->accountUserId)],
                'accountPhone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($this->accountUserId)],
                'accountStatus' => ['required', Rule::in(array_keys(UserOptions::statuses()))],
            ];

            if (! $this->accountUserId || $this->password !== '') {
                $accountRules['password'] = ['required', 'string', 'min:8', 'same:passwordConfirmation'];
            }

            $this->validate($accountRules, [], [
                'accountEmail' => 'البريد الإلكتروني للدخول',
                'accountPhone' => 'جوال حساب المدرب',
                'accountStatus' => 'حالة حساب الدخول',
                'password' => 'كلمة المرور',
            ]);
        }

        $data = [
            'name_ar' => $validated['nameAr'],
            'name_en' => $validated['nameEn'] ?: null,
            'role' => $validated['role'],
            'permission_preset' => $validated['permissionPreset'],
            'specialty' => $validated['specialty'] ?: null,
            'gender' => $validated['gender'] ?: null,
            'courses_count' => $validated['coursesCount'],
            'hours_per_week' => $validated['hoursPerWeek'],
            'compensation_total' => $validated['compensationTotal'],
            'status' => $validated['status'],
        ];

        DB::transaction(function () use ($data): void {
            $staff = $this->staffId
                ? AcademicStaff::query()->findOrFail($this->staffId)
                : new AcademicStaff;

            $staff->fill($data);

            if ($this->portalEnabled) {
                $user = $this->accountUserId
                    ? User::query()->findOrFail($this->accountUserId)
                    : new User;

                $user->fill([
                    'name' => $this->nameEn ?: $this->nameAr,
                    'name_ar' => $this->nameAr,
                    'email' => $this->accountEmail,
                    'phone' => $this->accountPhone ?: null,
                    'role' => 'instructor',
                    'status' => $this->accountStatus,
                    'locale' => 'ar',
                ]);

                if ($this->password !== '') {
                    $user->password = $this->password;
                }

                if ($this->accountStatus === 'active') {
                    $user->failed_login_attempts = 0;
                    $user->locked_until = null;
                }

                $user->save();
                $staff->user_id = $user->id;
            } elseif ($staff->user_id) {
                $staff->user?->update(['status' => 'suspended']);
            }

            $staff->save();

            if ($this->portalEnabled && $staff->user_id && AccessControl::available()) {
                $bundleKeys = array_keys(InstructorPermissions::presetLabels());
                $bundleRoleIds = AccessRole::query()->whereIn('key', $bundleKeys)->pluck('id');
                $selectedBundleId = AccessRole::query()->where('key', $this->permissionPreset)->value('id');
                $preservedRoleIds = $staff->user->accessRoles()
                    ->whereNotIn('access_roles.id', $bundleRoleIds)
                    ->pluck('access_roles.id')
                    ->all();
                AccessControl::syncUserRoles(
                    $staff->user,
                    array_values(array_filter([...$preservedRoleIds, $selectedBundleId])),
                    auth()->user(),
                );
            }

            $selectedSectionIds = collect($this->sectionIds)->map(fn ($id) => (int) $id)->unique()->values();
            AcademicSchedule::query()
                ->where('staff_id', $staff->id)
                ->when($selectedSectionIds->isNotEmpty(), fn ($query) => $query->whereNotIn('section_id', $selectedSectionIds))
                ->update(['staff_id' => null]);

            AcademicSection::query()
                ->whereKey($selectedSectionIds)
                ->get()
                ->each(function (AcademicSection $section) use ($staff): void {
                    AcademicSchedule::query()->updateOrCreate(
                        ['section_id' => $section->id],
                        [
                            'batch_id' => $section->batch_id,
                            'level_id' => $section->level_id,
                            'semester_key' => $section->semester_key,
                            'period' => $section->period,
                            'staff_id' => $staff->id,
                            'trainer_name' => $staff->name_ar,
                        ],
                    );
                    $section->update(['supervisor' => $staff->name_ar]);
                });
        });

        $accountLabel = $this->portalEnabled ? ' وحساب المدرب' : '';
        session()->flash('admin_message', $this->staffId
            ? "تم تحديث بيانات العضو{$accountLabel}."
            : "تم إضافة العضو{$accountLabel} بنجاح.");

        $this->redirect(route('admin.staff.members'));
    }
};
?>

@php
    $pageTitle = $staffId ? 'تعديل عضو الكادر' : 'عضو كادر جديد';
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.staff.members'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.staff'), 'label' => 'الكوادر الأكاديمية'],
        ['href' => route('admin.staff.members'), 'label' => 'إدارة الأعضاء'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.staff.members') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>
    <form wire:submit="save">
        <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
            <div class="admin-field">
                <label>الاسم بالعربية *</label>
                <input type="text" class="admin-control" wire:model="nameAr">
                @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>الاسم بالإنجليزية</label>
                <input type="text" class="admin-control" wire:model="nameEn" dir="ltr">
                @error('nameEn')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>الدور *</label>
                <select class="admin-control" wire:model="role">
                    @foreach (AcademicStaffOptions::roles() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>التخصص</label>
                <input type="text" class="admin-control" wire:model="specialty">
            </div>
            <div class="admin-field">
                <label>الجنس</label>
                <select class="admin-control" wire:model="gender">
                    <option value="">—</option>
                    @foreach (AcademicStaffOptions::genders() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label>الحالة *</label>
                <select class="admin-control" wire:model="status">
                    @foreach (AcademicStaffOptions::statuses() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <div class="staff-permission-label">
                    <label for="staff-permission-preset">حزمة صلاحيات المدرب *</label>
                    <span class="staff-permission-help">
                        <button type="button" aria-label="شرح حزم صلاحيات المدرب" aria-describedby="staff-permission-help-bubble">
                            <i class="fa-solid fa-question"></i>
                        </button>
                        <span class="staff-permission-help__bubble" id="staff-permission-help-bubble" role="tooltip">
                            <strong>ما حزمة الصلاحيات؟</strong>
                            <p>تحدد مستوى وصول المدرب داخل الشعب المسندة إليه، ولا تمنحه صلاحيات على شعب مدربين آخرين.</p>
                            <ul>
                                <li><b>عرض فقط:</b> مشاهدة الشعب والجداول والمحتوى والنتائج دون تعديل.</li>
                                <li><b>مدرب أساسي:</b> رفع المحتوى، إدارة الواجبات والحضور والدرجات والاختبارات.</li>
                                <li><b>مدرب رئيسي:</b> صلاحيات الأساسي مع النشر والحذف والتقارير وتجاوز الحضور.</li>
                                <li><b>منسق مقرر:</b> إدارة وتشغيل المقرر والتقارير واقتراح الجداول دون الصلاحيات الحساسة الموسعة.</li>
                                <li><b>صلاحيات موسعة:</b> أعلى مستوى للمدرب، ويشمل الاعتماد النهائي وإدارة اجتماعات Teams.</li>
                            </ul>
                            <small>يفضل اختيار أقل حزمة تكفي لمهام المدرب، ويمكن تغييرها لاحقاً.</small>
                        </span>
                    </span>
                </div>
                <select id="staff-permission-preset" class="admin-control" wire:model="permissionPreset">
                    @foreach (InstructorPermissions::presetLabels() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <small class="admin-field-hint is-visible">تحدد ما يستطيع المدرب عرضه أو تعديله داخل الشعب المسندة إليه.</small>
                @if ($accountUserId && auth()->user()?->canAdmin('users.permissions'))
                    <a href="{{ route('admin.users.access', $accountUserId) }}" class="staff-permission-custom-link">
                        <i class="fa-solid fa-sliders"></i> تخصيص دقيق للصلاحيات والاستثناءات
                    </a>
                @endif
                @error('permissionPreset')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>عدد المقررات</label>
                <input type="number" min="0" max="99" class="admin-control" wire:model="coursesCount">
                @error('coursesCount')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>الساعات أسبوعياً</label>
                <input type="number" min="0" max="60" class="admin-control" wire:model="hoursPerWeek">
                @error('hoursPerWeek')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>إجمالي المكافآت (ر.س)</label>
                <input type="number" min="0" step="0.01" class="admin-control" wire:model="compensationTotal" dir="ltr">
                @error('compensationTotal')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        </div>

        <section class="staff-account-panel">
            <header class="staff-account-panel__head">
                <div>
                    <h3><i class="fa-solid fa-id-card"></i> حساب بوابة المدرب</h3>
                    <p>أنشئ حساب الدخول واربطه بسجل الكادر مباشرة، ثم أسند الشعب إلى المدرب من الجداول الأكاديمية.</p>
                </div>
                <label class="staff-account-toggle">
                    <input
                        type="checkbox"
                        wire:model.live="portalEnabled"
                        @disabled($accountUserId)
                    >
                    <span>{{ $accountUserId ? 'الحساب مرتبط' : 'إنشاء حساب دخول' }}</span>
                </label>
            </header>

            @if ($portalEnabled)
                <div class="admin-filter-grid staff-account-panel__fields" style="grid-template-columns:repeat(2,1fr);">
                    <div class="admin-field">
                        <label>البريد الإلكتروني للدخول *</label>
                        <input type="email" class="admin-control" wire:model="accountEmail" dir="ltr" autocomplete="off">
                        @error('accountEmail')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>رقم الجوال</label>
                        <input type="text" class="admin-control" wire:model="accountPhone" dir="ltr">
                        @error('accountPhone')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>حالة حساب الدخول *</label>
                        <select class="admin-control" wire:model="accountStatus">
                            @foreach (UserOptions::statuses() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('accountStatus')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field staff-account-panel__status">
                        <label>حالة الربط</label>
                        <div @class(['staff-account-state', 'is-linked' => $accountUserId])>
                            <i class="fa-solid {{ $accountUserId ? 'fa-circle-check' : 'fa-circle-plus' }}"></i>
                            {{ $accountUserId ? 'مرتبط بمستخدم رقم '.$accountUserId : 'سيُنشأ الحساب عند الحفظ' }}
                        </div>
                    </div>
                    <div class="admin-field">
                        <label>{{ $accountUserId ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور *' }}</label>
                        <input type="password" class="admin-control" wire:model="password" dir="ltr" autocomplete="new-password">
                        @error('password')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>
                    <div class="admin-field">
                        <label>{{ $accountUserId ? 'تأكيد كلمة المرور الجديدة' : 'تأكيد كلمة المرور *' }}</label>
                        <input type="password" class="admin-control" wire:model="passwordConfirmation" dir="ltr" autocomplete="new-password">
                    </div>
                </div>
            @endif
        </section>

        <section class="staff-account-panel">
            <header class="staff-account-panel__head">
                <div>
                    <h3><i class="fa-solid fa-graduation-cap"></i> الإسناد الأكاديمي</h3>
                    <p>حدد الشعب التي ستظهر داخل لوحة المدرب. يظهر اسم البرنامج والمقرر والمدرب الحالي قبل نقل الإسناد.</p>
                </div>
                <span class="staff-account-state is-linked">{{ count($sectionIds) }} شعبة محددة</span>
            </header>

            <div class="staff-section-tools">
                <div class="admin-field staff-section-tools__search">
                    <label>بحث سريع</label>
                    <div class="staff-section-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" class="admin-control" wire:model.live.debounce.300ms="sectionSearch" placeholder="اسم الشعبة، الكود، البرنامج أو المقرر">
                    </div>
                </div>
                <div class="admin-field">
                    <label>البرنامج</label>
                    <select class="admin-control" wire:model.live="sectionProgram">
                        <option value="">كل البرامج</option>
                        @foreach ($this->sectionPrograms as $program)
                            <option value="{{ $program->id }}">{{ $program->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label>حالة الإسناد</label>
                    <select class="admin-control" wire:model.live="sectionAssignment">
                        <option value="">جميع الشعب</option>
                        <option value="unassigned">غير مسندة فقط</option>
                        @if ($staffId)<option value="mine">المسندة لهذا المدرب</option>@endif
                        <option value="assigned">المسندة لمدرب</option>
                    </select>
                </div>
            </div>

            @if ($this->selectedSections->isNotEmpty())
                <div class="staff-section-selected">
                    <div class="staff-section-selected__head">
                        <strong>الشعب المختارة ({{ $this->selectedSections->count() }})</strong>
                        <span>تبقى محفوظة عند البحث أو تغيير الفلاتر</span>
                    </div>
                    <div class="staff-section-selected__chips">
                        @foreach ($this->selectedSections as $selectedSection)
                            <span wire:key="selected-section-{{ $selectedSection->id }}">
                                <small>{{ $selectedSection->program?->name_ar }}</small>
                                {{ $selectedSection->name }}
                                <button type="button" wire:click="removeSection({{ $selectedSection->id }})" aria-label="إزالة {{ $selectedSection->name }}">×</button>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="staff-section-results">
                <strong>{{ number_format($this->matchingSectionsCount) }} نتيجة</strong>
                <span>يعرض {{ count($this->sections) }} فقط للحفاظ على سرعة الصفحة</span>
            </div>

            <div class="staff-section-picker">
                @forelse ($this->sections as $section)
                    <label class="staff-section-option" wire:key="section-option-{{ $section->id }}">
                        <input type="checkbox" value="{{ $section->id }}" wire:model.live="sectionIds">
                        <span class="staff-section-option__main">
                            <strong>{{ $section->name }}</strong>
                            <small>{{ $section->program?->name_ar ?? 'برنامج غير محدد' }} · {{ $section->course?->name_ar ?? $section->subtitle }}</small>
                            @if ($section->schedule?->staff && (int) $section->schedule->staff_id !== (int) $staffId)
                                <em>مسند حالياً إلى {{ $section->schedule->staff->name_ar }} — سيتم نقل الإسناد عند الحفظ</em>
                            @endif
                        </span>
                        <span class="admin-badge">{{ $section->code }}</span>
                    </label>
                @empty
                    <p class="admin-field-hint is-visible">لا توجد شعب نشطة. أنشئ شعبة أولاً من إدارة الخطة الأكاديمية.</p>
                @endforelse
            </div>
            @if (count($this->sections) < $this->matchingSectionsCount)
                <div class="staff-section-load-more">
                    <button type="button" class="admin-btn-secondary" wire:click="loadMoreSections" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="loadMoreSections">عرض 24 شعبة إضافية</span>
                        <span wire:loading wire:target="loadMoreSections">جاري التحميل...</span>
                    </button>
                </div>
            @endif
            @error('sectionIds.*')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </section>

        <div class="admin-filter-actions" style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
        </div>
    </form>
</section>

@push('styles')
<style>
    .staff-account-panel{margin-top:1.25rem;padding:1rem;border:1px solid #dbe7e0;border-radius:14px;background:#f8fbf9}
    .staff-account-panel__head{display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .staff-account-panel__head h3{margin:0 0 .3rem;color:#123b2a;font-size:1rem;font-weight:900}
    .staff-account-panel__head h3 i{margin-left:.35rem;color:#1b8354}
    .staff-account-panel__head p{margin:0;color:#64748b;font-size:.76rem}
    .staff-account-toggle{display:flex;align-items:center;gap:.45rem;padding:.5rem .7rem;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#334155;font-size:.76rem;font-weight:800;cursor:pointer;white-space:nowrap}
    .staff-account-toggle input{accent-color:#1b8354}
    .staff-account-toggle:has(input:disabled){cursor:default;background:#ecfdf5;border-color:#bbf7d0;color:#166534}
    .staff-account-panel__fields{margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0}
    .staff-account-panel__status{justify-content:flex-end}
    .staff-account-state{display:flex;align-items:center;gap:.45rem;min-height:2.5rem;padding:.55rem .7rem;border-radius:9px;background:#fff7ed;color:#9a3412;font-size:.76rem;font-weight:800}
    .staff-account-state.is-linked{background:#ecfdf5;color:#166534}
    .staff-permission-label{display:flex;align-items:center;gap:.4rem;margin-bottom:.35rem}.staff-permission-label>label{margin:0}
    .staff-permission-help{position:relative;display:inline-flex}
    .staff-permission-help>button{display:grid;place-items:center;width:1.3rem;height:1.3rem;padding:0;border:1px solid #86b89e;border-radius:50%;background:#effaf4;color:#166534;font-size:.65rem;cursor:help}
    .staff-permission-help__bubble{position:absolute;z-index:60;inset-inline-start:-.35rem;top:calc(100% + .55rem);display:none;width:min(390px,calc(100vw - 3rem));padding:.9rem;border:1px solid #cfe2d7;border-radius:13px;background:#fff;color:#334155;box-shadow:0 16px 40px rgba(15,81,50,.18);font-size:.7rem;line-height:1.65}
    .staff-permission-help:hover .staff-permission-help__bubble,.staff-permission-help:focus-within .staff-permission-help__bubble{display:block}
    .staff-permission-help__bubble:before{content:"";position:absolute;top:-6px;inset-inline-start:.75rem;width:11px;height:11px;border-top:1px solid #cfe2d7;border-inline-start:1px solid #cfe2d7;background:#fff;transform:rotate(45deg)}
    .staff-permission-help__bubble strong{display:block;margin-bottom:.25rem;color:#145a38;font-size:.78rem}.staff-permission-help__bubble p{margin:0 0 .45rem}.staff-permission-help__bubble ul{display:grid;gap:.25rem;margin:0;padding-inline-start:1rem}.staff-permission-help__bubble small{display:block;margin-top:.55rem;padding-top:.5rem;border-top:1px solid #e2e8f0;color:#166534;font-weight:800}
    .staff-permission-custom-link{display:inline-flex;align-items:center;gap:.35rem;margin-top:.45rem;color:#166534;font-size:.69rem;font-weight:900;text-decoration:none}.staff-permission-custom-link:hover{text-decoration:underline}
    .staff-section-tools{display:grid;grid-template-columns:minmax(260px,1.5fr) repeat(2,minmax(180px,1fr));gap:.7rem;margin-top:1rem;padding:1rem;border:1px solid #dbe7e0;border-radius:12px;background:#fff}
    .staff-section-search{position:relative}.staff-section-search i{position:absolute;z-index:1;inset-inline-start:.75rem;top:50%;color:#64748b;transform:translateY(-50%)}.staff-section-search input{padding-inline-start:2.25rem}
    .staff-section-selected{margin-top:.8rem;padding:.8rem;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4}
    .staff-section-selected__head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.55rem}.staff-section-selected__head strong{font-size:.75rem;color:#166534}.staff-section-selected__head span{font-size:.65rem;color:#64748b}
    .staff-section-selected__chips{display:flex;flex-wrap:wrap;gap:.4rem}.staff-section-selected__chips>span{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .5rem;border:1px solid #a7d7ba;border-radius:999px;background:#fff;color:#145a38;font-size:.67rem;font-weight:800}.staff-section-selected__chips small{color:#64748b;font-size:.6rem}.staff-section-selected__chips button{display:grid;place-items:center;width:1.1rem;height:1.1rem;padding:0;border:0;border-radius:50%;background:#fee2e2;color:#b91c1c;cursor:pointer}
    .staff-section-results{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:1rem;padding-bottom:.5rem;border-bottom:1px solid #e2e8f0}.staff-section-results strong{font-size:.75rem;color:#145a38}.staff-section-results span{font-size:.65rem;color:#64748b}
    .staff-section-picker{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem;margin-top:.6rem}
    .staff-section-option{display:flex;align-items:flex-start;gap:.65rem;padding:.8rem;border:1px solid #dbe7e0;border-radius:12px;background:#fff;cursor:pointer;transition:.2s ease}
    .staff-section-option:has(input:checked){border-color:#1b8354;background:#f0fdf4;box-shadow:0 0 0 2px rgba(27,131,84,.08)}
    .staff-section-option input{margin-top:.25rem;accent-color:#1b8354}
    .staff-section-option__main{display:flex;flex:1;min-width:0;flex-direction:column;gap:.2rem}
    .staff-section-option__main strong{font-size:.8rem;color:#163f2d}.staff-section-option__main small{font-size:.69rem;color:#64748b}.staff-section-option__main em{font-size:.65rem;color:#b45309;font-style:normal}
    .staff-section-load-more{display:flex;justify-content:center;padding-top:1rem}
    @media(max-width:700px){.staff-account-panel__head{align-items:flex-start;flex-direction:column}.staff-account-panel__fields{grid-template-columns:1fr!important}}
    @media(max-width:850px){.staff-section-tools{grid-template-columns:1fr}.staff-section-picker{grid-template-columns:1fr}.staff-section-selected__head,.staff-section-results{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@include('partials.admin.shell-end')
