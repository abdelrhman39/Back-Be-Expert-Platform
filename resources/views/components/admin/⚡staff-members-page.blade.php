<?php

use App\Models\AcademicStaff;
use App\Support\AcademicStaffOptions;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('إدارة الكوادر | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $access = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedAccess(): void
    {
        $this->resetPage();
    }

    public function deleteStaff(int $staffId): void
    {
        abort_unless(auth()->user()?->canAdmin('staff.manage'), 403);
        $member = AcademicStaff::query()->findOrFail($staffId);

        if ($member->schedules()->exists()) {
            $this->addError('delete', 'لا يمكن حذف عضو مرتبط بجداول دراسية.');

            return;
        }

        DB::transaction(function () use ($member): void {
            $member->user?->update(['status' => 'suspended']);
            $member->delete();
        });
        session()->flash('admin_message', 'تم حذف العضو بنجاح.');
    }

    #[Computed]
    public function members()
    {
        return AcademicStaff::query()
            ->with(['user', 'schedules.section.program'])
            ->withCount('schedules')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name_ar', 'like', '%'.$this->search.'%')
                    ->orWhere('name_en', 'like', '%'.$this->search.'%')
                    ->orWhere('specialty', 'like', '%'.$this->search.'%');
            }))
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->access === 'ready', fn ($q) => $q
                ->where('status', 'active')
                ->whereHas('user', fn ($user) => $user->where('role', 'instructor')->where('status', 'active'))
                ->whereHas('schedules'))
            ->when($this->access === 'no_account', fn ($q) => $q->whereNull('user_id'))
            ->when($this->access === 'unassigned', fn ($q) => $q->whereDoesntHave('schedules'))
            ->orderBy('name_ar')
            ->paginate(15);
    }

    #[Computed]
    public function overview(): array
    {
        return [
            'total' => AcademicStaff::query()->count(),
            'active' => AcademicStaff::query()->where('status', 'active')->count(),
            'portal' => AcademicStaff::query()->whereNotNull('user_id')->count(),
            'assigned' => AcademicStaff::query()->whereHas('schedules')->count(),
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.staff.members'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.staff'), 'label' => 'الكوادر الأكاديمية'],
        ['label' => 'إدارة الأعضاء'],
    ],
])

@if (session('admin_message'))
    <div class="admin-alert admin-alert--info is-visible">{{ session('admin_message') }}</div>
@endif
@error('delete')
    <div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>
@enderror
@error('staff')
    <div class="admin-alert admin-alert--error is-visible">{{ $message }}</div>
@enderror

<section class="staff-members-hero">
    <div>
        <span class="staff-members-hero__eyebrow">مركز إدارة الكادر</span>
        <h1>المدربون والحسابات والإسناد الأكاديمي</h1>
        <p>تحقق من جاهزية حساب المدرب، البرنامج المسند إليه، ثم ادخل إلى لوحته مباشرة لمراجعة تجربته.</p>
    </div>
    @canAdmin('staff.manage')
        <a href="{{ route('admin.staff.create') }}" class="admin-btn-primary">إضافة مدرب جديد</a>
    @endcanAdmin
</section>

<div class="staff-members-kpis">
    <div><strong>{{ $this->overview['total'] }}</strong><span>إجمالي الكادر</span></div>
    <div><strong>{{ $this->overview['active'] }}</strong><span>نشط</span></div>
    <div><strong>{{ $this->overview['portal'] }}</strong><span>حسابات بوابة</span></div>
    <div><strong>{{ $this->overview['assigned'] }}</strong><span>مسندون لشعب</span></div>
