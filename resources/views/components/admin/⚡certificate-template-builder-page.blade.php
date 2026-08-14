<?php

use App\Models\CertificateTemplate;
use App\Services\CertificateTemplateService;
use App\Support\CertificateVariables;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'محرر قالب الشهادة',
    'adminPageDesc' => 'رتّب النصوص والمتغيرات ورمز التحقق فوق صورة الشهادة',
    'adminLayout' => 'app',
])]
#[Title('محرر الشهادة | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public CertificateTemplate $template;

    /** @var array<int, array<string, mixed>> */
    public array $elements = [];

    /** @var array<string, mixed> */
    public array $settings = [];

    public mixed $background = null;

    public string $name = '';

    public string $description = '';

    public ?string $message = null;

    public function mount(CertificateTemplate $template): void
    {
        abort_unless(auth()->user()?->canAdmin('certificate-templates.manage'), 403);

        $this->template = $template;
        $this->elements = $template->elements ?: app(CertificateTemplateService::class)->defaultElements();
        $this->settings = array_replace(
            app(CertificateTemplateService::class)->defaultSettings(),
            $template->settings ?? [],
        );
        $this->name = $template->name;
        $this->description = $template->description ?? '';
    }

    public function save(CertificateTemplateService $service): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'settings.background_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.direction' => ['nullable', 'in:rtl,ltr'],
            'elements' => ['array', 'max:100'],
        ]);

        $this->template->update([
            'name' => trim($this->name),
            'description' => trim($this->description) ?: null,
        ]);

        $this->template = $service->saveDesign(
            $this->template,
            $this->elements,
            $this->settings,
            auth()->user(),
            $this->background,
        );
        $this->elements = $this->template->elements ?? [];
        $this->background = null;
        $this->message = 'تم حفظ التصميم وإصدار نسخة القالب v'.$this->template->version.'.';
    }

    public function activate(CertificateTemplateService $service): void
    {
        $this->save($service);
        $service->setDefault($this->template);
        $this->template->refresh();
        $this->message = 'تم حفظ القالب وتفعيله واعتماده للإصدارات الجديدة.';
    }

    public function backgroundPreview(): ?string
    {
        if ($this->background) {
            return $this->background->temporaryUrl();
        }

        return $this->template->backgroundUrl();
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
        ['href' => route('admin.certificate-templates'), 'label' => 'القوالب'],
        ['label' => $template->name],
    ],
])

@php
    $variableGroups = CertificateVariables::grouped();
    $variableSamples = CertificateVariables::samples();
    $backgroundPreview = $this->backgroundPreview();
@endphp

