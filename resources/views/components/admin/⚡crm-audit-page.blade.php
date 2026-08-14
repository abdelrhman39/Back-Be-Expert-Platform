<?php

use App\Models\ActivityLog;
use App\Services\AuditLogService;
use App\Support\CrmAccess;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('سجل أحداث CRM')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $expandedId = null;

    public function mount(): void
    {
        abort_unless(CrmAccess::canViewAudit(auth()->user()), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    #[Computed]
    public function stats(): array
    {
        $base = ActivityLog::query()->where('log_group', 'crm');

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'assignments' => (clone $base)->where('action', 'like', 'crm.contact.%assign%')->count(),
            'imports' => (clone $base)->where('action', 'like', 'crm.import.%')->count(),
        ];
    }

    #[Computed]
    public function logs()
    {
        return app(AuditLogService::class)->paginate('crm', $this->search);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.crm'),
    'shellBreadcrumb' => [
        ['href' => route('admin.crm'), 'label' => 'CRM'],
        ['label' => 'سجل الأحداث'],
    ],
])

<div class="crm-audit-page">
    <header class="crm-audit-hero">
        <div>
            <span>AUDIT TRAIL</span>
            <h1>سجل أحداث CRM</h1>
            <p>كل إجراء على العملاء والتوزيع والاستيراد والإعدادات مع اسم القائم به وتفاصيل التغيير.</p>
        </div>
        <a href="{{ route('admin.crm') }}" class="crm-audit-back">العودة إلى CRM</a>
    </header>

    <section class="crm-audit-kpis">
        <article><span>إجمالي الأحداث</span><strong>{{ number_format($this->stats['total']) }}</strong></article>
        <article><span>اليوم</span><strong>{{ number_format($this->stats['today']) }}</strong></article>
        <article><span>عمليات التوزيع</span><strong>{{ number_format($this->stats['assignments']) }}</strong></article>
        <article><span>عمليات الاستيراد</span><strong>{{ number_format($this->stats['imports']) }}</strong></article>
    </section>

    <section class="crm-audit-card">
        <div class="crm-audit-toolbar">
            <label><span>بحث في الأحداث</span><input wire:model.live.debounce.350ms="search" type="search" placeholder="اسم العميل، الإجراء، أو الوصف"></label>
        </div>
        <div class="crm-audit-table-wrap">
            <table class="crm-audit-table">
                <thead>
                    <tr>
                        <th>الوقت</th>
                        <th>القائم بالحدث</th>
                        <th>الوصف</th>
                        <th>الإجراء</th>
                        <th>IP</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->logs as $log)
                        <tr wire:key="crm-audit-{{ $log->id }}">
                            <td>{{ $log->created_at->format('Y/m/d H:i') }}</td>
                            <td>{{ $log->user?->displayName() ?: 'النظام' }}</td>
                            <td>
                                <strong>{{ $log->description_ar }}</strong>
                                @if ($log->subject_label)<small>{{ $log->subject_label }}</small>@endif
                            </td>
                            <td><code>{{ $log->action }}</code></td>
                            <td dir="ltr">{{ $log->ip_address ?: '—' }}</td>
                            <td>
                                @if ($log->old_values || $log->new_values)
                                    <button type="button" wire:click="toggleExpand({{ $log->id }})">{{ $expandedId === $log->id ? 'إخفاء' : 'التفاصيل' }}</button>
                                @endif
                            </td>
                        </tr>
                        @if ($expandedId === $log->id)
                            <tr class="crm-audit-details">
                                <td colspan="6">
                                    <div class="crm-audit-diff">
                                        <div>
                                            <h3>قبل</h3>
                                            <pre>{{ json_encode($log->old_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?: '—' }}</pre>
                                        </div>
                                        <div>
                                            <h3>بعد</h3>
                                            <pre>{{ json_encode($log->new_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) ?: '—' }}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="crm-audit-empty">لا توجد أحداث مسجلة بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="crm-audit-pagination">{{ $this->logs->links() }}</div>
    </section>
</div>

<style>
.crm-audit-page{display:grid;gap:18px;direction:rtl}
.crm-audit-hero{display:flex;justify-content:space-between;align-items:center;gap:18px;padding:24px;border-radius:20px;background:linear-gradient(125deg,#102b2d,#1b5852);color:#fff}
.crm-audit-hero span{font-size:10px;letter-spacing:2px;color:#8ad7ca}
.crm-audit-hero h1{margin:7px 0;font-size:27px}
.crm-audit-hero p{margin:0;color:#d4e7e3;max-width:640px}
.crm-audit-back{padding:10px 14px;border-radius:10px;background:#fff;color:#1d4d49;text-decoration:none;font-weight:800}
.crm-audit-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.crm-audit-kpis article{background:#fff;border:1px solid #e1eae8;border-radius:14px;padding:16px;display:grid;gap:4px}
.crm-audit-kpis span{color:#71817f;font-size:12px}
.crm-audit-kpis strong{font-size:26px;color:#183b38}
.crm-audit-card{background:#fff;border:1px solid #e1eae8;border-radius:16px;padding:18px;box-shadow:0 8px 24px #183d3b0a}
.crm-audit-toolbar label{display:grid;gap:6px;max-width:420px}
.crm-audit-toolbar span{font-size:11px;font-weight:800;color:#617573}
.crm-audit-toolbar input{border:1px solid #dbe6e4;border-radius:10px;padding:10px}
.crm-audit-table-wrap{overflow:auto;margin-top:14px}
.crm-audit-table{width:100%;min-width:980px;border-collapse:collapse}
.crm-audit-table th,.crm-audit-table td{text-align:right;padding:12px 10px;border-bottom:1px solid #e8efee;vertical-align:top}
.crm-audit-table th{font-size:11px;color:#6c7f7c}
.crm-audit-table td strong{display:block;color:#1d4642}
.crm-audit-table td small{display:block;color:#7d8d8b;margin-top:3px}
.crm-audit-table code{font-size:11px;background:#eef5f3;padding:4px 7px;border-radius:6px}
.crm-audit-table button{border:1px solid #d9e3e1;background:#fff;border-radius:8px;padding:7px 10px;cursor:pointer;font-weight:700}
.crm-audit-diff{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.crm-audit-diff h3{margin:0 0 8px;font-size:13px;color:#255550}
.crm-audit-diff pre{margin:0;background:#f5faf9;border:1px solid #e1eae8;border-radius:10px;padding:12px;white-space:pre-wrap;font-size:12px;color:#314846}
.crm-audit-empty{text-align:center!important;padding:40px!important;color:#758684}
.crm-audit-pagination{margin-top:14px}
@media(max-width:900px){.crm-audit-kpis{grid-template-columns:repeat(2,1fr)}.crm-audit-diff{grid-template-columns:1fr}}
@media(max-width:600px){.crm-audit-hero{flex-direction:column;align-items:flex-start}.crm-audit-kpis{grid-template-columns:1fr}}
</style>

@include('partials.admin.shell-end')
