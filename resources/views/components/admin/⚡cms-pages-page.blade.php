<?php

use App\Models\CmsPage;
use App\Services\CmsMenuService;
use App\Services\CmsPageService;
use App\Support\CmsOptions;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('صفحات الموقع | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $footer = '';

    #[Url]
    public string $view = 'cards';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.view'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedFooter(): void
    {
        $this->resetPage();
    }

    public function setView(string $mode): void
    {
        if (in_array($mode, ['cards', 'table'], true)) {
            $this->view = $mode;
        }
    }

    public function quickFilter(string $field, string $value): void
    {
        if ($field === 'status') {
            if ($value === '') {
                $this->status = '';
            } elseif (array_key_exists($value, CmsOptions::pageStatuses())) {
                $this->status = $this->status === $value ? '' : $value;
            }
        }

        if ($field === 'type') {
            if ($value === '') {
                $this->type = '';
            } elseif (array_key_exists($value, CmsOptions::pageTypes())) {
                $this->type = $this->type === $value ? '' : $value;
            }
        }

        if ($field === 'footer') {
            if ($value === '') {
                $this->footer = '';
            } elseif (in_array($value, ['yes', 'no'], true)) {
                $this->footer = $this->footer === $value ? '' : $value;
            }
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->type = '';
        $this->footer = '';
        $this->resetPage();
    }

    #[Computed]
    public function stats(): array
    {
        return app(CmsPageService::class)->stats();
    }

    #[Computed]
    public function pageList()
    {
        return CmsPage::query()
            ->with(['translations', 'creator'])
            ->when($this->search, fn ($q) => $q->whereHas('translations', fn ($t) => $t
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->footer === 'yes', fn ($q) => $q->where('show_in_footer', true))
            ->when($this->footer === 'no', fn ($q) => $q->where('show_in_footer', false))
            ->orderByDesc('updated_at')
            ->paginate($this->view === 'cards' ? 12 : 15);
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->status !== '' || $this->type !== '' || $this->footer !== '';
    }

    public function toggleStatus(int $pageId, CmsPageService $pages): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.manage'), 403);

        $page = CmsPage::query()->with('translations')->findOrFail($pageId);
        $next = $page->status === 'published' ? 'draft' : 'published';
        $pages->setStatus($page, $next);
        app(CmsMenuService::class)->forgetCache();

        session()->flash('admin_message', $next === 'published' ? 'تم نشر الصفحة.' : 'تم إرجاع الصفحة إلى مسودة.');
    }

    public function duplicatePage(int $pageId, CmsPageService $pages): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.manage'), 403);

        $copy = $pages->duplicate(CmsPage::query()->with('translations')->findOrFail($pageId));
        session()->flash('admin_message', 'تم إنشاء نسخة من الصفحة.');
        $this->redirect(route('admin.cms-pages.edit', $copy), navigate: true);
    }

    public function deletePage(int $pageId, CmsPageService $pages): void
    {
        abort_unless(auth()->user()?->canAdmin('pages.manage'), 403);

        $page = CmsPage::query()->findOrFail($pageId);

        if (in_array($page->type, ['home'], true)) {
            session()->flash('admin_error', 'لا يمكن حذف صفحة الرئيسية — عدّلها بدلاً من ذلك.');

            return;
        }

        $pages->delete($page);
        app(CmsMenuService::class)->forgetCache();
        session()->flash('admin_message', 'تم حذف الصفحة.');
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.cms-pages'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'صفحات الموقع'],
    ],
])

@php
    $cms = app(CmsPageService::class);
    $stats = $this->stats;
@endphp

