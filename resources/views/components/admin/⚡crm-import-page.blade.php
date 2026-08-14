<?php

use App\Models\AcademicProgram;
use App\Models\CrmImport;
use App\Services\CrmAssignmentService;
use App\Services\CrmAuditService;
use App\Services\CrmImportService;
use App\Support\CrmAccess;
use App\Support\CrmOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('استيراد العملاء | CRM')]
class extends Component
{
    use WithFileUploads;

    public $file;
    public string $program = '';
    public string $owner = '';
    public string $source = '';
    public bool $autoAssign = true;
    public ?int $lastImportId = null;

    public function mount(): void
    {
        abort_unless(CrmAccess::canImport(auth()->user()), 403);
        $this->source = CrmOptions::resolveSourceKey('import');
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
    public function imports()
    {
        return CrmImport::query()->with('importer:id,name,name_ar')->latest()->limit(20)->get();
    }

    public function runImport(): void
    {
        $validated = $this->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
            'program' => ['nullable', 'integer', 'exists:academic_programs,id'],
            'owner' => ['nullable', 'integer', 'exists:users,id'],
            'source' => ['required', 'in:'.implode(',', array_keys(CrmOptions::sources()))],
            'autoAssign' => ['boolean'],
        ]);
        if ($validated['owner']) {
            abort_unless($this->salesUsers->contains('id', (int) $validated['owner']), 422);
        }

        $import = app(CrmImportService::class)->import($this->file, auth()->user(), [
            'program_id' => $validated['program'] ? (int) $validated['program'] : null,
            'owner_id' => $validated['owner'] ? (int) $validated['owner'] : null,
            'source' => $validated['source'],
            'auto_assign' => $validated['autoAssign'] && ! $validated['owner'],
        ]);

        app(CrmAuditService::class)->imported($import, auth()->user());
        $this->lastImportId = $import->id;
        $this->reset('file');
        unset($this->imports);
        session()->flash(
            $import->status === 'completed' ? 'crm_success' : 'crm_error',
            $import->status === 'completed'
                ? "اكتمل الاستيراد: {$import->created_rows} جديد، {$import->updated_rows} محدث، {$import->failed_rows} خطأ."
                : 'فشل الاستيراد: '.($import->errors[0] ?? 'خطأ غير معروف')
        );
    }

    public function downloadTemplate(): mixed
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'email', 'phone', 'program', 'company', 'job_title', 'country', 'region', 'city', 'source', 'priority', 'notes']);
            fputcsv($out, ['أحمد محمد', 'ahmed@example.com', '+966500000000', 'DIP-01', '', '', 'السعودية', 'الرياض', 'الرياض', 'campaign', 'high', 'مهتم بالدفعة القادمة']);
            fclose($out);
        }, 'crm-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => route('admin.crm'), 'label' => 'CRM'],
        ['label' => 'استيراد العملاء'],
    ],
])

