<?php

use App\Support\IdentityThemes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'قوالب الهوية',
    'adminPageDesc' => 'هويات مستقلة لكل جهة: الشعار والألوان تُحفظ ولا يُحذف تقسيم عند تفعيل آخر',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['href' => '/admin/settings', 'label' => 'إعدادات المنصة'],
        ['label' => 'قوالب الهوية'],
    ],
])]
#[Title('قوالب الهوية | لوحة التحكم')]
class extends Component
{
    public string $filter = 'all';

    public string $customNameAr = '';

    public string $customNameEn = '';

    public ?string $savedMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('settings.view'), 403);
    }

    public function applyTheme(string $key): void
    {
        abort_unless(auth()->user()?->canAdmin('settings.manage'), 403);

        IdentityThemes::apply($key);
        $name = IdentityThemes::find($key)['name_ar'] ?? $key;
        $this->savedMessage = IdentityThemes::hasBranding(IdentityThemes::find($key) ?? [])
            ? 'تم تفعيل هوية «'.$name.'». الشعار واسم الجهة والألوان الخاصة بها أصبحت نشطة، والهويات الأخرى ما زالت محفوظة.'
            : 'تم تطبيق ألوان «'.$name.'» على الصفحة الرئيسية دون تغيير الشعار أو اسم الجهة.';
    }

    public function saveCurrentAsCustom(): void
    {
        abort_unless(auth()->user()?->canAdmin('settings.manage'), 403);

        $this->validate([
            'customNameAr' => ['required', 'string', 'max:80'],
            'customNameEn' => ['nullable', 'string', 'max:80'],
        ], [], [
            'customNameAr' => 'اسم الهوية',
            'customNameEn' => 'الاسم الإنجليزي',
        ]);

        $id = IdentityThemes::saveCustom($this->customNameAr, $this->customNameEn ?: null);
        IdentityThemes::apply($id);

        $this->customNameAr = '';
        $this->customNameEn = '';
        $this->filter = 'custom';
        $this->savedMessage = 'حُفظت الهوية الحالية (الألوان والشعار واسم الجهة) كتقسيم يمكن إعادة تفعيله لاحقاً.';
    }

    public function deleteCustom(string $key): void
    {
        abort_unless(auth()->user()?->canAdmin('settings.manage'), 403);

        IdentityThemes::deleteCustom($key);
        $this->savedMessage = 'تم حذف قالب الهوية المخصص.';
    }
};
?>

