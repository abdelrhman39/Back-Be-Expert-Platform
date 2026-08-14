<?php

use App\Models\AcademicProgram;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Services\CrmAssignmentService;
use App\Services\CrmAuditService;
use App\Services\CrmContactStatusService;
use App\Services\CrmContactSyncService;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('إدارة علاقات العملاء CRM')]
class extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $status = '';
    #[Url] public string $program = '';
    #[Url] public string $owner = '';
    #[Url] public string $source = '';
    #[Url] public string $priority = '';
    #[Url] public string $followUp = '';
    public array $selected = [];
    public string $bulkOwner = '';
    public bool $showCreate = false;
    public string $newName = '';
    public string $newEmail = '';
    public string $newPhone = '';
    public string $newProgram = '';
    public string $newOwner = '';
    public string $newSource = '';
    public string $newPriority = 'medium';
    public string $newNotes = '';

    public bool $showPaymentModal = false;
    public ?int $paymentContactId = null;
    public string $paymentContactName = '';
    public string $paymentStatus = '';
    public bool $paymentHasExistingReceipt = false;
    public string $paymentExistingReceiptName = '';
    public $paymentReceipt = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('crm.view'), 403);
        $this->newSource = CrmOptions::defaultSourceKey();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'program', 'owner', 'source', 'priority', 'followUp'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    private function baseQuery()
    {
        return CrmContact::query()->visibleTo(auth()->user());
    }

    private function filteredQuery()
    {
        return $this->baseQuery()
            ->when($this->search, fn ($query) => $query->where(function ($query) {
                $term = '%'.$this->search.'%';
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('company', 'like', $term);
            }))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->program, fn ($query) => $query->where('program_id', $this->program))
            ->when($this->owner && CrmAccess::canViewAll(auth()->user()), fn ($query) => $this->owner === 'unassigned'
                ? $query->whereNull('owner_id')
                : $query->where('owner_id', $this->owner))
            ->when($this->source, fn ($query) => $query->where('source', $this->source))
            ->when($this->priority, fn ($query) => $query->where('priority', $this->priority))
            ->when($this->followUp === 'today', fn ($query) => $query->whereDate('next_follow_up_at', today()))
            ->when($this->followUp === 'overdue', fn ($query) => $query
                ->where('next_follow_up_at', '<', now())
                ->whereNotIn('status', CrmOptions::closedStatusKeys() ?: ['__none__']))
            ->when($this->followUp === 'none', fn ($query) => $query->whereNull('next_follow_up_at'));
    }

    #[Computed]
    public function contacts()
    {
        return $this->filteredQuery()
            ->with(['owner:id,name,name_ar', 'program:id,name_ar,code'])
            ->latest('last_activity_at')
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function metrics(): array
    {
        $base = $this->baseQuery();
        $total = (clone $base)->count();
        $defaultKey = CrmOptions::defaultStatusKey();
        $wonKeys = CrmOptions::wonStatusKeys();
        $closedKeys = CrmOptions::closedStatusKeys();
        $won = $wonKeys === [] ? 0 : (clone $base)->whereIn('status', $wonKeys)->count();

        return [
            'total' => $total,
            'new' => (clone $base)->where('status', $defaultKey)->count(),
            'default_key' => $defaultKey,
            'won_key' => $wonKeys[0] ?? '',
            'due' => (clone $base)->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<=', today()->endOfDay())
                ->when($closedKeys !== [], fn ($query) => $query->whereNotIn('status', $closedKeys))
                ->count(),
            'unassigned' => (clone $base)->whereNull('owner_id')->count(),
            'won' => $won,
            'conversion' => $total > 0 ? round(($won / $total) * 100, 1) : 0,
        ];
    }

    #[Computed]
    public function pipeline(): array
    {
        $counts = $this->baseQuery()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return CrmOptions::statusModels(true)->map(fn ($status) => [
            'key' => $status->key,
            'label' => $status->name_ar,
            'color' => $status->color,
            'count' => (int) ($counts[$status->key] ?? 0),
        ])->values()->all();
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

    public function filterStatus(string $status): void
    {
        if (array_key_exists($status, CrmOptions::statuses())) {
            $this->status = $this->status === $status ? '' : $status;
            $this->resetPage();
        }
    }

    public function createContact(): void
    {
        abort_unless(CrmAccess::canCreate(auth()->user()), 403);
        $validated = $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newEmail' => ['nullable', 'email', 'max:255', 'required_without:newPhone'],
            'newPhone' => ['nullable', 'string', 'max:40', 'required_without:newEmail'],
            'newProgram' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'newOwner' => ['nullable', 'integer', 'exists:users,id'],
            'newSource' => ['required', 'in:'.implode(',', array_keys(CrmOptions::sources()))],
            'newPriority' => ['required', 'in:low,medium,high,urgent'],
            'newNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['newOwner']) {
            abort_unless(CrmAccess::canAssign(auth()->user()), 403);
        }

        $contact = CrmContact::query()->create([
            'name' => $validated['newName'],
            'email' => $validated['newEmail'] ?: null,
            'phone' => $validated['newPhone'] ?: null,
            'program_id' => $validated['newProgram'] ?: null,
            'owner_id' => $validated['newOwner'] ?: null,
            'source' => $validated['newSource'],
            'priority' => $validated['newPriority'],
            'status' => CrmOptions::defaultStatusKey(),
            'notes' => $validated['newNotes'] ?: null,
            'created_by' => auth()->id(),
            'assigned_at' => $validated['newOwner'] ? now() : null,
            'last_activity_at' => now(),
        ]);
        CrmActivity::query()->create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
            'type' => 'system',
            'subject' => 'إنشاء العميل',
            'content' => 'تم إنشاء العميل يدوياً.',
            'completed_at' => now(),
        ]);
        app(CrmAuditService::class)->contactCreated($contact, auth()->user());
        if (! $contact->owner_id) {
            app(CrmAssignmentService::class)->autoAssign($contact, auth()->user());
        }

        $this->reset(['newName', 'newEmail', 'newPhone', 'newProgram', 'newOwner', 'newNotes', 'showCreate']);
        $this->newSource = CrmOptions::defaultSourceKey();
        $this->newPriority = 'medium';
        session()->flash('crm_success', 'تمت إضافة العميل وتطبيق قواعد التوزيع.');
    }

    public function distributeSelected(): void
    {
        abort_unless(CrmAccess::canBulkAssign(auth()->user()) || CrmAccess::canAssign(auth()->user()), 403);
        $this->validate(['selected' => ['required', 'array', 'min:1'], 'bulkOwner' => ['required', 'integer']]);
        abort_unless($this->salesUsers->contains('id', (int) $this->bulkOwner), 422);
        $count = app(CrmAssignmentService::class)->bulkAssign($this->selected, (int) $this->bulkOwner, auth()->user());
        $this->selected = [];
        session()->flash('crm_success', "تم توزيع {$count} عميل بنجاح.");
    }

    public function syncRegistered(): void
    {
        abort_unless(CrmAccess::canSync(auth()->user()), 403);
        $result = app(CrmContactSyncService::class)->syncAll(auth()->user());
        app(CrmAuditService::class)->synced($result, auth()->user());
        session()->flash('crm_success', "تمت مزامنة {$result['users']} حساب و{$result['applications']} طلب تسجيل.");
    }

    public function updateContactStatus(int $contactId, string $status): void
    {
        $contact = CrmContact::query()->visibleTo(auth()->user())->findOrFail($contactId);

        if (CrmOptions::isPaymentStatus($status)) {
            $this->paymentContactId = $contact->id;
            $this->paymentContactName = $contact->name;
            $this->paymentStatus = $status;
            $this->paymentHasExistingReceipt = $contact->hasPaymentReceipt();
            $this->paymentExistingReceiptName = (string) ($contact->payment_receipt_name ?: '');
            $this->paymentReceipt = null;
            $this->showPaymentModal = true;
            $this->resetErrorBag('paymentReceipt');

            return;
        }

        if (! app(CrmContactStatusService::class)->change($contact, $status, auth()->user())) {
            return;
        }

        unset($this->contacts, $this->metrics, $this->pipeline);
    }

    public function cancelPaymentModal(): void
    {
        $this->reset(['showPaymentModal', 'paymentContactId', 'paymentContactName', 'paymentStatus', 'paymentReceipt', 'paymentHasExistingReceipt', 'paymentExistingReceiptName']);
        $this->resetErrorBag('paymentReceipt');
        unset($this->contacts);
    }

    public function confirmPaymentStatus(): void
    {
        abort_unless($this->paymentContactId && $this->paymentStatus, 422);

        $rules = [
            'paymentReceipt' => [
                CrmOptions::requiresPaymentReceipt($this->paymentStatus) ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ];
        $this->validate($rules, [
            'paymentReceipt.required' => 'أرفق إيصال التحويل أو السداد.',
            'paymentReceipt.mimes' => 'صيغة الإيصال يجب أن تكون صورة أو PDF.',
            'paymentReceipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميجابايت.',
        ]);

        $contact = CrmContact::query()->visibleTo(auth()->user())->findOrFail($this->paymentContactId);
        app(CrmContactStatusService::class)->change(
            $contact,
            $this->paymentStatus,
            auth()->user(),
            null,
            $this->paymentReceipt,
        );

        $label = CrmOptions::statusLabel($this->paymentStatus);
        $this->cancelPaymentModal();
        unset($this->contacts, $this->metrics, $this->pipeline);
        session()->flash('crm_success', "تم تحديث حالة «{$contact->name}» إلى {$label}".($contact->fresh()->hasPaymentReceipt() ? ' مع إرفاق الإيصال.' : '.'));
    }

    public function downloadPaymentReceipt(int $contactId): mixed
    {
        $contact = CrmContact::query()->visibleTo(auth()->user())->findOrFail($contactId);
        abort_unless($contact->hasPaymentReceipt(), 404);
        abort_unless(Storage::disk('local')->exists($contact->payment_receipt_path), 404);

        return Storage::disk('local')->download(
            $contact->payment_receipt_path,
            $contact->payment_receipt_name ?: 'payment-receipt'
        );
    }

    public function export(): mixed
    {
        abort_unless(CrmAccess::canExport(auth()->user()), 403);
        $rows = $this->filteredQuery()->with(['owner', 'program'])->get();
        app(CrmAuditService::class)->exported($rows->count(), auth()->user());

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['الاسم', 'البريد', 'الهاتف', 'البرنامج', 'الحالة', 'الأولوية', 'الموظف', 'المتابعة القادمة']);
            foreach ($rows as $contact) {
                fputcsv($out, [
                    $contact->name, $contact->email, $contact->phone, $contact->program?->name_ar,
                    CrmOptions::statusLabel($contact->status),
                    CrmOptions::label(CrmOptions::priorities(), $contact->priority),
                    $contact->owner?->displayName(), $contact->next_follow_up_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'crm-contacts-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => auth()->user()?->canAdmin('dashboard.view') ? route('admin.dashboard') : route('admin.crm'), 'label' => 'الرئيسية'],
        ['label' => 'إدارة علاقات العملاء'],
    ],
])

<link rel="stylesheet" href="{{ asset('css/admin-crm.css') }}?v=1">

<div class="crm-page">
    <header class="crm-hero">
        <div>
            <span class="crm-eyebrow">SALES WORKSPACE</span>
            <h1>إدارة علاقات العملاء</h1>
            <p>مسار موحد من أول تسجيل العميل حتى التحويل، مع متابعة وتوزيع واضح لفريق المبيعات.</p>
        </div>
        <div class="crm-hero__actions">
            @if (CrmAccess::canViewAudit(auth()->user()))
                <a href="{{ route('admin.crm.audit') }}" class="crm-btn crm-btn--ghost">سجل الأحداث</a>
            @endif
            @if (CrmAccess::canViewSettings(auth()->user()))
                <a href="{{ route('admin.crm.settings') }}" class="crm-btn crm-btn--ghost">الحالات والمصادر</a>
            @endif
            @if (CrmAccess::canViewRules(auth()->user()))
                <a href="{{ route('admin.crm.rules') }}" class="crm-btn crm-btn--ghost">الفريق وقواعد التوزيع</a>
            @endif
            @if (CrmAccess::canSync(auth()->user()))
                <button type="button" wire:click="syncRegistered" wire:loading.attr="disabled" class="crm-btn crm-btn--ghost">مزامنة المسجلين</button>
            @endif
            @if (CrmAccess::canImport(auth()->user()))
                <a href="{{ route('admin.crm.import') }}" class="crm-btn crm-btn--ghost">رفع ملف عملاء</a>
            @endif
            @if (CrmAccess::canCreate(auth()->user()))
                <button type="button" wire:click="$toggle('showCreate')" class="crm-btn crm-btn--primary">+ عميل جديد</button>
            @endif
        </div>
    </header>

    @if (session('crm_success'))
        <div class="crm-alert">{{ session('crm_success') }}</div>
    @endif

    @if ($showCreate)
        <form wire:submit="createContact" class="crm-panel crm-create">
            <div class="crm-panel__title"><div><h2>إضافة عميل جديد</h2><p>يُوزع تلقائياً وفق البرنامج إن لم تختر موظفاً.</p></div></div>
            <div class="crm-form-grid">
                <label><span>اسم العميل *</span><input wire:model="newName" type="text"></label>
                <label><span>الهاتف</span><input wire:model="newPhone" type="tel" dir="ltr"></label>
                <label><span>البريد الإلكتروني</span><input wire:model="newEmail" type="email" dir="ltr"></label>
                <label><span>البرنامج</span><select wire:model="newProgram"><option value="">بدون برنامج</option>@foreach ($this->programs as $item)<option value="{{ $item->id }}">{{ $item->name_ar }}</option>@endforeach</select></label>
                @if (CrmAccess::canAssign(auth()->user()))
                    <label><span>موظف السيلز</span><select wire:model="newOwner"><option value="">توزيع تلقائي</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select></label>
                @endif
                <label><span>المصدر</span><select wire:model="newSource">@foreach (CrmOptions::sources() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>الأولوية</span><select wire:model="newPriority">@foreach (CrmOptions::priorities() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                <label class="crm-span-2"><span>ملاحظات أولية</span><textarea wire:model="newNotes" rows="2"></textarea></label>
            </div>
            @if ($errors->any())<div class="crm-errors">{{ $errors->first() }}</div>@endif
            <div class="crm-form-actions"><button class="crm-btn crm-btn--primary">حفظ العميل</button><button type="button" wire:click="$set('showCreate', false)" class="crm-btn crm-btn--ghost">إلغاء</button></div>
        </form>
    @endif

    <section class="crm-kpis">
        <button wire:click="$set('status', '')" class="crm-kpi"><span>إجمالي العملاء</span><strong>{{ number_format($this->metrics['total']) }}</strong><small>في نطاق وصولك</small></button>
        <button wire:click="filterStatus('{{ $this->metrics['default_key'] }}')" class="crm-kpi crm-kpi--blue"><span>عملاء جدد</span><strong>{{ number_format($this->metrics['new']) }}</strong><small>بانتظار أول تواصل</small></button>
        <button wire:click="$set('followUp', 'overdue')" class="crm-kpi crm-kpi--orange"><span>متابعات مستحقة</span><strong>{{ number_format($this->metrics['due']) }}</strong><small>اليوم أو متأخرة</small></button>
        @if (CrmAccess::canViewAll(auth()->user()))
            <button wire:click="$set('owner', 'unassigned')" class="crm-kpi crm-kpi--red"><span>بدون مسؤول</span><strong>{{ number_format($this->metrics['unassigned']) }}</strong><small>تحتاج إلى توزيع</small></button>
        @endif
        @if ($this->metrics['won_key'] !== '')
            <button wire:click="filterStatus('{{ $this->metrics['won_key'] }}')" class="crm-kpi crm-kpi--green"><span>تم التحويل</span><strong>{{ number_format($this->metrics['won']) }}</strong><small>نسبة {{ $this->metrics['conversion'] }}%</small></button>
        @endif
    </section>

    <section class="crm-panel crm-pipeline">
        <div class="crm-panel__title"><div><h2>مسار المبيعات</h2><p>اضغط على أي مرحلة لتصفية قائمة العملاء.</p></div></div>
        <div class="crm-pipeline__track">
            @foreach ($this->pipeline as $stage)
                <button wire:click="filterStatus('{{ $stage['key'] }}')" @class(['crm-stage', 'is-active' => $status === $stage['key']]) style="--stage-color: {{ $stage['color'] }}">
                    <strong>{{ $stage['count'] }}</strong><span>{{ $stage['label'] }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <section class="crm-panel">
        <div class="crm-panel__title crm-list-head">
            <div><h2>قائمة العملاء</h2><p>{{ $this->contacts->total() }} نتيجة مطابقة@if (CrmAccess::canChangeStatus(auth()->user())) · غيّر المرحلة مباشرة من القائمة@endif</p></div>
            @if (CrmAccess::canExport(auth()->user()))
                <button wire:click="export" class="crm-btn crm-btn--ghost">تصدير النتائج CSV</button>
            @endif
        </div>

        <details class="crm-filters-drawer" open>
            <summary>فلاتر البحث والتصفية</summary>
            <div class="crm-filters">
                <label class="crm-search"><span>بحث</span><input wire:model.live.debounce.350ms="search" type="search" placeholder="الاسم، الهاتف، البريد أو الشركة"></label>
                <label><span>الحالة</span><select wire:model.live="status"><option value="">كل الحالات</option>@foreach (CrmOptions::statuses() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>البرنامج</span><select wire:model.live="program"><option value="">كل البرامج</option>@foreach ($this->programs as $item)<option value="{{ $item->id }}">{{ $item->name_ar }}</option>@endforeach</select></label>
                @if (CrmAccess::canViewAll(auth()->user()))
                    <label><span>موظف السيلز</span><select wire:model.live="owner"><option value="">كل الفريق</option><option value="unassigned">غير موزع</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select></label>
                @endif
                <label><span>المصدر</span><select wire:model.live="source"><option value="">كل المصادر</option>@foreach (CrmOptions::sources() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>المتابعة</span><select wire:model.live="followUp"><option value="">الكل</option><option value="today">اليوم</option><option value="overdue">متأخرة</option><option value="none">بدون موعد</option></select></label>
                <label><span>الأولوية</span><select wire:model.live="priority"><option value="">الكل</option>@foreach (CrmOptions::priorities() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            </div>
        </details>

        @if ((CrmAccess::canBulkAssign(auth()->user()) || CrmAccess::canAssign(auth()->user())) && count($selected))
            <div class="crm-bulk">
                <strong>{{ count($selected) }} عميل محدد</strong>
                <select wire:model="bulkOwner"><option value="">اختر موظف السيلز</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select>
                <button wire:click="distributeSelected" class="crm-btn crm-btn--primary">توزيع المحدد</button>
                <button wire:click="$set('selected', [])" class="crm-btn crm-btn--ghost">إلغاء التحديد</button>
            </div>
        @endif

        <div class="crm-table-wrap">
            <table class="crm-table">
                <thead><tr>
                    @if (CrmAccess::canBulkAssign(auth()->user()) || CrmAccess::canAssign(auth()->user()))<th class="crm-check"></th>@endif
                    <th>العميل</th><th>البرنامج</th><th>المرحلة</th><th>المسؤول</th><th>آخر نشاط</th><th>المتابعة القادمة</th><th></th>
                </tr></thead>
                <tbody>
                    @forelse ($this->contacts as $contact)
                        <tr @class(['is-overdue' => $contact->isOverdue()]) wire:key="crm-contact-{{ $contact->id }}">
                            @if (CrmAccess::canBulkAssign(auth()->user()) || CrmAccess::canAssign(auth()->user()))<td class="crm-check"><input wire:model.live="selected" value="{{ $contact->id }}" type="checkbox"></td>@endif
                            <td><div class="crm-person"><span class="crm-avatar">{{ mb_substr($contact->name, 0, 1) }}</span><div><a href="{{ route('admin.crm.contacts.show', $contact) }}">{{ $contact->name }}</a><small dir="ltr">{{ $contact->phone ?: $contact->email ?: '—' }}</small></div></div></td>
                            <td><span class="crm-program">{{ $contact->program?->name_ar ?: 'غير محدد' }}</span><small>{{ CrmOptions::sourceLabel($contact->source) }}</small></td>
                            <td>
                                @include('partials.admin.crm-contact-status', ['contact' => $contact])
                            </td>
                            <td>{{ $contact->owner?->displayName() ?: 'غير موزع' }}</td>
                            <td>{{ $contact->last_activity_at?->diffForHumans() ?: 'لا يوجد' }}</td>
                            <td>@if($contact->next_follow_up_at)<strong>{{ $contact->next_follow_up_at->format('Y/m/d') }}</strong><small>{{ $contact->next_follow_up_at->format('H:i') }}</small>@else — @endif</td>
                            <td><a href="{{ route('admin.crm.contacts.show', $contact) }}" class="crm-open">فتح الملف ←</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="crm-empty"><strong>لا توجد نتائج</strong><span>جرّب تغيير الفلاتر أو أضف عميلاً جديداً.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="crm-cards" aria-label="قائمة العملاء للجوال">
            @forelse ($this->contacts as $contact)
                <article @class(['crm-card', 'is-overdue' => $contact->isOverdue()]) wire:key="crm-card-{{ $contact->id }}">
                    <div class="crm-card__top">
                        @if (CrmAccess::canBulkAssign(auth()->user()) || CrmAccess::canAssign(auth()->user()))
                            <div class="crm-card__check">
                                <input wire:model.live="selected" value="{{ $contact->id }}" type="checkbox" aria-label="تحديد {{ $contact->name }}">
                            </div>
                        @endif
                        <div class="crm-person crm-card__person">
                            <span class="crm-avatar">{{ mb_substr($contact->name, 0, 1) }}</span>
                            <div>
                                <a href="{{ route('admin.crm.contacts.show', $contact) }}">{{ $contact->name }}</a>
                                <small dir="ltr">{{ $contact->phone ?: $contact->email ?: '—' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="crm-card__meta">
                        <div>
                            <span>البرنامج</span>
                            <strong>{{ $contact->program?->name_ar ?: 'غير محدد' }}</strong>
                            <em>{{ CrmOptions::sourceLabel($contact->source) }}</em>
                        </div>
                        <div>
                            <span>المسؤول</span>
                            <strong>{{ $contact->owner?->displayName() ?: 'غير موزع' }}</strong>
                        </div>
                        <div>
                            <span>آخر نشاط</span>
                            <strong>{{ $contact->last_activity_at?->diffForHumans() ?: 'لا يوجد' }}</strong>
                        </div>
                        <div>
                            <span>المتابعة القادمة</span>
                            @if ($contact->next_follow_up_at)
                                <strong>{{ $contact->next_follow_up_at->format('Y/m/d') }}</strong>
                                <em>{{ $contact->next_follow_up_at->format('H:i') }}</em>
                            @else
                                <strong>—</strong>
                            @endif
                        </div>
                    </div>

                    <div class="crm-card__status">
                        <span style="font-size:11px;font-weight:800;color:#6a7e7b">المرحلة</span>
                        @include('partials.admin.crm-contact-status', ['contact' => $contact])
                    </div>

                    <div class="crm-card__foot">
                        <a href="{{ route('admin.crm.contacts.show', $contact) }}" class="crm-card__open">فتح الملف</a>
                    </div>
                </article>
            @empty
                <div class="crm-empty"><strong>لا توجد نتائج</strong><span>جرّب تغيير الفلاتر أو أضف عميلاً جديداً.</span></div>
            @endforelse
        </div>
        <div class="crm-pagination">{{ $this->contacts->links() }}</div>
    </section>

    @if ($showPaymentModal)
        <div class="crm-modal" wire:key="crm-payment-modal" role="dialog" aria-modal="true" aria-labelledby="crm-payment-title">
            <div class="crm-modal__backdrop" wire:click="cancelPaymentModal"></div>
            <div class="crm-modal__card">
                <header>
                    <div>
                        <span class="crm-eyebrow" style="color:#0d9488">PAYMENT UPDATE</span>
                        <h2 id="crm-payment-title">تحديث حالة السداد</h2>
                        <p>العميل: <strong>{{ $paymentContactName }}</strong> · الحالة: <strong>{{ CrmOptions::statusLabel($paymentStatus) }}</strong></p>
                    </div>
                    <button type="button" class="crm-btn crm-btn--ghost" wire:click="cancelPaymentModal" aria-label="إغلاق">×</button>
                </header>
                <div class="crm-modal__body">
                    <p class="crm-modal__hint">
                        @if (CrmOptions::requiresPaymentReceipt($paymentStatus))
                            أرفق إيصال التحويل أو السداد لإتمام تحديث الحالة إلى «تم السداد».
                        @else
                            يمكنك إرفاق إيصال التحويل الآن أو لاحقاً بعد وصوله.
                        @endif
                    </p>
                    @if ($paymentHasExistingReceipt)
                        <div class="crm-receipt-existing">
                            <i class="fa-solid fa-file-invoice"></i>
                            <span>يوجد إيصال سابق: {{ $paymentExistingReceiptName }}</span>
                            @if ($paymentContactId)
                                <button type="button" wire:click="downloadPaymentReceipt({{ $paymentContactId }})" class="crm-btn crm-btn--ghost">تنزيل</button>
                            @endif
                        </div>
                    @endif
                    <label class="crm-upload">
                        <span>إيصال التحويل / السداد @if(CrmOptions::requiresPaymentReceipt($paymentStatus))*@endif</span>
                        <input type="file" wire:model="paymentReceipt" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf">
                        <small>صور أو PDF بحد أقصى 5 ميجابايت</small>
                    </label>
                    @error('paymentReceipt') <div class="crm-errors">{{ $message }}</div> @enderror
                    <div wire:loading wire:target="paymentReceipt" class="crm-upload-loading">جاري رفع الملف…</div>
                </div>
                <footer class="crm-form-actions">
                    <button type="button" class="crm-btn crm-btn--primary" wire:click="confirmPaymentStatus" wire:loading.attr="disabled" wire:target="confirmPaymentStatus,paymentReceipt">
                        <span wire:loading.remove wire:target="confirmPaymentStatus">تأكيد التحديث</span>
                        <span wire:loading wire:target="confirmPaymentStatus">جاري الحفظ…</span>
                    </button>
                    <button type="button" class="crm-btn crm-btn--ghost" wire:click="cancelPaymentModal">إلغاء</button>
                </footer>
            </div>
        </div>
    @endif
</div>

@include('partials.admin.shell-end')
