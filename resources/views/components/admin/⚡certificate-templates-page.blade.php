<?php

use App\Models\CertificateTemplate;
use App\Services\CertificateTemplateService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'قوالب الشهادات',
    'adminPageDesc' => 'تصميم الشهادات والتحكم في المتغيرات والطباعة',
    'adminLayout' => 'app',
])]
#[Title('منشئ الشهادات | لوحة التحكم')]
class extends Component
{
    public string $name = '';

    public string $description = '';

    public string $orientation = 'landscape';

    public ?string $message = null;

    public string $messageKind = 'info';

    public ?int $deleteTemplateId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificate-templates.manage'), 403);
    }

    #[Computed]
    public function templates()
    {
        return CertificateTemplate::query()
            ->withCount('certificates')
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function createTemplate(CertificateTemplateService $service): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'orientation' => ['required', 'in:landscape,portrait'],
        ]);

        $template = $service->create($validated, auth()->user());

        $this->redirectRoute('admin.certificate-templates.builder', ['template' => $template], navigate: true);
    }

    public function duplicate(int $templateId, CertificateTemplateService $service): void
    {
        $template = CertificateTemplate::query()->findOrFail($templateId);
        $copy = $service->duplicate($template, auth()->user());
        unset($this->templates);
        $this->message = 'تم إنشاء نسخة جديدة من القالب.';
        $this->redirectRoute('admin.certificate-templates.builder', ['template' => $copy], navigate: true);
    }

    public function setDefault(int $templateId, CertificateTemplateService $service): void
    {
        $template = CertificateTemplate::query()->findOrFail($templateId);
        $service->setDefault($template);
        unset($this->templates);
        $this->message = 'تم اعتماد القالب كقالب افتراضي للإصدارات الجديدة.';
    }

    public function toggleStatus(int $templateId): void
    {
        $template = CertificateTemplate::query()->findOrFail($templateId);
        $template->update([
            'status' => $template->status === 'active' ? 'draft' : 'active',
            'updated_by' => auth()->id(),
        ]);
        unset($this->templates);
        $this->message = $template->fresh()->status === 'active' ? 'تم تفعيل القالب.' : 'تم تحويل القالب إلى مسودة.';
    }

    public function confirmDelete(int $templateId): void
    {
        $template = CertificateTemplate::query()->findOrFail($templateId);

        if ($template->status === 'active') {
            $this->messageKind = 'danger';
            $this->message = 'يجب تعطيل القالب أولاً قبل حذفه.';

            return;
        }

        $this->deleteTemplateId = $template->id;
    }

    public function cancelDelete(): void
    {
        $this->deleteTemplateId = null;
    }

    public function deleteTemplate(CertificateTemplateService $service): void
    {
        abort_unless(auth()->user()?->canAdmin('certificate-templates.manage'), 403);

        $template = CertificateTemplate::query()->findOrFail($this->deleteTemplateId);
        $service->delete($template, auth()->user());

        $this->deleteTemplateId = null;
        unset($this->templates);
        $this->messageKind = 'info';
        $this->message = 'تم حذف القالب مع الاحتفاظ ببيانات الشهادات الصادرة سابقاً.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.certificates'),
    'shellActiveHeader' => 'students',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.certificates'), 'label' => 'الشهادات'],
        ['label' => 'القوالب'],
    ],
])

<section class="cert-template-hero">
    <div>
        <span class="cert-template-hero__eyebrow">Certificate Studio</span>
        <h1>منشئ قوالب الشهادات</h1>
        <p>ارفع صورة الشهادة، ثم ضع أسماء الطلاب والبرامج والتواريخ ورمز QR في مواضعها بدقة كاملة.</p>
    </div>
    <div class="cert-template-hero__actions">
        <a href="{{ route('admin.certificates') }}" class="admin-btn-secondary admin-btn-secondary--sm">الشهادات الصادرة</a>
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" onclick="document.getElementById('new-template-card')?.scrollIntoView({behavior:'smooth'})">
            <i class="fa-solid fa-plus"></i> قالب جديد
        </button>
    </div>