<div class="crm-import-page">
    <header class="crm-import-hero">
        <div><span>DATA INTAKE</span><h1>استيراد وتوزيع بيانات العملاء</h1><p>ارفع ملف CSV، امنع التكرار تلقائياً، ثم وزّع العملاء على فريق السيلز حسب البرنامج.</p></div>
        <div><button wire:click="downloadTemplate" class="crm-import-btn crm-import-btn--light">تنزيل نموذج CSV</button><a href="{{ route('admin.crm') }}" class="crm-import-btn crm-import-btn--light">العودة إلى CRM</a></div>
    </header>

    @if (session('crm_success'))<div class="crm-import-alert crm-import-alert--success">{{ session('crm_success') }}</div>@endif
    @if (session('crm_error'))<div class="crm-import-alert crm-import-alert--error">{{ session('crm_error') }}</div>@endif

    <div class="crm-import-grid">
        <form wire:submit="runImport" class="crm-import-card">
            <div class="crm-import-card__head"><span>1</span><div><h2>الملف وإعدادات التوزيع</h2><p>الحد الأقصى 20,000 سجل و10MB لكل عملية.</p></div></div>
            <label class="crm-drop">
                <input wire:model="file" type="file" accept=".csv,.txt">
                <strong>{{ $file?->getClientOriginalName() ?: 'اختر ملف CSV أو اسحبه هنا' }}</strong>
                <small>يدعم عناوين الأعمدة العربية والإنجليزية</small>
            </label>
            <div wire:loading wire:target="file" class="crm-loading">جارٍ تجهيز الملف...</div>
            @error('file')<div class="crm-import-error">{{ $message }}</div>@enderror

            <div class="crm-import-fields">
                <label><span>برنامج افتراضي</span><select wire:model="program"><option value="">يُقرأ من كل صف</option>@foreach ($this->programs as $item)<option value="{{ $item->id }}">{{ $item->name_ar }} ({{ $item->code }})</option>@endforeach</select></label>
                <label><span>توزيع مباشر لموظف</span><select wire:model.live="owner"><option value="">بدون موظف محدد</option>@foreach ($this->salesUsers as $sales)<option value="{{ $sales->id }}">{{ $sales->displayName() }}</option>@endforeach</select></label>
                <label><span>مصدر البيانات</span><select wire:model="source">@foreach (\App\Support\CrmOptions::sources() as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            </div>
            <label class="crm-import-check"><input wire:model="autoAssign" type="checkbox" @disabled($owner)><span><strong>التوزيع التلقائي</strong><small>يستخدم قواعد البرنامج والتوزيع العادل بين موظفي السيلز.</small></span></label>

            @if ($errors->any())<div class="crm-import-error">{{ $errors->first() }}</div>@endif
            <button class="crm-import-submit" wire:loading.attr="disabled"><span wire:loading.remove wire:target="runImport">بدء الاستيراد والتوزيع</span><span wire:loading wire:target="runImport">جارٍ استيراد البيانات...</span></button>
        </form>

        <aside class="crm-import-card">
            <div class="crm-import-card__head"><span>2</span><div><h2>الأعمدة المدعومة</h2><p>الاسم مع الهاتف أو البريد مطلوب فقط.</p></div></div>
            <div class="crm-columns">
                <div><strong>name / الاسم *</strong><span>اسم العميل</span></div>
                <div><strong>phone / الهاتف</strong><span>رقم التواصل</span></div>
                <div><strong>email / البريد</strong><span>البريد الإلكتروني</span></div>
                <div><strong>program / البرنامج</strong><span>كود البرنامج أو اسمه</span></div>
                <div><strong>company / الشركة</strong><span>اسم جهة العمل</span></div>
                <div><strong>city / المدينة</strong><span>مدينة العميل</span></div>
                <div><strong>source / المصدر</strong><span>مصدر الحملة</span></div>
                <div><strong>priority / الأولوية</strong><span>low, medium, high, urgent</span></div>
                <div><strong>notes / ملاحظات</strong><span>أي تفاصيل إضافية</span></div>
            </div>
            <div class="crm-import-note"><strong>منع التكرار</strong><p>إذا تطابق البريد الإلكتروني أو الهاتف، يتم تحديث سجل العميل الموجود بدلاً من إنشاء نسخة أخرى.</p></div>
        </aside>
    </div>

    <section class="crm-import-card">
        <div class="crm-import-card__head"><span>3</span><div><h2>سجل عمليات الاستيراد</h2><p>آخر 20 عملية مع نتائجها والأخطاء إن وجدت.</p></div></div>
        <div class="crm-import-table-wrap"><table class="crm-import-table"><thead><tr><th>الملف</th><th>بواسطة</th><th>الحالة</th><th>الإجمالي</th><th>جديد</th><th>محدّث</th><th>أخطاء</th><th>التاريخ</th></tr></thead><tbody>
            @forelse ($this->imports as $import)
                <tr @class(['is-latest' => $lastImportId === $import->id])><td><strong>{{ $import->original_filename }}</strong>@if($import->errors)<small title="{{ implode(' | ', $import->errors) }}">{{ $import->errors[0] }}</small>@endif</td><td>{{ $import->importer?->displayName() ?: '—' }}</td><td><span class="crm-import-status crm-import-status--{{ $import->status }}">{{ $import->status === 'completed' ? 'مكتمل' : ($import->status === 'failed' ? 'فشل' : 'قيد التنفيذ') }}</span></td><td>{{ $import->total_rows }}</td><td>{{ $import->created_rows }}</td><td>{{ $import->updated_rows }}</td><td>{{ $import->failed_rows }}</td><td>{{ $import->created_at->format('Y/m/d H:i') }}</td></tr>
            @empty
                <tr><td colspan="8" class="crm-import-empty">لا توجد عمليات استيراد سابقة.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>

<style>
.crm-import-page{display:grid;gap:18px;direction:rtl}.crm-import-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;background:linear-gradient(120deg,#102c2d,#1b5852);color:#fff;border-radius:20px;padding:26px}.crm-import-hero span{font-size:10px;letter-spacing:2px;color:#8bd6ca}.crm-import-hero h1{font-size:27px;margin:7px 0}.crm-import-hero p{margin:0;color:#d6e8e5}.crm-import-hero>div:last-child{display:flex;gap:8px}.crm-import-btn{display:inline-block;padding:10px 13px;border-radius:9px;text-decoration:none;border:0;cursor:pointer;font-weight:800}.crm-import-btn--light{background:#ffffff15;border:1px solid #ffffff30;color:#fff}.crm-import-alert{padding:13px 16px;border-radius:11px;font-weight:700}.crm-import-alert--success{background:#e3f7ec;color:#17633f}.crm-import-alert--error{background:#feeceb;color:#a43e37}.crm-import-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(300px,.7fr);gap:18px}.crm-import-card{background:#fff;border:1px solid #e0eae8;border-radius:17px;padding:20px;box-shadow:0 8px 24px #183d3b0a}.crm-import-card__head{display:flex;align-items:center;gap:11px;margin-bottom:17px}.crm-import-card__head>span{width:31px;height:31px;border-radius:9px;background:#d8a633;color:#183a37;display:grid;place-items:center;font-weight:900}.crm-import-card__head h2{font-size:18px;margin:0 0 3px;color:#193e3b}.crm-import-card__head p{margin:0;color:#798a88;font-size:12px}.crm-drop{min-height:150px;border:2px dashed #bdd4d0;background:#f6fbfa;border-radius:14px;display:grid;place-items:center;align-content:center;gap:5px;text-align:center;cursor:pointer}.crm-drop input{position:absolute;opacity:0}.crm-drop strong{color:#22534e}.crm-drop small{color:#82918f}.crm-loading{color:#237065;margin-top:7px}.crm-import-fields{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:15px}.crm-import-fields label{display:grid;gap:5px}.crm-import-fields label>span{font-size:11px;color:#617573;font-weight:800}.crm-import-fields input,.crm-import-fields select{border:1px solid #dbe6e4;border-radius:10px;padding:10px;width:100%}.crm-import-check{display:flex;gap:10px;align-items:flex-start;border:1px solid #deebe8;border-radius:11px;padding:12px;margin-top:13px}.crm-import-check span{display:grid}.crm-import-check small{color:#748684}.crm-import-error{color:#b42318;margin-top:8px}.crm-import-submit{width:100%;border:0;border-radius:11px;padding:13px;background:#d8a633;color:#193b38;font-weight:900;margin-top:14px;cursor:pointer}.crm-columns{display:grid;gap:8px}.crm-columns>div{display:grid;border-bottom:1px solid #edf2f1;padding-bottom:7px}.crm-columns strong{font:700 12px/1.5 monospace;color:#285c57}.crm-columns span{font-size:11px;color:#7a8b89}.crm-import-note{margin-top:15px;border-radius:11px;padding:12px;background:#eef7f5}.crm-import-note p{font-size:12px;color:#607572;margin:4px 0 0}.crm-import-table-wrap{overflow:auto}.crm-import-table{width:100%;min-width:850px;border-collapse:collapse}.crm-import-table th,.crm-import-table td{text-align:right;padding:11px;border-bottom:1px solid #e9efee;font-size:12px}.crm-import-table th{color:#6c7f7c}.crm-import-table td small{display:block;color:#b15a4e;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.crm-import-table tr.is-latest{background:#f2faf8}.crm-import-status{border-radius:99px;padding:5px 8px;background:#edf3f2;font-weight:800}.crm-import-status--completed{background:#e0f6e9;color:#176b43}.crm-import-status--failed{background:#fee9e7;color:#a83b35}.crm-import-empty{text-align:center!important;padding:35px!important;color:#758684}
@media(max-width:950px){.crm-import-grid{grid-template-columns:1fr}}@media(max-width:700px){.crm-import-hero{align-items:flex-start;flex-direction:column}.crm-import-hero>div:last-child{flex-wrap:wrap}.crm-import-fields{grid-template-columns:1fr}}
</style>

@include('partials.admin.shell-end')