<div
    class="cert-builder"
    x-data="{
        elements: $wire.entangle('elements').live,
        designSettings: $wire.entangle('settings').live,
        samples: @js($variableSamples),
        canvasWidth: {{ $template->canvas_width }},
        canvasHeight: {{ $template->canvas_height }},
        selectedId: null,
        scale: 0.72,
        dragging: null,
        resizing: null,
        fit() {
            const scroll = this.$refs.canvasScroll
            if (!scroll) return
            const available = Math.max(240, scroll.clientWidth - 48)
            this.scale = Math.min(1, Math.round((available / this.canvasWidth) * 100) / 100)
        },
        get selected() { return this.elements.find(e => e.id === this.selectedId) || null },
        sampleText(content) {
            return String(content || '').replace(/\{\{\s*([a-z0-9_.-]+)\s*\}\}/gi, (_, key) => this.samples[key] || key)
        },
        select(id) { this.selectedId = id },
        addText(content = 'نص جديد') {
            const id = 'text-' + Date.now()
            this.elements.push({ id, type:'text', content, x:280, y:240, width:560, height:60, rotation:0, z_index:this.elements.length + 5, font_family:'DejaVu Sans', font_size:28, font_weight:700, color:'#111827', background:'transparent', align:'center', direction:'rtl', line_height:1.35, letter_spacing:0 })
            this.selectedId = id
        },
        addVariable(key) { this.addText(String.fromCharCode(123,123) + ' ' + key + ' ' + String.fromCharCode(125,125)) },
        addQr() {
            const id = 'qr-' + Date.now()
            this.elements.push({ id, type:'qr', variable:'certificate.verify_url', x:900, y:610, width:115, height:115, rotation:0, z_index:this.elements.length + 5, foreground:'#111827', background:'#ffffff' })
            this.selectedId = id
        },
        addLine() {
            const id = 'line-' + Date.now()
            this.elements.push({ id, type:'line', x:310, y:500, width:500, height:2, rotation:0, z_index:this.elements.length + 5, color:'#14532d' })
            this.selectedId = id
        },
        duplicateSelected() {
            if (!this.selected) return
            const copy = JSON.parse(JSON.stringify(this.selected))
            copy.id = copy.type + '-' + Date.now()
            copy.x = Number(copy.x) + 18
            copy.y = Number(copy.y) + 18
            copy.z_index = this.elements.length + 5
            this.elements.push(copy)
            this.selectedId = copy.id
        },
        removeSelected() {
            if (!this.selected) return
            this.elements = this.elements.filter(e => e.id !== this.selectedId)
            this.selectedId = null
        },
        moveLayer(direction) {
            if (!this.selected) return
            this.selected.z_index = Math.max(1, Number(this.selected.z_index || 1) + direction)
        },
        nudge(event, dx, dy) {
            if (!this.selected || ['INPUT','TEXTAREA','SELECT'].includes(event.target.tagName)) return
            event.preventDefault()
            const step = event.shiftKey ? 10 : 1
            this.selected.x = Math.max(0, Number(this.selected.x) + dx * step)
            this.selected.y = Math.max(0, Number(this.selected.y) + dy * step)
        },
        startDrag(event, element) {
            if (event.target.closest('.cert-canvas-element__resize')) return
            this.selectedId = element.id
            this.dragging = { id:element.id, startX:event.clientX, startY:event.clientY, x:Number(element.x), y:Number(element.y) }
            event.currentTarget.setPointerCapture?.(event.pointerId)
        },
        startResize(event, element) {
            event.stopPropagation()
            this.selectedId = element.id
            this.resizing = { id:element.id, startX:event.clientX, startY:event.clientY, width:Number(element.width), height:Number(element.height) }
            event.currentTarget.setPointerCapture?.(event.pointerId)
        },
        pointerMove(event) {
            if (this.dragging) {
                const element = this.elements.find(e => e.id === this.dragging.id)
                if (!element) return
                element.x = Math.max(0, Math.round(this.dragging.x + (event.clientX - this.dragging.startX) / this.scale))
                element.y = Math.max(0, Math.round(this.dragging.y + (event.clientY - this.dragging.startY) / this.scale))
            }
            if (this.resizing) {
                const element = this.elements.find(e => e.id === this.resizing.id)
                if (!element) return
                element.width = Math.max(20, Math.round(this.resizing.width + (event.clientX - this.resizing.startX) / this.scale))
                element.height = Math.max(10, Math.round(this.resizing.height + (event.clientY - this.resizing.startY) / this.scale))
            }
        },
        stopPointer() { this.dragging = null; this.resizing = null },
    }"
    @pointermove.window="pointerMove($event)"
    @pointerup.window="stopPointer()"
    @keydown.delete.window="if (!['INPUT','TEXTAREA'].includes($event.target.tagName)) removeSelected()"
    @keydown.arrow-up.window="nudge($event, 0, -1)"
    @keydown.arrow-down.window="nudge($event, 0, 1)"
    @keydown.arrow-right.window="nudge($event, 1, 0)"
    @keydown.arrow-left.window="nudge($event, -1, 0)"
    @resize.window.debounce.150ms="fit()"
    x-init="$nextTick(() => fit())"