</section>

@if ($message)
    <div class="admin-alert admin-alert--{{ $messageKind === 'danger' ? 'danger' : 'info' }} is-visible" role="status">{{ $message }}</div>
@endif

<section class="cert-template-stats">
    <article><i class="fa-solid fa-layer-group"></i><div><strong>{{ $this->templates->count() }}</strong><span>إجمالي القوالب</span></div></article>
    <article><i class="fa-solid fa-circle-check"></i><div><strong>{{ $this->templates->where('status', 'active')->count() }}</strong><span>قوالب فعّالة</span></div></article>
    <article><i class="fa-solid fa-award"></i><div><strong>{{ $this->templates->sum('certificates_count') }}</strong><span>شهادات صادرة</span></div></article>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>القوالب المحفوظة</h2>
            <p class="admin-crud-card__meta">القالب الافتراضي يُستخدم تلقائياً عند إصدار شهادة جديدة.</p>
        </div>
    </div>

    @if ($this->templates->isEmpty())
        <div class="cert-template-empty">
            <i class="fa-regular fa-image"></i>
            <h3>لم تُنشأ قوالب بعد</h3>
            <p>أنشئ أول قالب، ارفع خلفية الشهادة، ثم رتب المتغيرات داخل المحرر.</p>
        </div>
    @else
        <div class="cert-template-grid">
            @foreach ($this->templates as $template)
                <article class="cert-template-card" wire:key="certificate-template-{{ $template->id }}">
                    <div class="cert-template-card__preview" style="@if($template->backgroundUrl()) background-image:url('{{ $template->backgroundUrl() }}') @endif">
                        @unless ($template->backgroundUrl())
                            <i class="fa-regular fa-image"></i>
                        @endunless
                        <div class="cert-template-card__badges">
                            @if ($template->is_default)<span class="is-default"><i class="fa-solid fa-star"></i> الافتراضي</span>@endif
                            <span class="{{ $template->status === 'active' ? 'is-active' : 'is-draft' }}">{{ $template->status === 'active' ? 'فعّال' : 'مسودة' }}</span>
                        </div>
                    </div>
                    <div class="cert-template-card__body">
                        <div>
                            <h3>{{ $template->name }}</h3>
                            <p>{{ $template->description ?: 'قالب شهادة قابل للتخصيص الكامل.' }}</p>
                        </div>
                        <dl>
                            <div><dt>المقاس</dt><dd dir="ltr">{{ $template->canvas_width }} × {{ $template->canvas_height }}</dd></div>
                            <div><dt>الإصدار</dt><dd>v{{ $template->version }}</dd></div>
                            <div><dt>الشهادات</dt><dd>{{ $template->certificates_count }}</dd></div>
                        </dl>
                        <div class="cert-template-card__actions">
                            <a href="{{ route('admin.certificate-templates.builder', $template) }}" class="admin-btn-primary admin-btn-primary--sm">
                                <i class="fa-solid fa-pen-ruler"></i> فتح المحرر
                            </a>
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="duplicate({{ $template->id }})">نسخ</button>
                            @unless ($template->is_default)
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="setDefault({{ $template->id }})">اعتماد</button>
                            @endunless
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleStatus({{ $template->id }})">
                                {{ $template->status === 'active' ? 'تعطيل' : 'تفعيل' }}
                            </button>
                            <button
                                type="button"
                                class="cert-template-delete"
                                wire:click="confirmDelete({{ $template->id }})"
                                @disabled($template->status === 'active')
                                title="{{ $template->status === 'active' ? 'عطّل القالب أولاً لتتمكن من حذفه' : 'حذف القالب' }}"
                            >
                                <i class="fa-regular fa-trash-can"></i> حذف
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-crud-card" id="new-template-card">
    <div class="admin-crud-card__head">
        <h2>إنشاء قالب جديد</h2>
        <p class="admin-crud-card__meta">ستنتقل مباشرة إلى المحرر المرئي بعد الإنشاء.</p>
    </div>
    <form wire:submit="createTemplate">
        <div class="admin-form-grid admin-form-grid--2">
            <div class="admin-field">
                <label for="template-name">اسم القالب</label>
                <input id="template-name" class="admin-control" wire:model="name" placeholder="مثال: شهادة الدبلوم المهني">
                @error('name')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label for="template-orientation">اتجاه الشهادة</label>
                <select id="template-orientation" class="admin-control" wire:model="orientation">
                    <option value="landscape">أفقي — 1123 × 794</option>
                    <option value="portrait">رأسي — 794 × 1123</option>
                </select>
            </div>
            <div class="admin-field admin-field--wide">
                <label for="template-description">وصف داخلي</label>
                <textarea id="template-description" class="admin-control" rows="3" wire:model="description" placeholder="الاستخدام المخصص لهذا القالب..."></textarea>
            </div>
        </div>
        <div class="admin-filter-actions" style="margin-top:1rem">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm"><i class="fa-solid fa-wand-magic-sparkles"></i> إنشاء وفتح المحرر</button>
        </div>
    </form>
