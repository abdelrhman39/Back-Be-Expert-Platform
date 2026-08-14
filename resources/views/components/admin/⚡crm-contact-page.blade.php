<?php

use App\Models\AcademicProgram;
use App\Models\CrmActivity;
use App\Models\CrmContact;
use App\Services\CrmAssignmentService;
use App\Services\CrmAuditService;
use App\Services\CrmContactStatusService;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('ملف العميل | CRM')]
class extends Component
{
    use WithFileUploads;

    public CrmContact $contact;
    public string $status = '';
    public string $priority = '';
    public string $program = '';
    public string $owner = '';
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $city = '';
    public string $notes = '';
    public bool $doNotContact = false;
    public string $nextFollowUp = '';
    public string $lostReason = '';
    public string $activityType = 'call';
    public string $activityOutcome = 'answered';
    public string $activitySubject = '';
    public string $activityContent = '';
    public string $activityNextFollowUp = '';
    public $paymentReceipt = null;

    public function mount(CrmContact $contact): void
    {
        abort_unless(CrmAccess::canAccessContact(auth()->user(), $contact), 403);
        $this->contact = $contact;
        $this->fillForm();
    }

    private function fillForm(): void
    {
        $this->contact->refresh();
        $this->status = $this->contact->status;
        $this->priority = $this->contact->priority;
        $this->program = (string) ($this->contact->program_id ?: '');
        $this->owner = (string) ($this->contact->owner_id ?: '');
        $this->name = $this->contact->name;
        $this->email = $this->contact->email ?: '';
        $this->phone = $this->contact->phone ?: '';
        $this->company = $this->contact->company ?: '';
        $this->city = $this->contact->city ?: '';
        $this->notes = $this->contact->notes ?: '';
        $this->doNotContact = $this->contact->do_not_contact;
        $this->nextFollowUp = $this->contact->next_follow_up_at?->format('Y-m-d\TH:i') ?: '';
        $this->lostReason = $this->contact->lost_reason ?: '';
    }

    #[Computed]
    public function activities()
    {
        return $this->contact->activities()->with('user:id,name,name_ar')->latest()->limit(100)->get();
    }

