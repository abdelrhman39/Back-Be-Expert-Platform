<?php

use App\Models\CrmContact;
use App\Models\CrmSource;
use App\Models\CrmStatus;
use App\Services\CrmAuditService;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('إعدادات الحالات والمصادر | CRM')]
class extends Component
{
    #[Url]
    public string $tab = 'statuses';

    public ?int $editingStatusId = null;
    public string $statusKey = '';
    public string $statusName = '';
    public string $statusColor = '#55706f';
    public int $statusSort = 100;
    public bool $statusActive = true;
    public bool $statusDefault = false;
    public bool $statusInitial = false;
    public bool $statusWon = false;
    public bool $statusLost = false;
    public bool $statusClosed = false;

    public ?int $editingSourceId = null;
    public string $sourceKey = '';
    public string $sourceName = '';
    public int $sourceSort = 100;
    public bool $sourceActive = true;

    public function mount(): void
    {
        abort_unless(CrmAccess::canViewSettings(auth()->user()), 403);
        if (! in_array($this->tab, ['statuses', 'sources'], true)) {
            $this->tab = 'statuses';
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['statuses', 'sources'], true)) {
            $this->tab = $tab;
            $this->resetForms();
        }
    }

    #[Computed]
    public function statuses()
    {
        return CrmStatus::query()->ordered()->get();
    }

    #[Computed]
    public function sources()
    {
        return CrmSource::query()->ordered()->get();
    }

    public function startCreateStatus(): void
    {
        abort_unless(CrmAccess::canManageStatuses(auth()->user()), 403);
        $this->resetStatusForm();
        $this->editingStatusId = 0;
        $this->statusSort = ((int) CrmStatus::query()->max('sort_order')) + 10;
        $this->statusColor = '#55706f';
        $this->statusActive = true;
    }

    public function startEditStatus(int $id): void
    {
        abort_unless(CrmAccess::canManageStatuses(auth()->user()), 403);        $status = CrmStatus::query()->findOrFail($id);
        $this->editingStatusId = $status->id;
        $this->statusKey = $status->key;
        $this->statusName = $status->name_ar;
        $this->statusColor = $status->color ?: '#55706f';
        $this->statusSort = $status->sort_order;
        $this->statusActive = $status->is_active;
        $this->statusDefault = $status->is_default;
        $this->statusInitial = $status->is_initial;
        $this->statusWon = $status->is_won;
        $this->statusLost = $status->is_lost;
        $this->statusClosed = $status->is_closed || $status->is_won || $status->is_lost;
    }

    public function saveStatus(): void
    {
        abort_unless(CrmAccess::canManageStatuses(auth()->user()), 403);
        $isCreate = $this->editingStatusId === 0;
        $existing = $isCreate ? null : CrmStatus::query()->findOrFail((int) $this->editingStatusId);
        $old = $existing ? [
            'code' => $existing->key,
            'name_ar' => $existing->name_ar,
            'color' => $existing->color,
            'sort_order' => $existing->sort_order,
            'is_active' => $existing->is_active,
            'is_default' => $existing->is_default,
            'is_won' => $existing->is_won,
            'is_lost' => $existing->is_lost,
            'is_closed' => $existing->is_closed,
        ] : null;

        if ($isCreate && trim($this->statusKey) === '') {
            $this->statusKey = CrmOptions::makeKey($this->statusName);
        }

        $validated = $this->validate([
            'statusKey' => [
                'required',
                'string',
                'max:40',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('crm_statuses', 'key')->ignore($existing?->id),
            ],
            'statusName' => ['required', 'string', 'max:120'],
            'statusColor' => ['required', 'string', 'max:16'],
            'statusSort' => ['required', 'integer', 'min:1', 'max:9999'],
            'statusActive' => ['boolean'],
            'statusDefault' => ['boolean'],
            'statusInitial' => ['boolean'],
            'statusWon' => ['boolean'],
            'statusLost' => ['boolean'],
            'statusClosed' => ['boolean'],
        ], [
            'statusKey.regex' => 'مفتاح الحالة يجب أن يكون بالإنجليزية الصغيرة والأرقام وشرطة سفلية فقط.',
        ], [
            'statusKey' => 'مفتاح الحالة',
            'statusName' => 'اسم الحالة',
            'statusColor' => 'اللون',
            'statusSort' => 'الترتيب',
        ]);

        if ($validated['statusWon'] && $validated['statusLost']) {
            $this->addError('statusWon', 'لا يمكن أن تكون الحالة تحويلاً ومفقودة في نفس الوقت.');

            return;
        }

        if (! $validated['statusActive'] && $validated['statusDefault']) {
            $this->addError('statusActive', 'لا يمكن تعطيل الحالة الافتراضية.');

            return;
        }

        DB::transaction(function () use ($validated, $isCreate, $existing, $old) {
            if ($validated['statusDefault']) {
                CrmStatus::query()->update(['is_default' => false]);
            }
            if ($validated['statusInitial']) {
                CrmStatus::query()->update(['is_initial' => false]);
            }

            $payload = [
                'key' => $validated['statusKey'],
                'name_ar' => $validated['statusName'],
                'color' => $validated['statusColor'],
                'sort_order' => $validated['statusSort'],
                'is_active' => $validated['statusActive'],
                'is_default' => $validated['statusDefault'],
                'is_initial' => $validated['statusInitial'],
                'is_won' => $validated['statusWon'],
                'is_lost' => $validated['statusLost'],
                'is_closed' => $validated['statusClosed'] || $validated['statusWon'] || $validated['statusLost'],
            ];

            if ($isCreate) {
                $saved = CrmStatus::query()->create([...$payload, 'is_system' => false]);
            } else {
                if ($existing->is_system) {
                    unset($payload['key']);
                }
                $existing->update($payload);
                $saved = $existing->refresh();
            }

            if (! CrmStatus::query()->where('is_default', true)->where('is_active', true)->exists()) {
                $fallback = CrmStatus::query()->active()->ordered()->first();
                $fallback?->update(['is_default' => true]);
            }

            app(CrmAuditService::class)->statusOptionChanged($saved, $isCreate ? 'created' : 'updated', $old, auth()->user());
        });

        CrmOptions::forgetCache();
        unset($this->statuses);
        $this->resetStatusForm();
        session()->flash('crm_settings_success', 'تم حفظ الحالة بنجاح.');
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(CrmAccess::canManageStatuses(auth()->user()), 403);
        $status = CrmStatus::query()->findOrFail($id);
        if ($status->is_default && $status->is_active) {
            session()->flash('crm_settings_error', 'لا يمكن تعطيل الحالة الافتراضية.');

            return;
        }
        $status->update(['is_active' => ! $status->is_active]);
        app(CrmAuditService::class)->statusOptionChanged($status->refresh(), 'toggled', null, auth()->user());
        CrmOptions::forgetCache();
        unset($this->statuses);
    }

    public function deleteStatus(int $id): void
    {
        abort_unless(CrmAccess::canManageStatuses(auth()->user()), 403);
        $status = CrmStatus::query()->findOrFail($id);
        if ($status->is_system) {
            session()->flash('crm_settings_error', 'لا يمكن حذف حالة نظامية. يمكنك تعطيلها أو تعديل اسمها.');

            return;
        }
        if ($status->is_default) {
            session()->flash('crm_settings_error', 'لا يمكن حذف الحالة الافتراضية.');

            return;
        }
        $usage = CrmContact::query()->where('status', $status->key)->count();
        if ($usage > 0) {
            session()->flash('crm_settings_error', "لا يمكن الحذف: مرتبطة بـ {$usage} عميل. عطّلها أو انقل العملاء أولاً.");

            return;
        }
        $status->delete();
        app(CrmAuditService::class)->statusOptionChanged($status, 'deleted', [
            'code' => $status->key,
            'name_ar' => $status->name_ar,
        ], auth()->user());
        CrmOptions::forgetCache();
        unset($this->statuses);
        session()->flash('crm_settings_success', 'تم حذف الحالة.');
    }

    public function startCreateSource(): void
    {
        abort_unless(CrmAccess::canManageSources(auth()->user()), 403);
        $this->resetSourceForm();
        $this->editingSourceId = 0;
        $this->sourceSort = ((int) CrmSource::query()->max('sort_order')) + 10;
        $this->sourceActive = true;
    }

    public function startEditSource(int $id): void
    {
        abort_unless(CrmAccess::canManageSources(auth()->user()), 403);
        $source = CrmSource::query()->findOrFail($id);
        $this->editingSourceId = $source->id;
        $this->sourceKey = $source->key;
        $this->sourceName = $source->name_ar;
        $this->sourceSort = $source->sort_order;
        $this->sourceActive = $source->is_active;
    }

    public function saveSource(): void
    {
        abort_unless(CrmAccess::canManageSources(auth()->user()), 403);
        $isCreate = $this->editingSourceId === 0;
        $existing = $isCreate ? null : CrmSource::query()->findOrFail((int) $this->editingSourceId);
        $old = $existing ? [
            'code' => $existing->key,
            'name_ar' => $existing->name_ar,
            'sort_order' => $existing->sort_order,
            'is_active' => $existing->is_active,
        ] : null;

        if ($isCreate && trim($this->sourceKey) === '') {
            $this->sourceKey = CrmOptions::makeKey($this->sourceName);
        }

        $validated = $this->validate([
            'sourceKey' => [
                'required',
                'string',
                'max:40',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('crm_sources', 'key')->ignore($existing?->id),
            ],
            'sourceName' => ['required', 'string', 'max:120'],
            'sourceSort' => ['required', 'integer', 'min:1', 'max:9999'],
            'sourceActive' => ['boolean'],
        ], [
            'sourceKey.regex' => 'مفتاح المصدر يجب أن يكون بالإنجليزية الصغيرة والأرقام وشرطة سفلية فقط.',
        ], [
            'sourceKey' => 'مفتاح المصدر',
            'sourceName' => 'اسم المصدر',
            'sourceSort' => 'الترتيب',
        ]);

        if ($isCreate) {
            $source = CrmSource::query()->create([
                'key' => $validated['sourceKey'],
                'name_ar' => $validated['sourceName'],
                'sort_order' => $validated['sourceSort'],
                'is_active' => $validated['sourceActive'],
                'is_system' => false,
            ]);
        } else {
            $payload = [
                'name_ar' => $validated['sourceName'],
                'sort_order' => $validated['sourceSort'],
                'is_active' => $validated['sourceActive'],
            ];
            if (! $existing->is_system) {
                $payload['key'] = $validated['sourceKey'];
            }
            $existing->update($payload);
            $source = $existing->refresh();
        }

        app(CrmAuditService::class)->sourceOptionChanged($source, $isCreate ? 'created' : 'updated', $old, auth()->user());
        CrmOptions::forgetCache();
        unset($this->sources);
        $this->resetSourceForm();
        session()->flash('crm_settings_success', 'تم حفظ المصدر بنجاح.');
    }

    public function toggleSource(int $id): void
    {
        abort_unless(CrmAccess::canManageSources(auth()->user()), 403);
        $source = CrmSource::query()->findOrFail($id);
        $source->update(['is_active' => ! $source->is_active]);
        app(CrmAuditService::class)->sourceOptionChanged($source->refresh(), 'toggled', null, auth()->user());
        CrmOptions::forgetCache();
        unset($this->sources);
    }

    public function deleteSource(int $id): void
    {
        abort_unless(CrmAccess::canManageSources(auth()->user()), 403);
        $source = CrmSource::query()->findOrFail($id);
        if ($source->is_system) {
            session()->flash('crm_settings_error', 'لا يمكن حذف مصدر نظامي. يمكنك تعطيله أو تعديل اسمه.');

            return;
        }
        $usage = CrmContact::query()->where('source', $source->key)->count();
        if ($usage > 0) {
            session()->flash('crm_settings_error', "لا يمكن الحذف: مرتبط بـ {$usage} عميل. عطّله أو انقل العملاء أولاً.");

            return;
        }
        $source->delete();
        app(CrmAuditService::class)->sourceOptionChanged($source, 'deleted', [
            'code' => $source->key,
            'name_ar' => $source->name_ar,
        ], auth()->user());
        CrmOptions::forgetCache();
        unset($this->sources);
        session()->flash('crm_settings_success', 'تم حذف المصدر.');
    }

    public function cancelEdit(): void
    {
        $this->resetForms();
    }

    private function resetForms(): void
    {
        $this->resetStatusForm();
        $this->resetSourceForm();
    }

    private function resetStatusForm(): void
    {
        $this->editingStatusId = null;
        $this->statusKey = '';
        $this->statusName = '';
        $this->statusColor = '#55706f';
        $this->statusSort = 100;
        $this->statusActive = true;
        $this->statusDefault = false;
        $this->statusInitial = false;
        $this->statusWon = false;
        $this->statusLost = false;
        $this->statusClosed = false;
        $this->resetErrorBag();
    }

    private function resetSourceForm(): void
    {
        $this->editingSourceId = null;
        $this->sourceKey = '';
        $this->sourceName = '';
        $this->sourceSort = 100;
        $this->sourceActive = true;
        $this->resetErrorBag();
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => route('admin.crm'), 'label' => 'CRM'],
        ['label' => 'الحالات والمصادر'],
    ],
])

