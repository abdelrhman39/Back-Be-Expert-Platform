<?php

use App\Models\AcademicProgram;
use App\Models\CrmAssignmentRule;
use App\Models\CrmContact;
use App\Services\CrmAssignmentService;
use App\Services\CrmAuditService;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('فريق CRM وقواعد التوزيع')]
class extends Component
{
    public string $program = '';
    public string $salesUser = '';
    public int $priority = 100;

    public function mount(): void
    {
        abort_unless(CrmAccess::canViewRules(auth()->user()), 403);
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    #[Computed]
    public function salesUsers()
    {
        return app(CrmAssignmentService::class)->salesUsers();
    }

    #[Computed]
    public function rules()
    {
        return CrmAssignmentRule::query()
            ->with(['program:id,name_ar,code', 'salesUser:id,name,name_ar,email'])
            ->orderByRaw('CASE WHEN program_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('program_id')
            ->orderBy('priority')
            ->orderBy('assigned_count')
            ->get();
    }

    #[Computed]
    public function workloads(): array
    {
        return CrmContact::query()
            ->whereNotNull('owner_id')
            ->when(CrmOptions::closedStatusKeys() !== [], fn ($query) => $query->whereNotIn('status', CrmOptions::closedStatusKeys()))
            ->selectRaw('owner_id, COUNT(*) as total')
            ->groupBy('owner_id')
            ->pluck('total', 'owner_id')
            ->all();
    }

    public function saveRule(): void
    {
        abort_unless(CrmAccess::canManageRules(auth()->user()), 403);
        $validated = $this->validate([
            'program' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'salesUser' => ['required', 'integer', 'exists:users,id'],
            'priority' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        abort_unless($this->salesUsers->contains('id', (int) $validated['salesUser']), 422);

        $query = CrmAssignmentRule::query()->where('sales_user_id', $validated['salesUser']);
        $validated['program']
            ? $query->where('program_id', $validated['program'])
            : $query->whereNull('program_id');
        $rule = $query->first();
        $created = ! $rule;
        $rule ??= new CrmAssignmentRule;
        $rule->fill([
            'program_id' => $validated['program'] ?: null,
            'sales_user_id' => $validated['salesUser'],
            'priority' => $validated['priority'],
            'is_active' => true,
            'created_by' => $rule->created_by ?: auth()->id(),
        ])->save();

        app(CrmAuditService::class)->ruleSaved($rule, $created, auth()->user());
        $this->reset(['program', 'salesUser']);
        $this->priority = 100;
        unset($this->rules);
        session()->flash('crm_success', 'تم حفظ قاعدة التوزيع.');
    }

    public function toggleRule(int $ruleId): void
    {
        abort_unless(CrmAccess::canManageRules(auth()->user()), 403);
        $rule = CrmAssignmentRule::query()->findOrFail($ruleId);
        $rule->update(['is_active' => ! $rule->is_active]);
        app(CrmAuditService::class)->ruleToggled($rule->refresh(), auth()->user());
        unset($this->rules);
    }

    public function deleteRule(int $ruleId): void
    {
        abort_unless(CrmAccess::canManageRules(auth()->user()), 403);
        $rule = CrmAssignmentRule::query()->findOrFail($ruleId);
        app(CrmAuditService::class)->ruleDeleted($rule, auth()->user());
        $rule->delete();
        unset($this->rules);
        session()->flash('crm_success', 'تم حذف قاعدة التوزيع.');
    }

    public function resetCounters(): void
    {
        abort_unless(CrmAccess::canManageRules(auth()->user()), 403);
        CrmAssignmentRule::query()->update(['assigned_count' => 0, 'last_assigned_at' => null]);
        unset($this->rules);
        session()->flash('crm_success', 'تمت إعادة ضبط عدادات التوزيع العادل.');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => route('admin.crm'), 'label' => 'CRM'],
        ['label' => 'الفريق وقواعد التوزيع'],
    ],
])

<div class="crm-rules-page">
    <header class="crm-rules-hero">
        <div><span>ROUTING & OWNERSHIP</span><h1>فريق السيلز وقواعد البرامج</h1><p>خصص موظفي مبيعات لكل برنامج، وسيتم التوزيع بالتناوب حسب الأولوية وحجم التوزيع السابق.</p></div>
        <div><a href="{{ route('admin.users.create') }}" class="crm-rules-btn crm-rules-btn--light">+ إنشاء حساب سيلز</a><a href="{{ route('admin.crm') }}" class="crm-rules-btn crm-rules-btn--light">العودة إلى CRM</a></div>
    </header>

    @if (session('crm_success'))<div class="crm-rules-alert">{{ session('crm_success') }}</div>@endif

    <section class="crm-rules-card">
        <div class="crm-rules-head"><div><h2>حمل فريق المبيعات الحالي</h2><p>العملاء المفتوحون المسندون لكل موظف.</p></div></div>
        <div class="crm-team-grid">
            @forelse ($this->salesUsers as $sales)
                <article class="crm-team-member">
                    <span class="crm-team-avatar">{{ mb_substr($sales->displayName(), 0, 1) }}</span>
                    <div><strong>{{ $sales->displayName() }}</strong><small>{{ $sales->email }}</small></div>
                    <b>{{ $this->workloads[$sales->id] ?? 0 }}<small>عميل مفتوح</small></b>
                </article>
            @empty
                <div class="crm-rules-empty">لا يوجد موظفو سيلز. أنشئ مستخدماً جديداً واختر نوع الحساب «موظف مبيعات CRM».</div>
            @endforelse
        </div>
    </section>

    <div class="crm-rules-layout">
        @if (CrmAccess::canManageRules(auth()->user()))
        <form wire:submit="saveRule" class="crm-rules-card crm-rule-form">
            <div class="crm-rules-head"><div><h2>إضافة قاعدة تخصيص</h2><p>يمكن ربط أكثر من موظف بالبرنامج نفسه.</p></div></div>
            <label><span>البرنامج المستهدف</span><select wire:model="program"><option value="">كل البرامج (قاعدة احتياطية)</option>@foreach ($this->programs as $item)<option value="{{ $item->id }}">{{ $item->name_ar }} · {{ $item->code }}</option>@endforeach</select></label>
            <label><span>موظف السيلز</span><select wire:model="salesUser"><option value="">اختر موظفاً</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select></label>
            <label><span>أولوية القاعدة</span><input wire:model="priority" type="number" min="1" max="999"><small>الرقم الأقل يُستخدم أولاً. عند التساوي، يُختار الأقل حملاً بالتناوب.</small></label>
            @if ($errors->any())<div class="crm-rule-error">{{ $errors->first() }}</div>@endif
            <button class="crm-rules-btn crm-rules-btn--primary">حفظ القاعدة</button>
        </form>
        @endif

        <section class="crm-rules-card">
            <div class="crm-rules-head"><div><h2>قواعد التوزيع الفعالة</h2><p>{{ $this->rules->count() }} قاعدة مضافة.</p></div>@if(CrmAccess::canManageRules(auth()->user()))<button wire:click="resetCounters" wire:confirm="إعادة ضبط عدادات التوزيع؟" class="crm-rules-link">إعادة ضبط العدادات</button>@endif</div>
            <div class="crm-rule-list">
                @forelse ($this->rules as $rule)
                    <article @class(['crm-rule-item', 'is-disabled' => !$rule->is_active]) wire:key="crm-rule-{{ $rule->id }}">
                        <div class="crm-rule-program"><span>{{ $rule->program?->code ?: 'ALL' }}</span><div><strong>{{ $rule->program?->name_ar ?: 'كل البرامج — قاعدة احتياطية' }}</strong><small>الأولوية {{ $rule->priority }}</small></div></div>
                        <div class="crm-rule-sales"><strong>{{ $rule->salesUser?->displayName() ?: 'مستخدم محذوف' }}</strong><small>وُزع له {{ $rule->assigned_count }} · {{ $rule->last_assigned_at?->diffForHumans() ?: 'لم يُستخدم' }}</small></div>
                        @if (CrmAccess::canManageRules(auth()->user()))
                        <div class="crm-rule-actions"><button wire:click="toggleRule({{ $rule->id }})">{{ $rule->is_active ? 'تعطيل' : 'تفعيل' }}</button><button wire:click="deleteRule({{ $rule->id }})" wire:confirm="حذف هذه القاعدة؟" class="is-danger">حذف</button></div>
                        @endif
                    </article>
                @empty
                    <div class="crm-rules-empty">أضف قاعدة واحدة على الأقل حتى يبدأ التوزيع التلقائي.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="crm-rules-card crm-how">
        <h2>كيف يعمل التوزيع؟</h2>
        <div><article><b>1</b><strong>مطابقة البرنامج</strong><p>تبحث المنصة أولاً عن موظفين مخصصين لبرنامج العميل.</p></article><article><b>2</b><strong>الأولوية والحمل</strong><p>تختار أقل أولوية رقمية، ثم الأقل حصولاً على عملاء.</p></article><article><b>3</b><strong>القاعدة الاحتياطية</strong><p>إذا لم يوجد تخصيص للبرنامج، تستخدم قواعد «كل البرامج».</p></article><article><b>4</b><strong>الخصوصية</strong><p>لا يرى موظف السيلز إلا العملاء الذين تم إسنادهم إليه.</p></article></div>
    </section>
</div>

<style>
.crm-rules-page{display:grid;gap:18px;direction:rtl}.crm-rules-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;background:linear-gradient(120deg,#102b2d,#1b5852);border-radius:20px;padding:26px;color:#fff}.crm-rules-hero>div:last-child{display:flex;gap:8px}.crm-rules-hero>div>span{font-size:10px;color:#8ad7ca;letter-spacing:2px}.crm-rules-hero h1{margin:7px 0;font-size:27px}.crm-rules-hero p{margin:0;color:#d4e7e3}.crm-rules-btn{border:0;border-radius:10px;padding:11px 14px;text-decoration:none;font-weight:900;cursor:pointer}.crm-rules-btn--light{background:#ffffff15;color:#fff;border:1px solid #ffffff2b}.crm-rules-btn--primary{background:#d8a633;color:#173a37}.crm-rules-alert{padding:13px 16px;border-radius:11px;background:#e3f7ec;color:#17633f;font-weight:800}.crm-rules-card{background:#fff;border:1px solid #e0e9e8;border-radius:17px;padding:19px;box-shadow:0 8px 24px #193c3a0a}.crm-rules-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:15px}.crm-rules-head h2,.crm-how h2{font-size:18px;color:#193e3b;margin:0 0 3px}.crm-rules-head p{font-size:12px;color:#7b8b89;margin:0}.crm-team-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.crm-team-member{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:9px;border:1px solid #e2ebe9;border-radius:12px;padding:12px}.crm-team-avatar{width:37px;height:37px;border-radius:11px;background:#e3f2ef;color:#1e6159;display:grid;place-items:center;font-weight:900}.crm-team-member div,.crm-team-member b{display:grid}.crm-team-member small{font-size:10px;color:#7b8b89}.crm-team-member b{font-size:20px;color:#1c514c;text-align:center}.crm-rules-layout{display:grid;grid-template-columns:340px minmax(0,1fr);gap:18px;align-items:start}.crm-rule-form{display:grid;gap:12px}.crm-rule-form label{display:grid;gap:5px}.crm-rule-form label>span{font-size:11px;color:#617472;font-weight:800}.crm-rule-form input,.crm-rule-form select{width:100%;border:1px solid #dbe6e4;border-radius:10px;padding:10px}.crm-rule-form label small{color:#798987}.crm-rule-error{color:#b42318}.crm-rules-link{border:0;background:none;color:#28726a;font-weight:800;cursor:pointer}.crm-rule-list{display:grid;gap:8px}.crm-rule-item{border:1px solid #e2eae9;border-radius:12px;padding:11px;display:grid;grid-template-columns:1.2fr 1fr auto;align-items:center;gap:12px}.crm-rule-item.is-disabled{opacity:.55;background:#f5f6f6}.crm-rule-program,.crm-rule-sales{display:flex;align-items:center;gap:9px}.crm-rule-program>span{width:43px;height:38px;border-radius:9px;background:#e8f3f1;color:#23645d;display:grid;place-items:center;font:800 10px/1 monospace}.crm-rule-program div,.crm-rule-sales{display:grid}.crm-rule-program small,.crm-rule-sales small{font-size:10px;color:#7e8d8b}.crm-rule-actions{display:flex;gap:5px}.crm-rule-actions button{border:1px solid #d9e3e1;background:#fff;border-radius:8px;padding:7px 9px;cursor:pointer}.crm-rule-actions button.is-danger{color:#b23d37}.crm-rules-empty{padding:35px;text-align:center;color:#748684}.crm-how>div{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:15px}.crm-how article{border-right:3px solid #d8a633;padding:4px 12px}.crm-how b{color:#b88110}.crm-how strong{display:block;color:#254c48;margin:4px 0}.crm-how p{font-size:12px;color:#71827f;margin:0}
@media(max-width:1100px){.crm-team-grid{grid-template-columns:repeat(2,1fr)}.crm-how>div{grid-template-columns:repeat(2,1fr)}}@media(max-width:850px){.crm-rules-layout{grid-template-columns:1fr}.crm-rules-hero{align-items:flex-start;flex-direction:column}.crm-rule-item{grid-template-columns:1fr}}@media(max-width:560px){.crm-team-grid,.crm-how>div{grid-template-columns:1fr}.crm-rules-hero>div:last-child{flex-wrap:wrap}}
</style>

@include('partials.admin.shell-end')