    #[Computed]
    public function auditLogs()
    {
        return app(\App\Services\AuditLogService::class)->recentForSubject($this->contact, 30);
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

    public function saveContact(): void
    {
        $canUpdate = CrmAccess::canUpdate(auth()->user());
        $canChangeStatus = CrmAccess::canChangeStatus(auth()->user());
        abort_unless($canUpdate || $canChangeStatus || CrmAccess::canAssign(auth()->user()), 403);

        $validated = $this->validate([
            'name' => [$canUpdate ? 'required' : 'nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'in:'.implode(',', array_keys(CrmOptions::statuses(false)))],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'program' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'nextFollowUp' => ['nullable', 'date'],
            'lostReason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'doNotContact' => ['boolean'],
        ]);

        $audit = app(CrmAuditService::class);
        $oldSnapshot = $audit->contactSnapshot($this->contact);
        $oldStatus = $this->contact->status;

        if ($oldStatus !== $validated['status']) {
            abort_unless($canChangeStatus, 403);
        }

        $payload = [];
        if ($canUpdate) {
            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'] ?: null,
                'phone' => $validated['phone'] ?: null,
                'company' => $validated['company'] ?: null,
                'city' => $validated['city'] ?: null,
                'priority' => $validated['priority'],
                'program_id' => $validated['program'] ?: null,
                'next_follow_up_at' => $validated['nextFollowUp'] ?: null,
                'notes' => $validated['notes'] ?: null,
                'do_not_contact' => $validated['doNotContact'],
            ];
        }
        if ($canChangeStatus) {
            if ($this->paymentReceipt) {
                $this->validate([
                    'paymentReceipt' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ], [
                    'paymentReceipt.mimes' => 'صيغة الإيصال يجب أن تكون صورة أو PDF.',
                    'paymentReceipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميجابايت.',
                ]);
            }

            if ($oldStatus !== $validated['status']) {
                if (CrmOptions::requiresPaymentReceipt($validated['status']) && ! $this->paymentReceipt && ! $this->contact->hasPaymentReceipt()) {
                    $this->addError('paymentReceipt', 'أرفق إيصال التحويل أو السداد قبل تحديث الحالة إلى «تم السداد».');

                    return;
                }

                if (
                    ($oldStatus === 'paid' || CrmOptions::isWon($oldStatus))
                    && $validated['status'] !== 'paid'
                    && ! CrmOptions::isWon($validated['status'])
                    && blank($validated['lostReason'] ?? null)
                ) {
                    $this->addError('lostReason', 'بعد تأكيد السداد يجب إدخال سبب تغيير المرحلة. الطالب لن يُسحب من البرنامج تلقائياً.');

                    return;
                }

                try {
                    app(CrmContactStatusService::class)->change(
                        $this->contact,
                        $validated['status'],
                        auth()->user(),
                        $validated['lostReason'] ?: null,
                        $this->paymentReceipt,
                    );
                } catch (\Illuminate\Validation\ValidationException $e) {
                    foreach ($e->errors() as $field => $messages) {
                        foreach ($messages as $message) {
                            $this->addError($field === 'lostReason' ? 'lostReason' : $field, $message);
                        }
                    }

                    return;
                }
                $this->contact->refresh();
                $this->reset('paymentReceipt');
            } elseif (CrmOptions::isLost($validated['status'])) {
                $this->contact->update(['lost_reason' => $validated['lostReason'] ?: null]);
            } elseif ($this->paymentReceipt) {
                app(CrmContactStatusService::class)->attachReceipt($this->contact, $this->paymentReceipt, auth()->user());
                $this->contact->refresh();
                $this->reset('paymentReceipt');
            }
        }
        if ($payload !== []) {
            $this->contact->update($payload);
        }

        if (CrmAccess::canAssign(auth()->user()) && $this->owner !== (string) ($this->contact->owner_id ?: '')) {
            $this->validate(['owner' => ['required', 'integer']]);
            abort_unless($this->salesUsers->contains('id', (int) $this->owner), 422);
            app(CrmAssignmentService::class)->assign($this->contact, (int) $this->owner, auth()->user(), 'إعادة توزيع من ملف العميل');
        }

        $this->contact->refresh();
        $newSnapshot = $audit->contactSnapshot($this->contact);
        $audit->contactUpdated($this->contact, $oldSnapshot, $newSnapshot, auth()->user());

        $this->fillForm();
        unset($this->activities, $this->auditLogs);
        session()->flash('crm_success', 'تم تحديث ملف العميل.');
    }

    public function addActivity(): void
    {
        abort_unless(CrmAccess::canLogActivity(auth()->user()), 403);
        $validated = $this->validate([
            'activityType' => ['required', 'in:call,whatsapp,email,meeting,note'],
            'activityOutcome' => ['nullable', 'in:answered,no_answer,busy,callback,interested,not_interested,completed'],
            'activitySubject' => ['nullable', 'string', 'max:255'],
            'activityContent' => ['required', 'string', 'max:5000'],
            'activityNextFollowUp' => ['nullable', 'date'],
        ]);

        $now = now();
        $activity = CrmActivity::query()->create([
            'contact_id' => $this->contact->id,
            'user_id' => auth()->id(),
            'type' => $validated['activityType'],
            'subject' => $validated['activitySubject'] ?: CrmOptions::label(CrmOptions::activityTypes(), $validated['activityType']),
            'content' => $validated['activityContent'],
            'outcome' => $validated['activityOutcome'] ?: null,
            'completed_at' => $now,
        ]);

        $updates = [
            'last_activity_at' => $now,
            'last_contacted_at' => $validated['activityType'] !== 'note' ? $now : $this->contact->last_contacted_at,
            'first_contacted_at' => $validated['activityType'] !== 'note' ? ($this->contact->first_contacted_at ?: $now) : $this->contact->first_contacted_at,
        ];
        if ($validated['activityNextFollowUp']) {
            $updates['next_follow_up_at'] = $validated['activityNextFollowUp'];
        }
        $oldStatus = $this->contact->status;
        if (CrmOptions::isDefault($this->contact->status) && $validated['activityType'] !== 'note' && CrmAccess::canChangeStatus(auth()->user())) {
            $updates['status'] = CrmOptions::contactedStatusKey();
        }
        $this->contact->update($updates);
        app(CrmAuditService::class)->activityLogged($activity, $this->contact->refresh(), auth()->user());
        if (isset($updates['status']) && $updates['status'] !== $oldStatus) {
            app(CrmAuditService::class)->statusChanged($this->contact, $oldStatus, $updates['status'], auth()->user());
        }

        $this->reset(['activitySubject', 'activityContent', 'activityNextFollowUp']);
        $this->activityType = 'call';
        $this->activityOutcome = 'answered';
        $this->fillForm();
        unset($this->activities, $this->auditLogs);
        session()->flash('crm_success', 'تم تسجيل النشاط وتحديث خط المتابعة.');
    }

    public function deleteContact(): void
    {
        abort_unless(CrmAccess::canDelete(auth()->user()), 403);
        $contact = $this->contact;
        app(CrmAuditService::class)->contactDeleted($contact, auth()->user());
        $contact->delete();
        session()->flash('crm_success', 'تم حذف العميل.');
        $this->redirect(route('admin.crm'), navigate: true);
    }

    public function quickStatus(string $status): void
    {
        if (! array_key_exists($status, CrmOptions::statuses())) {
            return;
        }

        if (CrmOptions::requiresPaymentReceipt($status) && ! $this->paymentReceipt && ! $this->contact->hasPaymentReceipt()) {
            $this->status = $status;
            $this->addError('paymentReceipt', 'أرفق إيصال السداد من نموذج البيانات ثم احفظ، أو ارفعه قبل اختيار «تم السداد».');

            return;
        }

        if (! app(CrmContactStatusService::class)->change($this->contact, $status, auth()->user(), $this->lostReason ?: null, $this->paymentReceipt)) {
            return;
        }

        $this->reset('paymentReceipt');
        $this->fillForm();
        unset($this->activities, $this->auditLogs);
        session()->flash('crm_success', 'تم تحديث مرحلة العميل.');
    }

    public function uploadPaymentReceipt(): void
    {
        $this->validate([
            'paymentReceipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'paymentReceipt.required' => 'اختر ملف الإيصال أولاً.',
        ]);

        app(CrmContactStatusService::class)->attachReceipt($this->contact, $this->paymentReceipt, auth()->user());
        $this->reset('paymentReceipt');
        $this->fillForm();
        unset($this->activities, $this->auditLogs);
        session()->flash('crm_success', 'تم إرفاق إيصال السداد.');
    }

    public function downloadPaymentReceipt(): mixed
    {
        abort_unless(CrmAccess::canAccessContact(auth()->user(), $this->contact), 403);
        abort_unless($this->contact->hasPaymentReceipt(), 404);
        abort_unless(Storage::disk('local')->exists($this->contact->payment_receipt_path), 404);

        return Storage::disk('local')->download(
            $this->contact->payment_receipt_path,
            $this->contact->payment_receipt_name ?: 'payment-receipt'
        );
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => route('admin.crm'), 'label' => 'CRM'],
        ['label' => $contact->name],
    ],
])