</section>

@if ($deleteTemplateId)
    @php($deleteTemplate = $this->templates->firstWhere('id', $deleteTemplateId))
    <div class="cert-template-modal" wire:click.self="cancelDelete">
        <section role="dialog" aria-modal="true" aria-labelledby="delete-template-title">
            <div class="cert-template-modal__icon"><i class="fa-regular fa-trash-can"></i></div>
            <h2 id="delete-template-title">حذف قالب الشهادة؟</h2>
            <p>سيتم حذف قالب <strong>{{ $deleteTemplate?->name }}</strong>. لن تتأثر الشهادات الصادرة سابقاً لأنها تحتفظ بنسخة ثابتة من التصميم.</p>
            <div class="cert-template-modal__warning"><i class="fa-solid fa-circle-info"></i> لا يمكن التراجع عن هذا الإجراء من لوحة التحكم.</div>
            <div class="admin-filter-actions">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelDelete">تراجع</button>
                <button type="button" class="cert-template-modal__delete" wire:click="deleteTemplate">تأكيد الحذف</button>
            </div>
        </section>
    </div>
@endif

@include('partials.admin.shell-end')

@push('styles')
<style>
    .cert-template-hero{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;margin-bottom:1rem;padding:1.5rem;border-radius:20px;background:radial-gradient(40rem 18rem at 110% -20%,rgba(250,204,21,.22),transparent 55%),linear-gradient(135deg,#103c2b,#17603f);color:#fff;box-shadow:0 16px 40px rgba(16,60,43,.22)}
    .cert-template-hero__eyebrow{color:#fde68a;font-size:.68rem;font-weight:900;letter-spacing:.08em}.cert-template-hero h1{margin:.25rem 0 .4rem;color:#fff;font-size:1.4rem;font-weight:900}.cert-template-hero p{max-width:48rem;margin:0;color:rgba(255,255,255,.8);font-size:.78rem;line-height:1.9}.cert-template-hero__actions{display:flex;gap:.5rem;flex-wrap:wrap}
    .cert-template-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-bottom:1rem}.cert-template-stats article{display:flex;align-items:center;gap:.8rem;padding:1rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.cert-template-stats i{display:grid;place-items:center;width:2.5rem;height:2.5rem;border-radius:11px;background:#ecfdf5;color:#15803d}.cert-template-stats strong,.cert-template-stats span{display:block}.cert-template-stats strong{font-size:1.15rem;color:#17251f}.cert-template-stats span{font-size:.67rem;color:#64748b}
    .cert-template-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem}.cert-template-card{overflow:hidden;border:1px solid #e2e8f0;border-radius:16px;background:#fff;transition:transform .2s ease,box-shadow .2s ease}.cert-template-card:hover{transform:translateY(-3px);box-shadow:0 16px 34px rgba(15,23,42,.1)}.cert-template-card__preview{position:relative;display:grid;place-items:center;height:180px;background-color:#f1f5f9;background-position:center;background-repeat:no-repeat;background-size:contain;color:#94a3b8;font-size:2rem}.cert-template-card__badges{position:absolute;inset:.7rem .7rem auto;display:flex;justify-content:space-between;gap:.4rem}.cert-template-card__badges span{padding:.28rem .55rem;border-radius:999px;font-size:.61rem;font-weight:900;box-shadow:0 3px 10px rgba(0,0,0,.12)}.cert-template-card__badges .is-default{background:#fef3c7;color:#92400e}.cert-template-card__badges .is-active{margin-inline-start:auto;background:#dcfce7;color:#166534}.cert-template-card__badges .is-draft{margin-inline-start:auto;background:#e2e8f0;color:#475569}
    .cert-template-card__body{display:grid;gap:.75rem;padding:1rem}.cert-template-card h3{margin:0;color:#17251f;font-size:.92rem;font-weight:900}.cert-template-card p{margin:.25rem 0 0;color:#64748b;font-size:.68rem;line-height:1.7}.cert-template-card dl{display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem;margin:0}.cert-template-card dl div{padding:.45rem;border-radius:8px;background:#f8fafc;text-align:center}.cert-template-card dt{font-size:.57rem;color:#94a3b8}.cert-template-card dd{margin:.12rem 0 0;font-size:.67rem;font-weight:800;color:#334155}.cert-template-card__actions{display:flex;gap:.4rem;flex-wrap:wrap}
    .cert-template-delete{display:inline-flex;align-items:center;gap:.3rem;padding:.42rem .7rem;border:1px solid #fecaca;border-radius:8px;background:#fff;color:#b91c1c;font:800 .65rem/1 inherit;cursor:pointer}.cert-template-delete:hover{background:#fef2f2}.cert-template-delete:disabled{border-color:#e2e8f0;background:#f8fafc;color:#94a3b8;cursor:not-allowed}
    .cert-template-modal{position:fixed;z-index:10500;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.64);backdrop-filter:blur(5px)}.cert-template-modal>section{width:min(450px,100%);padding:1.4rem;border-radius:20px;background:#fff;box-shadow:0 30px 80px rgba(15,23,42,.38)}.cert-template-modal__icon{display:grid;place-items:center;width:3.2rem;height:3.2rem;margin:0 auto .7rem;border-radius:50%;background:#fee2e2;color:#b91c1c;font-size:1.15rem}.cert-template-modal h2{margin:0;text-align:center;color:#17251f;font-size:1rem}.cert-template-modal p{margin:.5rem 0 1rem;text-align:center;color:#64748b;font-size:.72rem;line-height:1.8}.cert-template-modal__warning{display:flex;align-items:center;gap:.45rem;margin-bottom:1rem;padding:.65rem .75rem;border-radius:10px;background:#fff7ed;color:#9a3412;font-size:.65rem}.cert-template-modal__delete{padding:.5rem .8rem;border:0;border-radius:8px;background:#b91c1c;color:#fff;font:900 .68rem/1 inherit;cursor:pointer}
    .cert-template-empty{padding:3rem 1rem;text-align:center;color:#64748b}.cert-template-empty i{font-size:2.2rem;color:#94a3b8}.cert-template-empty h3{margin:.7rem 0 .3rem;color:#334155}.cert-template-empty p{margin:0;font-size:.75rem}
    .admin-form-grid--2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.admin-field--wide{grid-column:1/-1}
    @media(max-width:767px){.cert-template-hero{align-items:flex-start;flex-direction:column}.cert-template-stats{grid-template-columns:1fr}.admin-form-grid--2{grid-template-columns:1fr}}
</style>
@endpush
