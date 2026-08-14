<?php

use App\Models\ActivityLog;
use App\Services\AuditLogService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'سجل التدقيق',
    'adminPageDesc' => 'تتبع التغييرات قبل وبعد كل حدث',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'سجل التدقيق'],
    ],
])]
#[Title('سجل التدقيق | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $group = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $expandedId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('audit-log.view'), 403);
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function updatedGroup(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total' => ActivityLog::query()->count(),
            'today' => ActivityLog::query()->whereDate('created_at', today())->count(),
            'settings' => ActivityLog::query()->where('log_group', 'settings')->count(),
            'refunds' => ActivityLog::query()->where('log_group', 'refunds')->count(),
        ];
    }

    public function getLogsProperty()
    {
        return app(AuditLogService::class)->paginate($this->group, $this->search);
    }

    public function getGroupsProperty(): array
    {
        return app(AuditLogService::class)->groups();
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.audit-log'),
])

<div class="admin-module audit-log-hub">
    <section class="admin-module-hero">
        <div class="admin-module-hero__main">
            <h2>سجل التدقيق</h2>
            <p>سجل كامل لكل تغيير في المنصة — مع مقارنة واضحة بين القيم قبل وبعد الحدث، وتمييز الحقول المعدّلة بلون خاص.</p>
        </div>
        <div class="admin-module-hero__aside">
            <div class="admin-module-chip admin-module-chip--ok">
                <i class="fa-solid fa-shield-halved"></i>
                <div>
                    <strong>{{ number_format($this->stats['total']) }}</strong>
                    <span>حدث مسجّل</span>
                </div>
            </div>
        </div>
    </section>

    <div class="admin-module-kpis">
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--blue"><i class="fa-solid fa-calendar-day"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['today'] }}</span>
                <span class="admin-module-kpi__label">اليوم</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--amber"><i class="fa-solid fa-gear"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ $this->stats['settings'] }}</span>
                <span class="admin-module-kpi__label">إعدادات</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--green"><i class="fa-solid fa-bell"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ ActivityLog::query()->where('log_group', 'notifications')->count() }}</span>
                <span class="admin-module-kpi__label">إشعارات</span>
            </div>
        </div>
        <div class="admin-module-kpi">
            <span class="admin-module-kpi__icon admin-module-kpi__icon--purple"><i class="fa-solid fa-rotate-left"></i></span>
            <div class="admin-module-kpi__body">
                <span class="admin-module-kpi__value">{{ ActivityLog::query()->where('log_group', 'requests')->count() }}</span>
                <span class="admin-module-kpi__label">طلبات طلاب</span>
            </div>
        </div>
    </div>

    <div class="audit-log-toolbar">
        <nav class="admin-module-pipeline">
            @foreach ($this->groups as $key => $label)
                <button type="button" @class(['admin-module-pipeline__btn', 'is-active' => $group === $key]) wire:click="$set('group', '{{ $key }}')">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
        <div class="audit-log-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="بحث في الوصف أو الإجراء أو المرجع...">
        </div>
    </div>

    <div class="audit-log-timeline">
        @forelse ($this->logs as $log)
            @php
                $hasDiff = $log->old_values || $log->new_values;
                $isExpanded = $expandedId === $log->id;
                        $icon = match($log->log_group) {
                            'settings' => 'fa-gear',
                            'notifications' => 'fa-bell',
                            'refunds' => 'fa-rotate-left',
                            'requests' => 'fa-clipboard-list',
                            default => 'fa-clock-rotate-left',
                        };
            @endphp
            <article @class(['audit-log-card', 'is-expanded' => $isExpanded]) wire:key="log-{{ $log->id }}">
                <div class="audit-log-card__rail audit-log-card__rail--{{ $log->log_group }}"></div>
                <div class="audit-log-card__icon audit-log-card__icon--{{ $log->log_group }}">
                    <i class="fa-solid {{ $icon }}"></i>
                </div>
                <div class="audit-log-card__body">
                    <header class="audit-log-card__head">
                        <div class="audit-log-card__title">
                            <strong>{{ $log->description_ar }}</strong>
                            <div class="audit-log-card__tags">
                                <span class="audit-tag audit-tag--action" dir="ltr">{{ $log->action }}</span>
                                @if ($log->subject_label)
                                    <span class="audit-tag audit-tag--subject" dir="ltr">{{ $log->subject_label }}</span>
                                @endif
                                <span class="audit-tag audit-tag--group">{{ $this->groups[$log->log_group] ?? $log->log_group }}</span>
                            </div>
                        </div>
                        <div class="audit-log-card__meta">
                            <time datetime="{{ $log->created_at->toIso8601String() }}">
                                <i class="fa-regular fa-clock"></i>
                                {{ $log->created_at->translatedFormat('d M Y · H:i') }}
                            </time>
                            <span class="audit-log-card__ago">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </header>

                    <div class="audit-log-card__actors">
                        @if ($log->user)
                            <span class="audit-actor">
                                <i class="fa-solid fa-user"></i>
                                {{ $log->user->displayName() }}
                            </span>
                        @else
                            <span class="audit-actor audit-actor--system">
                                <i class="fa-solid fa-robot"></i> النظام
                            </span>
                        @endif
                        @if ($log->ip_address)
                            <span class="audit-actor" dir="ltr">
                                <i class="fa-solid fa-globe"></i> {{ $log->ip_address }}
                            </span>
                        @endif
                    </div>

                    @if ($hasDiff)
                        <div class="audit-log-card__diff-toggle">
                            <button type="button" class="audit-log-expand-btn" wire:click="toggleExpand({{ $log->id }})">
                                <i class="fa-solid fa-code-compare"></i>
                                {{ $isExpanded ? 'إخفاء التفاصيل' : 'عرض التفاصيل — قبل / بعد' }}
                                <i class="fa-solid fa-chevron-down audit-log-expand-btn__chevron @if($isExpanded) is-open @endif"></i>
                            </button>
                        </div>

                        @if ($isExpanded)
                            <div class="audit-log-card__diff">
                                @include('partials.admin.audit-diff', ['log' => $log])
                            </div>
                        @endif
                    @endif
                </div>
            </article>
        @empty
            <div class="audit-log-empty">
                <i class="fa-solid fa-inbox"></i>
                <h3>لا توجد سجلات</h3>
                <p>لم يُعثر على أحداث{{ $group !== 'all' ? ' في هذا القسم' : '' }}.</p>
            </div>
        @endforelse
    </div>

    @if ($this->logs->hasPages())
        <div class="audit-log-pagination">{{ $this->logs->links() }}</div>
    @endif
</div>

@include('partials.admin.shell-end')
