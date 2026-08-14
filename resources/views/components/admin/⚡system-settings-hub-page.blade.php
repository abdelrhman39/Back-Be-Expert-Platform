<?php

use App\Support\AdminPermissions;
use App\Support\RuntimeSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'مركز إعدادات النظام',
    'adminPageDesc' => 'التحكم في متغيرات البيئة والبريد والبنية التحتية من لوحة واحدة',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'إعدادات النظام'],
    ],
])]
#[Title('إعدادات النظام | لوحة التحكم')]
class extends Component
{
    public function mount(): void
    {
        abort_unless(RuntimeSettings::canViewHub(auth()->user()), 403);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.system-settings'),
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>مركز الإعدادات الموحّد</h2>
        <p class="admin-crud-card__meta">القيم المحفوظة هنا تتجاوز ملف <code dir="ltr">.env</code> عند التشغيل. الأسرار تُخزَّن مشفّرة في قاعدة البيانات.</p>
    </div>

    <div class="sys-settings-grid">
        @foreach (RuntimeSettings::sections() as $slug => $section)
            @if (RuntimeSettings::canManageSection(auth()->user(), $slug) || AdminPermissions::can(auth()->user(), 'system-settings.view'))
                <a href="{{ route('admin.system-settings.section', ['section' => $slug]) }}" class="sys-settings-card">
                    <span class="sys-settings-card__icon"><i class="fa-solid {{ $section['icon'] ?? 'fa-gear' }}"></i></span>
                    <strong>{{ $section['label'] }}</strong>
                    <p>{{ $section['description'] }}</p>
                    <span class="sys-settings-card__perm">{{ $section['permission'] ?? 'system-settings.manage' }}</span>
                </a>
            @endif
        @endforeach
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>التكاملات والإعدادات المتخصصة</h2>
        <p class="admin-crud-card__meta">صفحات إعدادات مرتبطة بخدمات خارجية — لكل منها صلاحية مستقلة.</p>
    </div>
    <div class="sys-settings-grid sys-settings-grid--integrations">
        @foreach (RuntimeSettings::integrationPages() as $page)
            @if (AdminPermissions::can(auth()->user(), $page['permission']))
                <a href="{{ route($page['route']) }}" class="sys-settings-card sys-settings-card--link">
                    <strong>{{ $page['label'] }}</strong>
                    <p>{{ $page['description'] }}</p>
                </a>
            @endif
        @endforeach
    </div>
</section>

<section class="admin-crud-card sys-settings-readonly">
    <div class="admin-crud-card__head">
        <h2>قراءة فقط</h2>
    </div>
    <ul class="teams-setup-steps">
        @foreach (config('runtime-settings.readonly_keys', []) as $key => $meta)
            <li><code dir="ltr">{{ $key }}</code> — {{ $meta['hint_ar'] ?? $meta['label_ar'] }}</li>
        @endforeach
    </ul>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .sys-settings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
    .sys-settings-card {
        display: block; padding: 1.1rem; border: 1px solid #e2e8f0; border-radius: 12px;
        text-decoration: none; color: inherit; background: #fff; transition: border-color .15s, box-shadow .15s;
    }
    .sys-settings-card:hover { border-color: #86efac; box-shadow: 0 6px 20px rgba(22,93,49,.08); }
    .sys-settings-card__icon { display: inline-flex; width: 2.25rem; height: 2.25rem; align-items: center; justify-content: center; border-radius: 8px; background: #ecfdf5; color: #165d31; margin-bottom: 0.5rem; }
    .sys-settings-card strong { display: block; font-size: 0.92rem; margin-bottom: 0.25rem; }
    .sys-settings-card p { margin: 0; font-size: 0.78rem; color: #64748b; line-height: 1.5; }
    .sys-settings-card__perm { display: inline-block; margin-top: 0.5rem; font-size: 0.68rem; color: #94a3b8; font-family: monospace; }
    .sys-settings-grid--integrations .sys-settings-card { background: #f8fafc; }
</style>
@endpush