>
    <header class="cert-builder__topbar">
        <div class="cert-builder__title">
            <a href="{{ route('admin.certificate-templates') }}" aria-label="العودة"><i class="fa-solid fa-arrow-right"></i></a>
            <div>
                <span>محرر قالب الشهادة</span>
                <input type="text" wire:model="name" aria-label="اسم القالب">
            </div>
        </div>
        <div class="cert-builder__status">
            <span class="{{ $template->status === 'active' ? 'is-active' : '' }}">{{ $template->status === 'active' ? 'فعّال' : 'مسودة' }}</span>
            <span>v{{ $template->version }}</span>
            <span dir="ltr">{{ $template->canvas_width }} × {{ $template->canvas_height }}</span>
        </div>
        <div class="cert-builder__top-actions">
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="save" wire:loading.attr="disabled"><i class="fa-regular fa-floppy-disk"></i> حفظ</button>
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="activate" wire:loading.attr="disabled"><i class="fa-solid fa-circle-check"></i> حفظ واعتماد</button>
        </div>
    </header>

    @if ($message)
        <div class="admin-alert admin-alert--info is-visible" role="status">{{ $message }}</div>
    @endif
    @error('background')<div class="admin-alert admin-alert--danger is-visible">{{ $message }}</div>@enderror
    @error('elements')<div class="admin-alert admin-alert--danger is-visible">{{ $message }}</div>@enderror

    <div class="cert-builder__workspace">
        <aside class="cert-builder__sidebar cert-builder__sidebar--tools">
            <section>
                <h2><i class="fa-solid fa-plus"></i> إضافة عنصر</h2>
                <div class="cert-tool-grid">
                    <button type="button" @click="addText()"><i class="fa-solid fa-font"></i><span>نص حر</span></button>
                    <button type="button" @click="addQr()"><i class="fa-solid fa-qrcode"></i><span>QR تحقق</span></button>
                    <button type="button" @click="addLine()"><i class="fa-solid fa-minus"></i><span>خط فاصل</span></button>
                </div>
            </section>

            <section>
                <h2><i class="fa-solid fa-database"></i> المتغيرات</h2>
                <p>اضغط على المتغير لإضافته إلى الشهادة.</p>
                <div class="cert-variable-list">
                    @foreach ($variableGroups as $group => $variables)
                        <details @if($loop->first) open @endif>
                            <summary>{{ $group }} <span>{{ count($variables) }}</span></summary>
                            <div>
                                @foreach ($variables as $variable)
                                    <button type="button" @click="addVariable(@js($variable['key']))">
                                        <strong>{{ $variable['label'] }}</strong>
                                        <small dir="ltr">{{ $variable['key'] }}</small>
                                    </button>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>

            <section>
                <h2><i class="fa-regular fa-image"></i> خلفية الشهادة</h2>
                <label class="cert-background-upload">
                    <input type="file" wire:model="background" accept="image/png,image/jpeg,image/webp">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>{{ $backgroundPreview ? 'استبدال صورة الخلفية' : 'رفع صورة الخلفية' }}</span>
                    <small>PNG, JPG, WEBP — حتى 15MB</small>
                </label>
                <div wire:loading wire:target="background" class="admin-field-hint">جاري رفع الصورة…</div>
                <label class="cert-property-field" style="margin-top:.65rem">
                    <span>لون الخلفية عند عدم وجود صورة</span>
                    <input type="color" x-model="designSettings.background_color">
                </label>
            </section>
        </aside>

        <main class="cert-builder__stage">
            <div class="cert-builder__stagebar">
                <div>
                    <button type="button" @click="scale = Math.max(.3, Math.round((scale - .1) * 100) / 100)"><i class="fa-solid fa-minus"></i></button>
                    <span x-text="Math.round(scale * 100) + '%'"></span>
                    <button type="button" @click="scale = Math.min(1.3, Math.round((scale + .1) * 100) / 100)"><i class="fa-solid fa-plus"></i></button>
                    <button type="button" class="cert-stage-fit" @click="fit()"><i class="fa-solid fa-compress"></i> ملاءمة</button>
                </div>
                <label class="cert-stage-safe-area">
                    <input type="checkbox" x-model="designSettings.show_safe_area">
                    منطقة الأمان
                </label>
                <span><i class="fa-solid fa-hand-pointer"></i> اسحب العناصر أو استخدم الأسهم للتحريك الدقيق</span>
            </div>

            <div class="cert-builder__canvas-scroll" x-ref="canvasScroll" @click.self="selectedId = null">
                <div
                    class="cert-builder__canvas-holder"
                    :style="{ width: (canvasWidth * scale) + 'px', height: (canvasHeight * scale) + 'px' }"
                >
                    <div
                        class="cert-builder__canvas"
                        :style="{
                            width: '{{ $template->canvas_width }}px',
                            height: '{{ $template->canvas_height }}px',
                            transform: 'scale(' + scale + ')',
                            backgroundColor: designSettings.background_color || '#ffffff',
                            backgroundImage: @js($backgroundPreview ? "url('".$backgroundPreview."')" : 'none')
                        }"
                        @click.self="selectedId = null"
                    >
                        <div class="cert-canvas-safe-area" x-show="designSettings.show_safe_area"></div>
                        <template x-for="element in [...elements].sort((a,b) => Number(a.z_index)-Number(b.z_index))" :key="element.id">
                            <div
                                class="cert-canvas-element"
                                :class="{ 'is-selected': selectedId === element.id, 'is-qr': element.type === 'qr', 'is-line': element.type === 'line' }"
                                :style="`left:${element.x}px;top:${element.y}px;width:${element.width}px;height:${element.height}px;z-index:${element.z_index};transform:rotate(${element.rotation || 0}deg);`"
                                @pointerdown="startDrag($event, element)"
                                @click.stop="select(element.id)"
                            >
                                <div
                                    x-show="element.type === 'text'"
                                    class="cert-canvas-element__text"
                                    :style="`font-family:${element.font_family};font-size:${element.font_size}px;font-weight:${element.font_weight};color:${element.color};background:${element.background};text-align:${element.align};direction:${element.direction};line-height:${element.line_height};letter-spacing:${element.letter_spacing}px;`"
                                    x-text="sampleText(element.content)"
                                ></div>
                                <div x-show="element.type === 'qr'" class="cert-canvas-element__qr">
                                    <i class="fa-solid fa-qrcode"></i><small>QR تحقق</small>
                                </div>
                                <div x-show="element.type === 'line'" class="cert-canvas-element__line" :style="`background:${element.color}`"></div>
                                <button type="button" class="cert-canvas-element__resize" @pointerdown="startResize($event, element)" aria-label="تغيير الحجم"></button>
                                <span class="cert-canvas-element__label" x-show="selectedId === element.id" x-text="element.type === 'text' ? 'نص' : (element.type === 'qr' ? 'QR' : 'خط')"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <aside class="cert-builder__sidebar cert-builder__sidebar--properties">
            <template x-if="selected">
                <div>
                    <section class="cert-properties-head">
                        <div><span>خصائص العنصر</span><strong x-text="selected.type === 'text' ? 'نص' : (selected.type === 'qr' ? 'رمز QR' : 'خط فاصل')"></strong></div>
                        <button type="button" @click="removeSelected()" title="حذف"><i class="fa-regular fa-trash-can"></i></button>
                    </section>

                    <section x-show="selected.type === 'text'">
                        <label class="cert-property-field">
                            <span>المحتوى</span>
                            <textarea rows="4" x-model="selected.content"></textarea>
                        </label>
                        <label class="cert-property-field">
                            <span>نوع الخط</span>
                            <select x-model="selected.font_family">
                                <option value="DejaVu Sans">DejaVu Sans — عربي</option>
                                <option value="Arial">Arial</option>
                                <option value="Tahoma">Tahoma</option>
                                <option value="Georgia">Georgia</option>
                                <option value="serif">Serif</option>
                            </select>
                        </label>
                        <div class="cert-property-grid">
                            <label class="cert-property-field"><span>حجم الخط</span><input type="number" min="6" max="160" x-model.number="selected.font_size"></label>
                            <label class="cert-property-field"><span>السُمك</span><select x-model.number="selected.font_weight"><option value="400">عادي</option><option value="500">متوسط</option><option value="600">نصف عريض</option><option value="700">عريض</option><option value="800">عريض جداً</option><option value="900">أسود</option></select></label>
                            <label class="cert-property-field"><span>لون النص</span><input type="color" x-model="selected.color"></label>
                            <label class="cert-property-field"><span>المحاذاة</span><select x-model="selected.align"><option value="right">يمين</option><option value="center">وسط</option><option value="left">يسار</option></select></label>
                            <label class="cert-property-field"><span>اتجاه النص</span><select x-model="selected.direction"><option value="rtl">RTL</option><option value="ltr">LTR</option></select></label>
                            <label class="cert-property-field"><span>تباعد السطور</span><input type="number" min=".7" max="3" step=".05" x-model.number="selected.line_height"></label>
                        </div>
                    </section>

                    <section x-show="selected.type === 'qr'">
                        <label class="cert-property-field"><span>بيانات QR</span><select x-model="selected.variable"><option value="certificate.verify_url">رابط التحقق الآمن</option><option value="certificate.code">رقم الشهادة</option></select></label>
                        <div class="cert-property-grid">
                            <label class="cert-property-field"><span>لون QR</span><input type="color" x-model="selected.foreground"></label>
                            <label class="cert-property-field"><span>الخلفية</span><input type="color" x-model="selected.background"></label>
                        </div>
                    </section>

                    <section x-show="selected.type === 'line'">
                        <label class="cert-property-field"><span>لون الخط</span><input type="color" x-model="selected.color"></label>
                    </section>

                    <section>
                        <h2>الموضع والحجم</h2>
                        <div class="cert-property-grid">
                            <label class="cert-property-field"><span>X</span><input type="number" min="0" x-model.number="selected.x"></label>
                            <label class="cert-property-field"><span>Y</span><input type="number" min="0" x-model.number="selected.y"></label>
                            <label class="cert-property-field"><span>العرض</span><input type="number" min="10" x-model.number="selected.width"></label>
                            <label class="cert-property-field"><span>الارتفاع</span><input type="number" min="10" x-model.number="selected.height"></label>
                            <label class="cert-property-field"><span>الدوران</span><input type="number" min="-360" max="360" x-model.number="selected.rotation"></label>
                            <label class="cert-property-field"><span>الطبقة</span><input type="number" min="1" max="999" x-model.number="selected.z_index"></label>
                        </div>
                        <div class="cert-layer-actions">
                            <button type="button" @click="moveLayer(1)"><i class="fa-solid fa-arrow-up"></i> للأمام</button>
                            <button type="button" @click="moveLayer(-1)"><i class="fa-solid fa-arrow-down"></i> للخلف</button>
                            <button type="button" @click="duplicateSelected()"><i class="fa-regular fa-copy"></i> نسخ</button>
                        </div>
                    </section>
                </div>
            </template>

            <div x-show="!selected" class="cert-properties-empty">
                <i class="fa-solid fa-arrow-pointer"></i>
                <h3>اختر عنصراً من الشهادة</h3>
                <p>ستظهر هنا خصائص النص والموضع والحجم والألوان.</p>
            </div>

            <section class="cert-layers">
                <h2><i class="fa-solid fa-layer-group"></i> طبقات التصميم <span x-text="elements.length"></span></h2>
                <div>
                    <template x-for="element in [...elements].sort((a,b) => Number(b.z_index)-Number(a.z_index))" :key="'layer-'+element.id">
                        <button type="button" :class="{ 'is-active':selectedId === element.id }" @click="select(element.id)">
                            <i :class="element.type === 'text' ? 'fa-solid fa-font' : (element.type === 'qr' ? 'fa-solid fa-qrcode' : 'fa-solid fa-minus')"></i>
                            <span x-text="element.type === 'text' ? sampleText(element.content).slice(0,24) : (element.type === 'qr' ? 'QR التحقق' : 'خط فاصل')"></span>
                            <small x-text="element.z_index"></small>
                        </button>
                    </template>
                </div>
            </section>
        </aside>
    </div>