@php
    $divisions = collect(\App\Support\IdentityThemes::divisions())->values();
    $packs = collect(\App\Support\IdentityThemes::palettes())
        ->when($filter !== 'all', fn ($c) => $c->filter(fn ($pack) => ($pack['category'] ?? '') === $filter))
        ->values();
    $active = \App\Support\IdentityThemes::active();
    $activeSwatches = \App\Support\IdentityThemes::swatches($active);
    $customized = \App\Support\IdentityThemes::isCustomized();
    $activeLogo = \App\Support\IdentityThemes::logoUrl($active);
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/identity-themes.css') }}?v=3">
@endpush

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.identity-themes'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.settings'), 'label' => 'إعدادات المنصة'],
        ['label' => 'قوالب الهوية'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<div class="identity-studio">

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>استوديو هويات الجهات</h2>
            <p class="admin-crud-card__meta">كل جامعة تقسيم مستقل: الشعار والاسم والألوان محفوظة. تفعيل جامعة طيبة أو جامعة الملك خالد لا يحذف هوية الجامعة العربية المفتوحة، والعكس صحيح.</p>
        </div>
        <div class="identity-studio__intro">
            <ol class="identity-studio__howto">
                <li>
                    <span class="identity-studio__step">1</span>
                    <span>فعّل هوية الجامعة المطلوبة من قسم «هويات الجامعات» أدناه. كل هوية تبقى محفوظة بعد التبديل.</span>
                </li>
                <li>
                    <span class="identity-studio__step">2</span>
                    <span>لحفظ تقسيم إضافي لجهة جديدة: اضبط الشعار والاسم من الإعدادات ثم اضغط «حفظ كهوية مخصصة».</span>
                </li>
                <li>
                    <span class="identity-studio__step">3</span>
                    <span>لوحات الألوان تغيّر الأجواء فقط دون المساس بشعار الجامعة النشطة. المحتوى والبلوكات لا تتغير.</span>
                </li>
            </ol>
            <aside class="identity-active">
                <p class="identity-active__label">الهوية النشطة الآن</p>
                @if ($activeLogo)
                    <img src="{{ $activeLogo }}" alt="{{ $active['name_ar'] }}" class="identity-active__logo">
                @endif
                <h3 class="identity-active__name">{{ $active['name_ar'] }}</h3>
                <p class="identity-active__meta">
                    {{ $active['tagline_ar'] }}
                    @if ($customized)
                        <br><strong>معدّل يدوياً</strong> — الألوان الحالية تختلف عن أصل القالب.
                    @endif
                </p>
                <div class="identity-active__swatches" aria-hidden="true">
                    @foreach ($activeSwatches as $swatch)
                        <span class="identity-swatch" style="background: {{ $swatch }}"></span>
                    @endforeach
                </div>
                <div class="admin-filter-actions" style="margin-top:0.25rem;">
                    <a href="{{ route('home', ['locale' => 'ar']) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة الصفحة الرئيسية</a>
                    <a href="{{ route('admin.settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل الشعارات والاسم</a>
                </div>
            </aside>
        </div>
    </section>

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>هويات الجامعات</h2>
            <p class="admin-crud-card__meta">{{ $divisions->count() }} تقسيم مستقل · التفعيل يبدّل الشعار واسم الجهة والألوان دون حذف الباقي.</p>
        </div>
        <div class="identity-grid">
            @foreach ($divisions as $pack)
                @include('partials.admin.identity-card', ['pack' => $pack])
            @endforeach
        </div>
    </section>

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>حفظ تقسيم جديد</h2>
            <p class="admin-crud-card__meta">بعد ضبط شعار وألوان جهة جديدة احفظها باسم واضح لتعيد تفعيلها لاحقاً بجانب الجامعات المحفوظة.</p>
        </div>
        <form class="identity-save" wire:submit.prevent="saveCurrentAsCustom">
            <div class="admin-field">
                <label for="customNameAr">اسم الهوية (عربي)</label>
                <input id="customNameAr" type="text" class="admin-control" wire:model="customNameAr" placeholder="مثال: هوية كلية التقنية">
                @error('customNameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label for="customNameEn">الاسم الإنجليزي (اختياري)</label>
                <input id="customNameEn" type="text" class="admin-control" wire:model="customNameEn" placeholder="College identity">
            </div>
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ كهوية مخصصة</button>
        </form>
    </section>

    <section class="admin-crud-card">
        <div class="admin-crud-card__head">
            <h2>لوحات الألوان</h2>
            <p class="admin-crud-card__meta">{{ $packs->count() }} لوحة · تغيّر الألوان فقط دون استبدال شعار الجامعة النشطة.</p>
        </div>

        <div class="identity-filters" role="tablist" aria-label="تصنيف القوالب">
            @foreach (\App\Support\IdentityThemes::categories() as $key => $label)
                <button type="button" wire:click="$set('filter', '{{ $key }}')" @class(['is-active' => $filter === $key])>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($packs->isEmpty())
            <p class="admin-field-hint is-visible" style="margin-top:1rem;">لا توجد قوالب في هذا التصنيف بعد. احفظ الهوية الحالية لإنشاء أول قالب مخصص.</p>
        @else
            <div class="identity-grid" style="margin-top:1rem;">
                @foreach ($packs as $pack)
                    @include('partials.admin.identity-card', ['pack' => $pack])
                @endforeach
            </div>
        @endif
    </section>

</div>

@include('partials.admin.shell-end')