<div class="cms-hub">
    <section class="cms-hub-hero">
        <div class="cms-hub-hero__main">
            <span class="cms-hub-hero__eyebrow">إدارة المحتوى</span>
            <h1 class="cms-hub-hero__title">صفحات الموقع</h1>
            <p class="cms-hub-hero__desc">أنشئ وحرّر صفحات المنصة — محتوى ثنائي اللغة، SEO، تخطيطات متعددة، ونشر فوري.</p>
        </div>
        <div class="cms-hub-hero__aside">
            @canAdmin('pages.manage')
                <a href="{{ route('admin.cms-pages.create') }}" class="cms-hub-hero__cta">
                    <span aria-hidden="true">+</span>
                    صفحة جديدة
                </a>
            @endcanAdmin
            <a href="{{ route('admin.cms-menus') }}" class="cms-hub-hero__link">إدارة القوائم ←</a>
        </div>
    </section>

    <section class="cms-hub-kpis" aria-label="إحصائيات الصفحات">
        <button type="button" @class(['cms-hub-kpi', 'is-active' => $status === '']) wire:click="quickFilter('status', '')">
            <span class="cms-hub-kpi__icon cms-hub-kpi__icon--slate">📄</span>
            <span class="cms-hub-kpi__body">
                <strong>{{ $stats['total'] }}</strong>
                <small>إجمالي الصفحات</small>
            </span>
        </button>
        <button type="button" @class(['cms-hub-kpi', 'is-active' => $status === 'published']) wire:click="quickFilter('status', 'published')">
            <span class="cms-hub-kpi__icon cms-hub-kpi__icon--green">✓</span>
            <span class="cms-hub-kpi__body">
                <strong>{{ $stats['published'] }}</strong>
                <small>منشورة</small>
            </span>
        </button>
        <button type="button" @class(['cms-hub-kpi', 'is-active' => $status === 'draft']) wire:click="quickFilter('status', 'draft')">
            <span class="cms-hub-kpi__icon cms-hub-kpi__icon--amber">◷</span>
            <span class="cms-hub-kpi__body">
                <strong>{{ $stats['draft'] }}</strong>
                <small>مسودات</small>
            </span>
        </button>
        <button type="button" @class(['cms-hub-kpi', 'is-active' => $type === 'policy']) wire:click="quickFilter('type', 'policy')">
            <span class="cms-hub-kpi__icon cms-hub-kpi__icon--blue">§</span>
            <span class="cms-hub-kpi__body">
                <strong>{{ $stats['policies'] }}</strong>
                <small>سياسات</small>
            </span>
        </button>
        <button type="button" @class(['cms-hub-kpi', 'is-active' => $footer === 'yes']) wire:click="quickFilter('footer', 'yes')">
            <span class="cms-hub-kpi__icon cms-hub-kpi__icon--purple">⌂</span>
            <span class="cms-hub-kpi__body">
                <strong>{{ $stats['footer'] }}</strong>
                <small>في الفوتر</small>
            </span>
        </button>
    </section>

    <section class="cms-hub-toolbar">
        <div class="cms-hub-toolbar__search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="بحث بالعنوان أو الرابط...">
        </div>

        <div class="cms-hub-toolbar__filters">
            <select class="cms-hub-select" wire:model.live="status">
                <option value="">كل الحالات</option>
                @foreach (CmsOptions::pageStatuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select class="cms-hub-select" wire:model.live="type">
                <option value="">كل الأنواع</option>
                @foreach (CmsOptions::pageTypes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <select class="cms-hub-select" wire:model.live="footer">
                <option value="">الفوتر — الكل</option>
                <option value="yes">في الفوتر</option>
                <option value="no">خارج الفوتر</option>
            </select>
        </div>

        <div class="cms-hub-toolbar__view">
            @if ($this->hasActiveFilters)
                <button type="button" class="cms-hub-clear" wire:click="clearFilters">مسح الفلاتر</button>
            @endif
            <div class="cms-hub-view-toggle" role="group" aria-label="طريقة العرض">
                <button type="button" @class(['is-active' => $view === 'cards']) wire:click="setView('cards')" title="عرض بطاقات">▦</button>
                <button type="button" @class(['is-active' => $view === 'table']) wire:click="setView('table')" title="عرض جدول">☰</button>
            </div>
        </div>
    </section>

    <section class="cms-hub-results">
        <div class="cms-hub-results__head">
            <span>{{ $this->pageList->total() }} صفحة</span>
            @if ($this->hasActiveFilters)
                <span class="cms-hub-results__filtered">نتائج مفلترة</span>
            @endif
        </div>

        @if ($view === 'cards')
            <div class="cms-page-grid">
                @forelse ($this->pageList as $page)
                    @php
                        $tAr = $page->translate('ar');
                        $tEn = $page->translate('en');
                        $publicUrl = $cms->publicUrl($page, 'ar');
                    @endphp
                    @include('partials.admin.cms-page-card', compact('page', 'tAr', 'tEn', 'publicUrl'))
                @empty
                    <div class="cms-hub-empty">
                        <div class="cms-hub-empty__icon">📭</div>
                        <h3>لا توجد صفحات</h3>
                        <p>أنشئ صفحة جديدة أو شغّل <code>php artisan cms:import-policies</code></p>
                        @canAdmin('pages.manage')
                            <a href="{{ route('admin.cms-pages.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ صفحة جديدة</a>
                        @endcanAdmin
                    </div>
                @endforelse
            </div>
        @else
            @include('partials.admin.cms-page-table', [
                'pages' => $this->pageList,
                'cms' => $cms,
            ])
        @endif

        @if ($this->pageList->hasPages())
            <div class="cms-hub-pagination">{{ $this->pageList->links() }}</div>
        @endif
    </section>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cms-admin.css') }}">
@endpush

@include('partials.admin.shell-end')