</div>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .cert-builder{display:flex;flex-direction:column;gap:.75rem}.cert-builder__topbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1rem;border:1px solid #dce8e1;border-radius:14px;background:#fff;box-shadow:0 7px 20px rgba(15,23,42,.06)}.cert-builder__title{display:flex;align-items:center;gap:.7rem}.cert-builder__title>a{display:grid;place-items:center;width:2.2rem;height:2.2rem;border-radius:10px;background:#f1f5f9;color:#334155}.cert-builder__title span{display:block;color:#94a3b8;font-size:.58rem;font-weight:800}.cert-builder__title input{width:min(22rem,35vw);padding:.15rem;border:0;border-bottom:1px dashed transparent;background:transparent;color:#17251f;font:900 .9rem/1.4 inherit}.cert-builder__title input:focus{outline:0;border-bottom-color:#16a34a}.cert-builder__status{display:flex;gap:.4rem}.cert-builder__status span{padding:.28rem .55rem;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.61rem;font-weight:800}.cert-builder__status .is-active{background:#dcfce7;color:#166534}.cert-builder__top-actions{display:flex;gap:.45rem}
    .cert-builder__workspace{display:grid;grid-template-columns:245px minmax(420px,1fr) 260px;min-height:690px;border:1px solid #dbe5df;border-radius:16px;background:#e8eeeb;overflow:hidden}.cert-builder__sidebar{overflow:auto;max-height:calc(100vh - 170px);background:#fff}.cert-builder__sidebar section{padding:.9rem;border-bottom:1px solid #eef2f0}.cert-builder__sidebar h2{display:flex;align-items:center;gap:.4rem;margin:0 0 .65rem;color:#334155;font-size:.72rem;font-weight:900}.cert-builder__sidebar h2 i{color:#16a34a}.cert-builder__sidebar p{margin:-.35rem 0 .65rem;color:#94a3b8;font-size:.6rem;line-height:1.6}
    .cert-tool-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.4rem}.cert-tool-grid button{display:grid;place-items:center;gap:.35rem;padding:.6rem .25rem;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#475569;font-size:.59rem;font-weight:800;cursor:pointer}.cert-tool-grid button:hover{border-color:#86efac;background:#f0fdf4;color:#166534}.cert-tool-grid i{font-size:.9rem}
    .cert-variable-list{display:grid;gap:.35rem}.cert-variable-list details{border:1px solid #e2e8f0;border-radius:9px;overflow:hidden}.cert-variable-list summary{display:flex;justify-content:space-between;padding:.48rem .55rem;background:#f8fafc;color:#475569;font-size:.63rem;font-weight:900;cursor:pointer}.cert-variable-list summary span{color:#94a3b8}.cert-variable-list details>div{display:grid;padding:.3rem}.cert-variable-list button{display:grid;gap:.1rem;padding:.42rem;border:0;border-radius:6px;background:transparent;text-align:start;cursor:pointer}.cert-variable-list button:hover{background:#f0fdf4}.cert-variable-list strong{font-size:.61rem;color:#334155}.cert-variable-list small{font-size:.5rem;color:#94a3b8}
    .cert-background-upload{display:grid;place-items:center;gap:.25rem;padding:.85rem;border:1.5px dashed #a7c4b3;border-radius:10px;background:#f7fbf8;color:#47705a;text-align:center;cursor:pointer}.cert-background-upload input{display:none}.cert-background-upload i{font-size:1.15rem;color:#16a34a}.cert-background-upload span{font-size:.64rem;font-weight:900}.cert-background-upload small{font-size:.52rem;color:#94a3b8}
    .cert-builder__stage{min-width:0;background:#dfe7e2}.cert-builder__stagebar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.55rem .75rem;border-bottom:1px solid #cdd9d1;background:#f8faf9;color:#64748b;font-size:.59rem}.cert-builder__stagebar>div{display:flex;align-items:center;gap:.3rem}.cert-builder__stagebar button{display:grid;place-items:center;width:1.7rem;height:1.7rem;border:1px solid #d7e0db;border-radius:6px;background:#fff;color:#475569;cursor:pointer}.cert-builder__stagebar span{min-width:2.7rem;text-align:center}.cert-stage-safe-area{display:flex;align-items:center;gap:.3rem;color:#64748b;font-size:.56rem;cursor:pointer}.cert-stage-safe-area input{accent-color:#16a34a}.cert-stage-fit{display:flex!important;align-items:center;gap:.25rem;width:auto!important;height:1.7rem;padding:0 .55rem;font-size:.56rem;font-weight:800;white-space:nowrap}.cert-builder__canvas-scroll{direction:ltr;min-height:630px;max-height:calc(100vh - 230px);padding:1.5rem;overflow:auto;text-align:center}.cert-builder__canvas-holder{position:relative;display:inline-block;margin:0 auto;text-align:start;vertical-align:top}.cert-builder__canvas{position:absolute;top:0;left:0;transform-origin:top left;background-position:center;background-repeat:no-repeat;background-size:100% 100%;box-shadow:0 18px 50px rgba(15,23,42,.28);overflow:hidden}.cert-canvas-safe-area{position:absolute;z-index:1000;inset:38px;border:1px dashed rgba(37,99,235,.55);pointer-events:none}.cert-canvas-safe-area::before{content:"منطقة الأمان";position:absolute;inset:.3rem .4rem auto auto;padding:.12rem .3rem;border-radius:4px;background:rgba(37,99,235,.82);color:#fff;font-size:9px}
    .cert-canvas-element{position:absolute;box-sizing:border-box;cursor:move;user-select:none}.cert-canvas-element.is-selected{outline:2px solid #2563eb;outline-offset:2px}.cert-canvas-element__text{display:flex;align-items:center;justify-content:center;width:100%;height:100%;overflow:hidden;white-space:pre-wrap}.cert-canvas-element__qr{display:grid;place-items:center;width:100%;height:100%;border:2px solid #111827;background:repeating-conic-gradient(#111827 0 25%,#fff 0 50%) 0/18px 18px;color:#fff;text-shadow:0 1px 3px #000}.cert-canvas-element__qr i{font-size:2rem}.cert-canvas-element__qr small{padding:.15rem .3rem;background:#111827;font-size:.55rem}.cert-canvas-element__line{position:absolute;inset:50% 0 auto;height:100%;min-height:1px}.cert-canvas-element__resize{display:none;position:absolute;inset:auto -6px -6px auto;width:13px;height:13px;padding:0;border:2px solid #fff;border-radius:3px;background:#2563eb;cursor:nwse-resize}.cert-canvas-element.is-selected .cert-canvas-element__resize{display:block}.cert-canvas-element__label{position:absolute;inset:auto auto calc(100% + 6px) 0;padding:.15rem .35rem;border-radius:4px;background:#2563eb;color:#fff;font-size:.52rem}
    .cert-properties-head{display:flex;align-items:center;justify-content:space-between}.cert-properties-head span,.cert-properties-head strong{display:block}.cert-properties-head span{color:#94a3b8;font-size:.55rem}.cert-properties-head strong{margin-top:.15rem;color:#334155;font-size:.73rem}.cert-properties-head button{border:0;background:transparent;color:#dc2626;cursor:pointer}.cert-property-grid{display:grid;grid-template-columns:1fr 1fr;gap:.45rem}.cert-property-field{display:grid;gap:.25rem;margin-bottom:.5rem}.cert-property-field>span{color:#64748b;font-size:.57rem;font-weight:800}.cert-property-field input,.cert-property-field select,.cert-property-field textarea{min-width:0;width:100%;padding:.45rem .5rem;border:1px solid #d8e1dc;border-radius:7px;background:#fff;color:#334155;font:600 .63rem/1.5 inherit;box-sizing:border-box}.cert-property-field input[type=color]{height:2rem;padding:.15rem}.cert-layer-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:.3rem}.cert-layer-actions button{padding:.4rem .2rem;border:1px solid #dce5df;border-radius:7px;background:#f8fafc;color:#475569;font-size:.53rem;cursor:pointer}.cert-properties-empty{display:grid;place-items:center;padding:2.3rem 1rem;text-align:center;color:#94a3b8}.cert-properties-empty i{font-size:1.5rem}.cert-properties-empty h3{margin:.55rem 0 .2rem;color:#64748b;font-size:.72rem}.cert-properties-empty p{margin:0;max-width:12rem;font-size:.58rem;line-height:1.7}
    .cert-layers>h2 span{margin-inline-start:auto;padding:.15rem .35rem;border-radius:999px;background:#e2e8f0;color:#475569;font-size:.52rem}.cert-layers>div{display:grid;gap:.25rem}.cert-layers button{display:flex;align-items:center;gap:.4rem;padding:.42rem .5rem;border:1px solid transparent;border-radius:7px;background:#f8fafc;color:#64748b;font-size:.58rem;text-align:start;cursor:pointer}.cert-layers button.is-active{border-color:#93c5fd;background:#eff6ff;color:#1d4ed8}.cert-layers button span{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cert-layers button small{font-size:.5rem;color:#94a3b8}
    @media(max-width:1200px){.cert-builder__workspace{grid-template-columns:210px minmax(400px,1fr) 230px}}@media(max-width:900px){.cert-builder__workspace{display:flex;flex-direction:column}.cert-builder__sidebar{max-height:none}.cert-builder__sidebar--tools{order:1}.cert-builder__stage{order:2}.cert-builder__sidebar--properties{order:3}.cert-builder__topbar{align-items:flex-start;flex-wrap:wrap}.cert-builder__status{display:none}}
</style>
@endpush