<div class="crm-profile">
    <header class="crm-profile__hero">
        <div class="crm-profile__identity">
            <span class="crm-profile__avatar">{{ mb_substr($contact->name, 0, 1) }}</span>
            <div><span class="crm-profile__code">CRM-{{ str_pad($contact->id, 6, '0', STR_PAD_LEFT) }}</span><h1>{{ $contact->name }}</h1><p>{{ $contact->program?->name_ar ?: 'بدون برنامج محدد' }} · {{ $contact->owner?->displayName() ?: 'غير موزع' }}</p></div>
        </div>
        <div class="crm-profile__actions">
            @if (!$contact->do_not_contact && $contact->phone)
                <a href="tel:{{ $contact->phone }}" class="crm-action">اتصال</a>
                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contact->phone) }}" target="_blank" rel="noopener" class="crm-action crm-action--wa">واتساب</a>
            @endif
            @if (!$contact->do_not_contact && $contact->email)<a href="mailto:{{ $contact->email }}" class="crm-action">بريد</a>@endif
            @if (CrmAccess::canDelete(auth()->user()))
                <button type="button" wire:click="deleteContact" wire:confirm="هل تريد حذف هذا العميل؟" class="crm-action crm-action--danger">حذف</button>
            @endif
            <a href="{{ route('admin.crm') }}" class="crm-action crm-action--back">العودة للقائمة</a>
        </div>
    </header>

    @if ($contact->do_not_contact)<div class="crm-warning">هذا العميل اختار عدم التواصل. أزرار التواصل معطلة حتى إزالة العلامة من بياناته.</div>@endif
    @if (session('crm_success'))<div class="crm-success">{{ session('crm_success') }}</div>@endif

    @if (CrmAccess::canChangeStatus(auth()->user()))
    <nav class="crm-quick-stage" aria-label="مراحل العميل">
        @foreach (CrmOptions::statusModels(true) as $stage)
            <button wire:click="quickStatus('{{ $stage->key }}')" @class(['is-active' => $status === $stage->key]) style="--stage-color: {{ $stage->color }}">{{ $stage->name_ar }}</button>
        @endforeach
    </nav>
    @endif

    <div class="crm-profile__grid">
        <main class="crm-profile__main">
            @if (CrmAccess::canLogActivity(auth()->user()))
            <section class="crm-card">
                <div class="crm-card__head"><div><h2>تسجيل تواصل أو متابعة</h2><p>كل تواصل يُحفظ زمنياً في ملف العميل.</p></div></div>
                <form wire:submit="addActivity">
                    <div class="crm-activity-types">
                        @foreach (array_intersect_key(CrmOptions::activityTypes(), array_flip(['call','whatsapp','email','meeting','note'])) as $key => $label)
                            <label><input wire:model.live="activityType" type="radio" value="{{ $key }}"><span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                    <div class="crm-fields crm-fields--2">
                        <label><span>نتيجة التواصل</span><select wire:model="activityOutcome">@foreach (CrmOptions::outcomes() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        <label><span>عنوان مختصر</span><input wire:model="activitySubject" type="text" placeholder="مثال: استفسار عن رسوم البرنامج"></label>
                        <label class="crm-wide"><span>تفاصيل التواصل *</span><textarea wire:model="activityContent" rows="4" placeholder="ما الذي تم مناقشته؟ وما الخطوة التالية؟"></textarea></label>
                        <label><span>موعد المتابعة القادمة</span><input wire:model="activityNextFollowUp" type="datetime-local"></label>
                    </div>
                    @error('activityContent')<div class="crm-error">{{ $message }}</div>@enderror
                    <button class="crm-save">حفظ النشاط</button>
                </form>
            </section>
            @endif

            <section class="crm-card">
                <div class="crm-card__head"><div><h2>سجل رحلة العميل</h2><p>{{ $this->activities->count() }} نشاط مسجل</p></div></div>
                <div class="crm-timeline">
                    @forelse ($this->activities as $activity)
                        <article class="crm-event">
                            <span class="crm-event__dot crm-event__dot--{{ $activity->type }}"></span>
                            <div class="crm-event__body">
                                <header><div><strong>{{ $activity->subject ?: CrmOptions::label(CrmOptions::activityTypes(), $activity->type) }}</strong>@if($activity->outcome)<span>{{ CrmOptions::label(CrmOptions::outcomes(), $activity->outcome) }}</span>@endif</div><time>{{ $activity->created_at->format('Y/m/d H:i') }}</time></header>
                                @if ($activity->content)<p>{{ $activity->content }}</p>@endif
                                <small>بواسطة {{ $activity->user?->displayName() ?: 'النظام' }}</small>
                            </div>
                        </article>
                    @empty
                        <div class="crm-empty">لم يتم تسجيل أي نشاط بعد.</div>
                    @endforelse
                </div>
            </section>

            @if (CrmAccess::canViewAudit(auth()->user()))
            <section class="crm-card">
                <div class="crm-card__head"><div><h2>سجل التدقيق</h2><p>من نفّذ ماذا وعلى أي بيانات.</p></div></div>
                <div class="crm-audit-list">
                    @forelse ($this->auditLogs as $log)
                        <article class="crm-audit-item">
                            <div>
                                <strong>{{ $log->description_ar }}</strong>
                                <small>{{ $log->user?->displayName() ?: 'النظام' }} · {{ $log->created_at->format('Y/m/d H:i') }} · IP {{ $log->ip_address ?: '—' }}</small>
                            </div>
                            <code>{{ $log->action }}</code>
                        </article>
                    @empty
                        <div class="crm-empty">لا توجد أحداث تدقيق بعد.</div>
                    @endforelse
                </div>
            </section>
            @endif
        </main>

        <aside class="crm-profile__side">
            @if (CrmAccess::canUpdate(auth()->user()) || CrmAccess::canChangeStatus(auth()->user()) || CrmAccess::canAssign(auth()->user()))
            <form wire:submit="saveContact" class="crm-card crm-sticky">
                <div class="crm-card__head"><div><h2>بيانات العميل</h2><p>المعلومات والحالة الحالية</p></div></div>
                <div class="crm-fields">
                    @if (CrmAccess::canUpdate(auth()->user()))
                    <label><span>الاسم</span><input wire:model="name" type="text"></label>
                    <label><span>الهاتف</span><input wire:model="phone" type="tel" dir="ltr"></label>
                    <label><span>البريد</span><input wire:model="email" type="email" dir="ltr"></label>
                    <label><span>الشركة</span><input wire:model="company" type="text"></label>
                    <label><span>المدينة</span><input wire:model="city" type="text"></label>
                    <label><span>البرنامج</span><select wire:model="program"><option value="">غير محدد</option>@foreach ($this->programs as $item)<option value="{{ $item->id }}">{{ $item->name_ar }}</option>@endforeach</select></label>
                    @endif
                    <div class="crm-fields crm-fields--2">
                        @if (CrmAccess::canChangeStatus(auth()->user()))
                        <label><span>الحالة</span><select wire:model.live="status">@foreach (CrmOptions::statuses() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        @endif
                        @if (CrmAccess::canUpdate(auth()->user()))
                        <label><span>الأولوية</span><select wire:model="priority">@foreach (CrmOptions::priorities() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        @endif
                    </div>
                    @if (CrmAccess::canChangeStatus(auth()->user()) && (CrmOptions::isLost($status) || $this->contact->status === 'paid' || CrmOptions::isWon($this->contact->status)))
                        <label>
                            <span>{{ ($this->contact->status === 'paid' || CrmOptions::isWon($this->contact->status)) && $status !== 'paid' ? 'سبب تغيير المرحلة بعد السداد *' : 'سبب الفقد' }}</span>
                            <input wire:model="lostReason" type="text" placeholder="مطلوب عند الخروج من «تم السداد» أو عند الفقد">
                        </label>
                    @endif
                    @if (CrmAccess::canChangeStatus(auth()->user()) || CrmAccess::canUpdate(auth()->user()))
                        <div class="crm-receipt-box">
                            <span class="crm-receipt-box__label">إيصال التحويل / السداد</span>
                            @if ($contact->hasPaymentReceipt())
                                <div class="crm-receipt-box__file">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    <div>
                                        <strong>{{ $contact->payment_receipt_name }}</strong>
                                        <small>{{ $contact->payment_receipt_uploaded_at?->format('Y/m/d H:i') }}</small>
                                    </div>
                                    <button type="button" wire:click="downloadPaymentReceipt" class="crm-action" style="background:#e8f5f1;color:#0f766e;border-color:#b7e0d6">تنزيل</button>
                                </div>
                            @endif
                            <label class="crm-receipt-box__upload">
                                <span>{{ $contact->hasPaymentReceipt() ? 'استبدال الإيصال' : 'رفع إيصال' }}@if(CrmOptions::requiresPaymentReceipt($status)) *@endif</span>
                                <input type="file" wire:model="paymentReceipt" accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf">
                            </label>
                            @error('paymentReceipt')<div class="crm-error">{{ $message }}</div>@enderror
                            <div wire:loading wire:target="paymentReceipt" class="crm-error" style="color:#0d9488">جاري تجهيز الملف…</div>
                            @if ($paymentReceipt)
                                <button type="button" class="crm-save" style="margin-top:8px;background:#0d9488;color:#fff" wire:click="uploadPaymentReceipt" wire:loading.attr="disabled">حفظ الإيصال فقط</button>
                            @endif
                            @if (CrmOptions::isPaymentStatus($status))
                                <small class="crm-receipt-box__hint">{{ CrmOptions::requiresPaymentReceipt($status) ? 'مطلوب عند حالة «تم السداد».' : 'اختياري في حالة «يريد السداد».' }}</small>
                            @endif
                        </div>
                    @endif
                    @if (CrmAccess::canUpdate(auth()->user()))
                    <label><span>المتابعة القادمة</span><input wire:model="nextFollowUp" type="datetime-local"></label>
                    @endif
                    @if (CrmAccess::canAssign(auth()->user()))
                        <label><span>موظف السيلز المسؤول</span><select wire:model="owner"><option value="">اختر موظفاً</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select></label>
                    @endif
                    @if (CrmAccess::canUpdate(auth()->user()))
                    <label><span>ملاحظات دائمة</span><textarea wire:model="notes" rows="4"></textarea></label>
                    <label class="crm-check-label"><input wire:model="doNotContact" type="checkbox"><span>عدم التواصل مع هذا العميل</span></label>
                    @endif
                </div>
                @if ($errors->any())<div class="crm-error">{{ $errors->first() }}</div>@endif
                <button class="crm-save">حفظ التغييرات</button>
            </form>
            @endif
        </aside>
    </div>
</div>

<style>
.crm-profile{display:grid;gap:18px;direction:rtl}.crm-profile__hero{border-radius:20px;padding:24px;background:linear-gradient(125deg,#102b2d,#1a5551);color:#fff;display:flex;justify-content:space-between;align-items:center;gap:20px}.crm-profile__identity{display:flex;align-items:center;gap:16px}.crm-profile__avatar{width:65px;height:65px;border-radius:19px;display:grid;place-items:center;background:#d8a633;color:#173735;font-size:27px;font-weight:900}.crm-profile__code{font-size:11px;color:#8cd7cb;letter-spacing:1px}.crm-profile h1{margin:3px 0;font-size:27px}.crm-profile__identity p{margin:0;color:#d3e5e2}.crm-profile__actions{display:flex;flex-wrap:wrap;gap:8px}.crm-action{padding:10px 14px;border-radius:10px;background:#ffffff17;border:1px solid #ffffff2d;color:#fff;text-decoration:none;font-weight:800}.crm-action--wa{background:#1e9d69}.crm-action--danger{background:#b23d37;border-color:#b23d37}.crm-action--back{background:#fff;color:#204744}.crm-success,.crm-warning{border-radius:11px;padding:12px 15px;font-weight:700}.crm-success{background:#e4f7ec;color:#16613e}.crm-warning{background:#fff2dc;color:#8c5710;border:1px solid #f1d297}.crm-quick-stage{display:grid;grid-template-columns:repeat(auto-fit,minmax(105px,1fr));gap:6px;overflow-x:auto}.crm-quick-stage button{padding:10px;border-radius:10px;border:1px solid #dce7e5;background:#fff;color:#526967;font-weight:700;cursor:pointer}.crm-quick-stage button.is-active{background:#d8a633;color:#183a38;border-color:#d8a633}.crm-profile__grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:18px;align-items:start}.crm-profile__main{display:grid;gap:18px}.crm-card{background:#fff;border:1px solid #e1eae8;border-radius:17px;padding:19px;box-shadow:0 8px 24px #193f3d0b}.crm-card__head{margin-bottom:16px}.crm-card__head h2{margin:0 0 3px;font-size:18px;color:#193d3a}.crm-card__head p{margin:0;color:#7a8988;font-size:12px}.crm-sticky{position:sticky;top:20px}.crm-fields{display:grid;gap:11px}.crm-fields--2{grid-template-columns:1fr 1fr}.crm-wide{grid-column:1/-1}.crm-fields label{display:grid;gap:5px}.crm-fields label>span{font-size:11px;color:#627674;font-weight:800}.crm-fields input,.crm-fields select,.crm-fields textarea{width:100%;border:1px solid #dbe6e4;border-radius:10px;padding:10px;background:#fff;color:#243f3d}.crm-activity-types{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:13px}.crm-activity-types input{position:absolute;opacity:0}.crm-activity-types span{display:block;padding:9px 14px;border:1px solid #dbe6e4;border-radius:999px;color:#546a68;font-weight:700;cursor:pointer}.crm-activity-types input:checked+span{background:#1b5651;border-color:#1b5651;color:#fff}.crm-save{margin-top:14px;border:0;border-radius:10px;padding:11px 18px;background:#d8a633;color:#163836;font-weight:900;cursor:pointer}.crm-error{margin-top:10px;color:#b42318}.crm-receipt-box{display:grid;gap:8px;padding:12px;border:1px solid #cfe3df;border-radius:12px;background:#f4faf8}.crm-receipt-box__label{font-size:11px;color:#627674;font-weight:800}.crm-receipt-box__file{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.crm-receipt-box__file strong{display:block;color:#184844;font-size:12px}.crm-receipt-box__file small{display:block;color:#7a8988;font-size:11px}.crm-receipt-box__upload{display:grid;gap:5px}.crm-receipt-box__upload>span{font-size:11px;color:#627674;font-weight:800}.crm-receipt-box__upload input{width:100%;border:1px dashed #b7d0cb;border-radius:10px;padding:10px;background:#fff}.crm-receipt-box__hint{color:#0f766e;font-size:11px}.crm-check-label{display:flex!important;grid-template-columns:auto 1fr!important;align-items:center}.crm-check-label input{width:auto}.crm-timeline{display:grid}.crm-event{display:grid;grid-template-columns:18px 1fr;gap:11px;position:relative;padding-bottom:20px}.crm-event:not(:last-child):before{content:"";position:absolute;right:7px;top:17px;bottom:0;width:2px;background:#e3ecea}.crm-event__dot{width:15px;height:15px;border-radius:50%;background:#86a4a0;border:3px solid #e8f2f0;z-index:1}.crm-event__dot--call,.crm-event__dot--whatsapp{background:#1a9b67}.crm-event__dot--email{background:#3b82f6}.crm-event__dot--status_change{background:#d8a633}.crm-event__body{border:1px solid #e6edec;border-radius:12px;padding:12px}.crm-event__body header{display:flex;justify-content:space-between;gap:10px}.crm-event__body header>div{display:flex;align-items:center;gap:7px}.crm-event__body header span{font-size:10px;background:#edf4f2;border-radius:99px;padding:4px 7px}.crm-event__body time,.crm-event__body small{font-size:11px;color:#84928f}.crm-event__body p{margin:9px 0;color:#405957;white-space:pre-line}.crm-empty{text-align:center;color:#71817f;padding:30px}.crm-audit-list{display:grid;gap:8px}.crm-audit-item{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border:1px solid #e6edec;border-radius:11px;padding:11px}.crm-audit-item strong{display:block;color:#1d4642}.crm-audit-item small{display:block;color:#7d8d8b;margin-top:3px}.crm-audit-item code{font-size:10px;background:#eef5f3;padding:4px 6px;border-radius:6px;white-space:nowrap}
@media(max-width:1000px){.crm-profile__grid{grid-template-columns:1fr}.crm-profile__side{grid-row:1}.crm-sticky{position:static}}@media(max-width:700px){.crm-profile__hero{align-items:flex-start;flex-direction:column}.crm-profile__identity{align-items:flex-start}.crm-profile__avatar{width:50px;height:50px}.crm-fields--2{grid-template-columns:1fr}.crm-wide{grid-column:auto}.crm-event__body header{flex-direction:column}.crm-audit-item{flex-direction:column}}
</style>

@include('partials.admin.shell-end')