<div class="crm-settings">
    <header class="crm-settings__hero">
        <div>
            <span>PIPELINE CONFIG</span>
            <h1>إدارة الحالات والمصادر</h1>
            <p>أضف وعدّل واحذف مراحل مسار المبيعات ومصادر العملاء بشكل ديناميكي بدلاً من القيم الثابتة.</p>
        </div>
        <a href="{{ route('admin.crm') }}" class="crm-settings__back">العودة إلى CRM</a>
    </header>

    @if (session('crm_settings_success'))
        <div class="crm-settings__alert crm-settings__alert--ok">{{ session('crm_settings_success') }}</div>
    @endif
    @if (session('crm_settings_error'))
        <div class="crm-settings__alert crm-settings__alert--err">{{ session('crm_settings_error') }}</div>
    @endif

    <nav class="crm-settings__tabs">
        <button type="button" wire:click="setTab('statuses')" @class(['is-active' => $tab === 'statuses'])>الحالات ({{ $this->statuses->count() }})</button>
        <button type="button" wire:click="setTab('sources')" @class(['is-active' => $tab === 'sources'])>المصادر ({{ $this->sources->count() }})</button>
    </nav>

    @if ($tab === 'statuses')
        <div class="crm-settings__layout">
            <section class="crm-settings__card">
                <div class="crm-settings__head">
                    <div>
                        <h2>قائمة الحالات</h2>
                        <p>الترتيب يظهر مباشرة في مسار المبيعات وملف العميل.</p>
                    </div>
                    @if (CrmAccess::canManageStatuses(auth()->user()))
                    <button type="button" wire:click="startCreateStatus" class="crm-settings__btn crm-settings__btn--primary">+ حالة جديدة</button>
                    @endif
                </div>

                <div class="crm-settings__list">
                    @foreach ($this->statuses as $status)
                        <article wire:key="status-{{ $status->id }}" @class(['is-inactive' => ! $status->is_active])>
                            <span class="crm-settings__swatch" style="background: {{ $status->color }}"></span>
                            <div class="crm-settings__meta">
                                <strong>{{ $status->name_ar }}</strong>
                                <small dir="ltr">{{ $status->key }} · ترتيب {{ $status->sort_order }}</small>
                                <div class="crm-settings__flags">
                                    @if ($status->is_default)<em>افتراضية</em>@endif
                                    @if ($status->is_initial)<em>بداية المسار</em>@endif
                                    @if ($status->is_won)<em>تحويل</em>@endif
                                    @if ($status->is_lost)<em>فقد</em>@endif
                                    @if ($status->is_closed)<em>مغلقة</em>@endif
                                    @if ($status->is_system)<em>نظامية</em>@endif
                                    @unless ($status->is_active)<em class="is-off">معطّلة</em>@endunless
                                </div>
                            </div>
                            <div class="crm-settings__actions">
                                <button type="button" wire:click="startEditStatus({{ $status->id }})">تعديل</button>
                                <button type="button" wire:click="toggleStatus({{ $status->id }})">{{ $status->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                                @unless ($status->is_system)
                                    <button type="button" class="is-danger" wire:click="deleteStatus({{ $status->id }})" wire:confirm="حذف هذه الحالة؟">حذف</button>
                                @endunless
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($editingStatusId !== null)
                <form wire:submit="saveStatus" class="crm-settings__card crm-settings__form">
                    <div class="crm-settings__head">
                        <div>
                            <h2>{{ $editingStatusId === 0 ? 'إضافة حالة' : 'تعديل حالة' }}</h2>
                            <p>حدد سلوك الحالة داخل مسار المبيعات.</p>
                        </div>
                    </div>
                    <label><span>الاسم الظاهر *</span><input wire:model="statusName" type="text" placeholder="مثال: بانتظار الدفع"></label>
                    <label>
                        <span>المفتاح التقني *</span>
                        <input wire:model="statusKey" type="text" dir="ltr" placeholder="awaiting_payment" @disabled($editingStatusId && $this->statuses->firstWhere('id', $editingStatusId)?->is_system)>
                        <small>يُستخدم في قاعدة البيانات والاستيراد. للحالات النظامية لا يمكن تغييره.</small>
                    </label>
                    <div class="crm-settings__row">
                        <label><span>اللون</span><input wire:model="statusColor" type="color"></label>
                        <label><span>الترتيب</span><input wire:model="statusSort" type="number" min="1"></label>
                    </div>
                    <div class="crm-settings__checks">
                        <label><input wire:model="statusActive" type="checkbox"><span>نشطة</span></label>
                        <label><input wire:model="statusDefault" type="checkbox"><span>الحالة الافتراضية للعملاء الجدد</span></label>
                        <label><input wire:model="statusInitial" type="checkbox"><span>بداية المسار (قبل أول تواصل)</span></label>
                        <label><input wire:model="statusWon" type="checkbox"><span>حالة تحويل ناجح</span></label>
                        <label><input wire:model="statusLost" type="checkbox"><span>حالة فقد / غير مهتم</span></label>
                        <label><input wire:model="statusClosed" type="checkbox"><span>حالة مغلقة (لا تدخل في المتابعات المستحقة)</span></label>
                    </div>
                    @if ($errors->any())
                        <div class="crm-settings__error">{{ $errors->first() }}</div>
                    @endif
                    <div class="crm-settings__form-actions">
                        <button type="submit" class="crm-settings__btn crm-settings__btn--primary">حفظ</button>
                        <button type="button" wire:click="cancelEdit" class="crm-settings__btn">إلغاء</button>
                    </div>
                </form>
            @endif
        </div>
    @else
        <div class="crm-settings__layout">
            <section class="crm-settings__card">
                <div class="crm-settings__head">
                    <div>
                        <h2>قائمة المصادر</h2>
                        <p>تظهر في الفلاتر ونموذج الإضافة والاستيراد.</p>
                    </div>
                    @if (CrmAccess::canManageSources(auth()->user()))
                    <button type="button" wire:click="startCreateSource" class="crm-settings__btn crm-settings__btn--primary">+ مصدر جديد</button>
                    @endif
                </div>
                <div class="crm-settings__list">
                    @foreach ($this->sources as $source)
                        <article wire:key="source-{{ $source->id }}" @class(['is-inactive' => ! $source->is_active])>
                            <span class="crm-settings__swatch crm-settings__swatch--src">{{ mb_substr($source->name_ar, 0, 1) }}</span>
                            <div class="crm-settings__meta">
                                <strong>{{ $source->name_ar }}</strong>
                                <small dir="ltr">{{ $source->key }} · ترتيب {{ $source->sort_order }}</small>
                                <div class="crm-settings__flags">
                                    @if ($source->is_system)<em>نظامي</em>@endif
                                    @unless ($source->is_active)<em class="is-off">معطّل</em>@endunless
                                </div>
                            </div>
                            <div class="crm-settings__actions">
                                <button type="button" wire:click="startEditSource({{ $source->id }})">تعديل</button>
                                <button type="button" wire:click="toggleSource({{ $source->id }})">{{ $source->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                                @unless ($source->is_system)
                                    <button type="button" class="is-danger" wire:click="deleteSource({{ $source->id }})" wire:confirm="حذف هذا المصدر؟">حذف</button>
                                @endunless
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            @if ($editingSourceId !== null)
                <form wire:submit="saveSource" class="crm-settings__card crm-settings__form">
                    <div class="crm-settings__head">
                        <div>
                            <h2>{{ $editingSourceId === 0 ? 'إضافة مصدر' : 'تعديل مصدر' }}</h2>
                            <p>أضف مصادر الحملات والقنوات التي يعمل عليها فريق السيلز.</p>
                        </div>
                    </div>
                    <label><span>الاسم الظاهر *</span><input wire:model="sourceName" type="text" placeholder="مثال: إعلانات سناب"></label>
                    <label>
                        <span>المفتاح التقني *</span>
                        <input wire:model="sourceKey" type="text" dir="ltr" placeholder="snapchat_ads" @disabled($editingSourceId && $this->sources->firstWhere('id', $editingSourceId)?->is_system)>
                        <small>للمصادر النظامية (تسجيل / استيراد / يدوي) لا يمكن تغيير المفتاح.</small>
                    </label>
                    <div class="crm-settings__row">
                        <label><span>الترتيب</span><input wire:model="sourceSort" type="number" min="1"></label>
                        <label class="crm-settings__inline-check"><input wire:model="sourceActive" type="checkbox"><span>نشط</span></label>
                    </div>
                    @if ($errors->any())
                        <div class="crm-settings__error">{{ $errors->first() }}</div>
                    @endif
                    <div class="crm-settings__form-actions">
                        <button type="submit" class="crm-settings__btn crm-settings__btn--primary">حفظ</button>
                        <button type="button" wire:click="cancelEdit" class="crm-settings__btn">إلغاء</button>
                    </div>
                </form>
            @endif
        </div>
    @endif
</div>

<style>
.crm-settings{display:grid;gap:18px;direction:rtl}
.crm-settings__hero{display:flex;justify-content:space-between;align-items:center;gap:18px;padding:24px;border-radius:20px;background:linear-gradient(125deg,#102b2d,#1b5852);color:#fff}
.crm-settings__hero span{font-size:10px;letter-spacing:2px;color:#8ad7ca}
.crm-settings__hero h1{margin:7px 0;font-size:27px}
.crm-settings__hero p{margin:0;color:#d4e7e3;max-width:640px}
.crm-settings__back{padding:10px 14px;border-radius:10px;background:#fff;color:#1d4d49;text-decoration:none;font-weight:800;white-space:nowrap}
.crm-settings__alert{padding:12px 15px;border-radius:11px;font-weight:700}
.crm-settings__alert--ok{background:#e3f7ec;color:#17633f}
.crm-settings__alert--err{background:#feeceb;color:#a43e37}
.crm-settings__tabs{display:flex;gap:8px}
.crm-settings__tabs button{border:1px solid #dce7e5;background:#fff;border-radius:999px;padding:10px 16px;font-weight:800;color:#4f6764;cursor:pointer}
.crm-settings__tabs button.is-active{background:#1b5651;border-color:#1b5651;color:#fff}
.crm-settings__layout{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(300px,.8fr);gap:16px;align-items:start}
.crm-settings__card{background:#fff;border:1px solid #e1eae8;border-radius:16px;padding:18px;box-shadow:0 8px 24px #183d3b0a}
.crm-settings__head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}
.crm-settings__head h2{margin:0 0 3px;font-size:18px;color:#193e3b}
.crm-settings__head p{margin:0;font-size:12px;color:#7a8b89}
.crm-settings__btn{border:1px solid #d9e3e1;background:#fff;border-radius:10px;padding:10px 14px;font-weight:800;cursor:pointer;color:#255550}
.crm-settings__btn--primary{background:#d8a633;border-color:#d8a633;color:#183a37}
.crm-settings__list{display:grid;gap:8px}
.crm-settings__list article{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;border:1px solid #e3ebe9;border-radius:12px;padding:12px}
.crm-settings__list article.is-inactive{opacity:.55;background:#f7f8f8}
.crm-settings__swatch{width:34px;height:34px;border-radius:10px;display:block}
.crm-settings__swatch--src{display:grid;place-items:center;background:#e7f3f0;color:#1d625b;font-weight:900}
.crm-settings__meta{display:grid;gap:2px}
.crm-settings__meta strong{color:#1d4642}
.crm-settings__meta small{color:#7d8d8b}
.crm-settings__flags{display:flex;flex-wrap:wrap;gap:5px;margin-top:4px}
.crm-settings__flags em{font-style:normal;font-size:10px;padding:3px 7px;border-radius:999px;background:#eef5f3;color:#3f6964;font-weight:800}
.crm-settings__flags em.is-off{background:#feeceb;color:#a43e37}
.crm-settings__actions{display:flex;gap:5px;flex-wrap:wrap}
.crm-settings__actions button{border:1px solid #d9e3e1;background:#fff;border-radius:8px;padding:7px 9px;cursor:pointer;font-weight:700}
.crm-settings__actions button.is-danger{color:#b23d37}
.crm-settings__form{display:grid;gap:12px;position:sticky;top:18px}
.crm-settings__form label{display:grid;gap:5px}
.crm-settings__form label>span{font-size:11px;font-weight:800;color:#617573}
.crm-settings__form input[type=text],.crm-settings__form input[type=number],.crm-settings__form input[type=color]{width:100%;border:1px solid #dbe6e4;border-radius:10px;padding:10px}
.crm-settings__form small{font-size:11px;color:#7a8b89}
.crm-settings__row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.crm-settings__checks{display:grid;gap:8px}
.crm-settings__checks label,.crm-settings__inline-check{display:flex!important;align-items:center;gap:8px;grid-template-columns:none}
.crm-settings__checks input,.crm-settings__inline-check input{width:auto}
.crm-settings__error{color:#b42318;font-weight:700}
.crm-settings__form-actions{display:flex;gap:8px}
@media(max-width:980px){.crm-settings__layout{grid-template-columns:1fr}.crm-settings__form{position:static}}
@media(max-width:640px){.crm-settings__hero{flex-direction:column;align-items:flex-start}.crm-settings__list article{grid-template-columns:auto 1fr}.crm-settings__actions{grid-column:1/-1}.crm-settings__row{grid-template-columns:1fr}}
</style>

@include('partials.admin.shell-end')