</div>

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head">
        <h2>بحث وفلترة <span class="admin-crud-card__meta">— {{ $this->members->total() }} عضو</span></h2>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="الاسم أو التخصص">
        </div>
        <div class="admin-field">
            <label>جاهزية بوابة المدرب</label>
            <select class="admin-control" wire:model.live="access">
                <option value="">الكل</option>
                <option value="ready">جاهز للدخول المباشر</option>
                <option value="no_account">بدون حساب بوابة</option>
                <option value="unassigned">بدون شعبة مسندة</option>
            </select>
        </div>
        <div class="admin-field">
            <label>الدور</label>
            <select class="admin-control" wire:model.live="role">
                <option value="">الكل</option>
                @foreach (AcademicStaffOptions::roles() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (AcademicStaffOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card staff-members-table-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>قائمة الكوادر الأكاديمية <span class="admin-crud-card__meta">— {{ $this->members->total() }}</span></h2>
        <div class="admin-row-actions">
            <a href="{{ route('admin.staff') }}" class="admin-btn-secondary admin-btn-secondary--sm">لوحة المؤشرات</a>
            @canAdmin('staff.manage')
                <a href="{{ route('admin.staff.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ عضو جديد</a>
            @endcanAdmin
        </div>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-data-table staff-members-table">
            <thead>
                <tr>
                    <th>العضو</th>
                    <th>الدور</th>
                    <th>التخصص</th>
                    <th>الإسناد</th>
                    <th>حساب البوابة</th>
                    <th>الساعات</th>
                    <th>الحالة والإجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->members as $member)
                    @php
                        $programNames = $member->schedules->pluck('section.program.name_ar')->filter()->unique()->values();
                        $blockReason = $member->impersonationBlockReason();
                        $portalReady = $member->canBeImpersonated();
                        $initials = collect(preg_split('/\s+/u', trim($member->name_ar)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->implode('');
                        $roleClass = match ($member->role) {
                            'instructor' => 'staff-role--instructor',
                            'coordinator' => 'staff-role--coordinator',
                            'reviewer' => 'staff-role--reviewer',
                            'assistant' => 'staff-role--assistant',
                            default => 'staff-role--default',
                        };
                    @endphp
                    <tr wire:key="staff-row-{{ $member->id }}">
                        <td>
                            <div class="staff-member-cell">
                                <span class="staff-member-cell__avatar" aria-hidden="true">{{ $initials ?: '؟' }}</span>
                                <span class="staff-member-cell__body">
                                    <a href="{{ route('admin.staff.show', $member) }}" class="staff-member-cell__name">{{ $member->name_ar }}</a>
                                    <span class="staff-member-cell__meta">
                                        #{{ $member->id }}
                                        @if ($member->name_en)
                                            · <span dir="ltr">{{ $member->name_en }}</span>
                                        @endif
                                    </span>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span @class(['staff-role', $roleClass])>{{ AcademicStaffOptions::roleLabel($member->role) }}</span>
                        </td>
                        <td>
                            <span class="staff-specialty" title="{{ $member->specialty }}">{{ $member->specialty ?: '—' }}</span>
                        </td>
                        <td>
                            <div class="staff-assign-cell">
                                @if ($programNames->isNotEmpty())
                                    <strong>{{ $programNames->first() }}</strong>
                                    @if ($programNames->count() > 1)
                                        <span class="staff-assign-cell__more">+{{ $programNames->count() - 1 }} برنامج</span>
                                    @endif
                                    <span class="staff-assign-cell__meta">{{ $member->schedules_count }} شعبة/جدول</span>
                                @else
                                    <strong class="is-muted">غير مسند</strong>
                                    <span class="staff-assign-cell__meta">لا توجد شعب مرتبطة</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="staff-portal-cell">
                                @if ($member->user)
                                    <span class="staff-portal-cell__email" dir="ltr" title="{{ $member->user->email }}">{{ $member->user->email }}</span>
                                    <span @class([
                                        'staff-portal-pill',
                                        'is-ready' => $portalReady,
                                        'is-blocked' => ! $portalReady,
                                    ])>
                                        <i class="fa-solid {{ $portalReady ? 'fa-circle-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                                        {{ $portalReady ? 'جاهز للدخول' : ($blockReason ?: 'غير جاهز') }}
                                    </span>
                                @else
                                    <span class="staff-portal-cell__email is-muted">غير مرتبط</span>
                                    <span class="staff-portal-pill is-empty">
                                        <i class="fa-solid fa-link-slash" aria-hidden="true"></i>
                                        بدون حساب بوابة
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="staff-col-load">
                            <div class="staff-hours-cell" title="الساعات الأسبوعية">
                                <strong>{{ (int) $member->hours_per_week }}</strong>
                                <span>ساعة / أسبوع</span>
                            </div>
                        </td>
                        <td class="admin-table-actions staff-col-actions">
                            <div class="staff-actions-cell">
                                <span @class([
                                    'staff-status-badge',
                                    'is-active' => $member->status === 'active',
                                    'is-leave' => $member->status === 'on_leave',
                                    'is-inactive' => $member->status === 'inactive',
                                ])>
                                    {{ AcademicStaffOptions::statusLabel($member->status) }}
                                </span>
                                <div class="staff-row-actions">
                                    @canAdmin('staff.impersonate')
                                        @if ($portalReady)
                                            <form method="post" action="{{ route('admin.staff.impersonate', $member) }}">
                                                @csrf
                                                <button type="submit" class="staff-impersonate-btn" title="دخول كمدرب">
                                                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                                    دخول
                                                </button>
                                            </form>
                                        @endif
                                    @endcanAdmin
                                    <div class="admin-actions-menu">
                                        <button
                                            type="button"
                                            class="admin-kebab"
                                            aria-expanded="false"
                                            aria-haspopup="true"
                                            aria-label="إجراءات {{ $member->name_ar }}"
                                        >
                                            <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                                            </svg>
                                        </button>
                                        <ul class="admin-actions-dropdown" hidden role="menu">
                                            <li role="none">
                                                <a href="{{ route('admin.staff.show', $member) }}" class="admin-actions-item" role="menuitem">عرض الملف</a>
                                            </li>
                                            @canAdmin('staff.manage')
                                                <li role="none">
                                                    <a href="{{ route('admin.staff.edit', $member) }}" class="admin-actions-item" role="menuitem">تعديل</a>
                                                </li>
                                                <li role="none">
                                                    <button
                                                        type="button"
                                                        class="admin-actions-item admin-actions-item--btn"
                                                        role="menuitem"
                                                        wire:click="deleteStaff({{ $member->id }})"
                                                        wire:confirm="حذف هذا العضو؟"
                                                    >حذف</button>
                                                </li>
                                            @endcanAdmin
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="staff-members-empty">لا يوجد كادر مطابق للفلاتر.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->members->hasPages())
        {{ $this->members->links() }}
    @endif
</section>

@push('styles')
<style>
    .admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}
    .admin-alert--error{display:block;background:var(--color-danger-bg);color:var(--color-danger-text);padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem}
    .staff-members-hero{display:flex;align-items:center;justify-content:space-between;gap:1.25rem;padding:1.4rem;margin-bottom:1rem;border-radius:18px;background:linear-gradient(135deg,#0f5132,#178654);color:#fff;box-shadow:0 16px 34px rgba(15,81,50,.18)}
    .staff-members-hero__eyebrow{font-size:.7rem;font-weight:900;opacity:.8}
    .staff-members-hero h1{margin:.25rem 0;font-size:1.35rem;font-weight:900}
    .staff-members-hero p{margin:0;max-width:720px;font-size:.78rem;opacity:.86}
    .staff-members-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem}
    .staff-members-kpis>div{display:flex;flex-direction:column;padding:1rem;border:1px solid #dbe7e0;border-radius:14px;background:#fff}
    .staff-members-kpis strong{font-size:1.4rem;color:#145a38}
    .staff-members-kpis span{font-size:.72rem;color:#64748b;font-weight:700}

    .staff-members-table-card{overflow:hidden}
    .staff-members-table-card .admin-table-wrap{
        max-width:100%;
        overflow-x:auto;
        overflow-y:visible;
        -webkit-overflow-scrolling:touch;
    }
    .staff-members-table{width:max-content;min-width:100%;border-collapse:collapse;table-layout:auto}
    .staff-members-table thead th{white-space:nowrap;font-size:.72rem;font-weight:800;letter-spacing:.01em;padding:.8rem .75rem}
    .staff-members-table thead th:last-child,
    .staff-members-table tbody td:last-child{padding-inline-end:1rem}
    .staff-members-table tbody td{vertical-align:middle;padding:.85rem .75rem;border-bottom:1px solid #eef2f6;position:static}
    .staff-members-table tbody tr:hover td{background:#f8fafc}
    .staff-members-empty{text-align:center;padding:2.25rem 1rem!important;color:#64748b;font-weight:700}

    .staff-member-cell{display:flex;align-items:center;gap:.65rem;min-width:11rem;max-width:14rem}
    .staff-member-cell__avatar{flex-shrink:0;width:2.35rem;height:2.35rem;display:grid;place-items:center;border-radius:50%;border:1px solid #dbe7e0;background:#ecfdf5;color:#145a38;font-size:.72rem;font-weight:900}
    .staff-member-cell__body{min-width:0;display:grid;gap:.15rem}
    .staff-member-cell__name{display:block;font-size:.86rem;font-weight:800;color:#0f172a;text-decoration:none;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .staff-member-cell__name:hover{color:#145a38}
    .staff-member-cell__meta{display:block;font-size:.68rem;color:#94a3b8;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

    .staff-role{display:inline-flex;align-items:center;padding:.25rem .55rem;border-radius:999px;border:1px solid transparent;font-size:.68rem;font-weight:800;white-space:nowrap}
    .staff-role--instructor{background:#dcfce7;color:#166534;border-color:#bbf7d0}
    .staff-role--coordinator{background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe}
    .staff-role--reviewer{background:#fef3c7;color:#b45309;border-color:#fde68a}
    .staff-role--assistant{background:#e0f2fe;color:#0369a1;border-color:#bae6fd}
    .staff-role--default{background:#f1f5f9;color:#475569;border-color:#e2e8f0}

    .staff-specialty{display:block;max-width:9.5rem;font-size:.78rem;font-weight:700;color:#334155;line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

    .staff-assign-cell{display:grid;gap:.22rem;min-width:9.5rem;max-width:13rem}
    .staff-assign-cell strong{font-size:.78rem;font-weight:800;color:#0f172a;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .staff-assign-cell strong.is-muted,.staff-portal-cell__email.is-muted{color:#94a3b8;font-weight:700}
    .staff-assign-cell__more{display:inline-flex;width:fit-content;padding:.1rem .4rem;border-radius:999px;background:#f1f5f9;color:#64748b;font-size:.62rem;font-weight:800}
    .staff-assign-cell__meta{font-size:.66rem;color:#94a3b8;font-weight:700}

    .staff-portal-cell{display:grid;gap:.35rem;min-width:10rem;max-width:13rem}
    .staff-portal-cell__email{display:block;font-size:.74rem;font-weight:800;color:#0f172a;line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .staff-portal-pill{display:inline-flex;align-items:center;gap:.3rem;width:fit-content;max-width:100%;padding:.22rem .48rem;border-radius:999px;font-size:.64rem;font-weight:800;line-height:1.2}
    .staff-portal-pill i{font-size:.58rem}
    .staff-portal-pill.is-ready{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
    .staff-portal-pill.is-blocked{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
    .staff-portal-pill.is-empty{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0}

    .staff-col-load{width:7.5rem;white-space:nowrap}
    .staff-hours-cell{display:inline-flex;align-items:baseline;gap:.4rem;padding:.45rem .7rem;border-radius:12px;background:#ecfdf5;border:1px solid #bbf7d0;color:#145a38}
    .staff-hours-cell strong{font-size:1.15rem;font-weight:900;line-height:1;letter-spacing:-.02em}
    .staff-hours-cell span{font-size:.72rem;font-weight:800;color:#166534;opacity:.9}

    .staff-status-badge{display:inline-flex;align-items:center;padding:.28rem .6rem;border-radius:999px;font-size:.68rem;font-weight:800;white-space:nowrap;border:1px solid transparent}
    .staff-status-badge.is-active{background:#dcfce7;color:#166534;border-color:#bbf7d0}
    .staff-status-badge.is-leave{background:#fef3c7;color:#b45309;border-color:#fde68a}
    .staff-status-badge.is-inactive{background:#fee2e2;color:#b91c1c;border-color:#fecaca}

    .staff-members-table .admin-table-actions,
    .staff-members-table-card .admin-table-actions,
    [dir="rtl"] .staff-members-table .admin-table-actions{
        position:static!important;
        left:auto!important;
        right:auto!important;
        inset:auto!important;
        z-index:auto!important;
        box-shadow:none!important;
        width:1%;
        min-width:9.5rem;
        white-space:nowrap;
        background:transparent!important;
        vertical-align:middle;
    }
    .staff-members-table tbody tr:hover .admin-table-actions{background:#f8fafc!important}
    .staff-actions-cell{display:grid;gap:.45rem;justify-items:start}
    .staff-row-actions{display:inline-flex;align-items:center;justify-content:flex-start;gap:.35rem;flex-wrap:nowrap}
    .staff-row-actions form{display:inline-flex;margin:0}
    .staff-impersonate-btn{appearance:none;display:inline-flex;align-items:center;gap:.28rem;border:0;border-radius:8px;padding:.36rem .58rem;background:#166534;color:#fff;font:inherit;font-size:.68rem;font-weight:800;cursor:pointer;white-space:nowrap;line-height:1}
    .staff-impersonate-btn i{font-size:.62rem}
    .staff-impersonate-btn:hover{background:#14532d}
    .admin-actions-item--btn{border:none;background:transparent;width:100%;text-align:inherit;cursor:pointer;font:inherit}
    .admin-actions-item--btn:hover{background:var(--sa-green-soft,#ecfdf5);color:var(--sa-green-dark,#145a38)}

    .staff-members-table tbody tr.is-row-actions-open{position:relative;z-index:20}
    .staff-members-table tbody tr.is-row-actions-open .admin-table-actions{position:relative!important;z-index:21}

    @media(max-width:800px){
        .staff-members-hero{align-items:flex-start;flex-direction:column}
        .staff-members-kpis{grid-template-columns:repeat(2,1fr)}
    }

</style>
@endpush

@include('partials.admin.shell-end')
