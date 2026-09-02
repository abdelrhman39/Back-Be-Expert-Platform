<?php

use App\Support\IdentityThemes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'قوالب الهوية',
    'adminPageDesc' => 'تبديل هوية الصفحة الرئيسية دون المساس بالعناصر أو البرمجة',
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
        $this->savedMessage = 'تم تطبيق قالب «'.(IdentityThemes::find($key)['name_ar'] ?? $key).'» على الصفحة الرئيسية. المحتوى والعناصر الأساسية لم تتغير.';
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
        $this->savedMessage = 'حُفظت الهوية الحالية كقالب يمكن إعادة تفعيله لاحقاً.';
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
    $packs = collect(\App\Support\IdentityThemes::all())
        ->when($filter !== 'all', fn ($c) => $c->filter(fn ($pack) => ($pack['category'] ?? '') === $filter))
        ->values();
    $active = \App\Support\IdentityThemes::active();
    $activeSwatches = \App\Support\IdentityThemes::swatches($active);
    $customized = \App\Support\IdentityThemes::isCustomized();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/identity-themes.css') }}?v=1">
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
            <h2>استوديو هوية الصفحة الرئيسية</h2>
            <p class="admin-crud-card__meta">القوالب تغيّر الألوان وأجواء الهيرو فقط. البلوكات، القوائم، الشعارات، واسم الجهة تبقى كما هي ويمكن ضبطها من إعدادات المنصة.</p>
        </div>
        <div class="identity-studio__intro">
            <ol class="identity-studio__howto">
                <li>
                    <span class="identity-studio__step">1</span>
                    <span>اختر قالباً يناسب الجهة (جامعة، معهد، شركة، جهة حكومية) من المعرض أدناه.</span>
                </li>
                <li>
                    <span class="identity-studio__step">2</span>
                    <span>اضغط «تفعيل» لتطبيق الألوان فوراً على الصفحة الرئيسية دون تغيير البرمجة أو العناصر.</span>
                </li>
                <li>
                    <span class="identity-studio__step">3</span>
                    <span>إن احتجت ضبطاً دقيقاً: عدّل الألوان أو الشعارات من <a href="{{ route('admin.settings') }}">إعدادات المنصة</a> ثم احفظ النتيجة كهوية مخصصة.</span>
                </li>
            </ol>
            <aside class="identity-active">
                <p class="identity-active__label">القالب النشط الآن</p>
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
            <h2>حفظ الهوية الحالية</h2>
            <p class="admin-crud-card__meta">بعد ضبط الألوان لجهة معيّنة احفظها باسم واضح لتعيد تفعيلها لاحقاً دون إعادة الضبط.</p>
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
            <h2>معرض القوالب</h2>
            <p class="admin-crud-card__meta">{{ $packs->count() }} قالب · التفعيل يستبدل الألوان فقط.</p>
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
                    @php
                        $swatches = \App\Support\IdentityThemes::swatches($pack);
                        $isActive = \App\Support\IdentityThemes::isActive($pack['id']);
                        $hero = $pack['colors']['theme_color_primary_dark'] ?? '#135f3d';
                        $mid = $pack['colors']['theme_color_primary'] ?? '#1b8354';
                    @endphp
                    <article @class(['identity-card', 'is-active' => $isActive])>
                        <div class="identity-card__preview">
                            <div class="identity-card__hero" style="background: linear-gradient(115deg, {{ $hero }} 0%, {{ $mid }} 58%, {{ $pack['colors']['theme_color_primary_light'] ?? $mid }} 100%);"></div>
                            <div class="identity-card__bar"></div>
                            <div class="identity-card__title-block">
                                <div class="identity-card__line"></div>
                                <div class="identity-card__line identity-card__line--short"></div>
                            </div>
                            @if ($isActive)
                                <span class="identity-card__badge">نشط</span>
                            @endif
                        </div>
                        <div class="identity-card__body">
                            <h3 class="identity-card__name">{{ $pack['name_ar'] }}</h3>
                            <p class="identity-card__tagline">{{ $pack['tagline_ar'] }}</p>
                            <p class="identity-card__audience">{{ $pack['audience_ar'] }}</p>
                            <div class="identity-card__swatches" aria-hidden="true">
                                @foreach ($swatches as $swatch)
                                    <span class="identity-swatch" style="background: {{ $swatch }}"></span>
                                @endforeach
                            </div>
                            <div class="identity-card__actions">
                                @if ($isActive)
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" disabled>مفعّل حالياً</button>
                                @else
                                    <button
                                        type="button"
                                        class="admin-btn-primary admin-btn-primary--sm"
                                        wire:click="applyTheme('{{ $pack['id'] }}')"
                                        wire:confirm="سيتم تطبيق ألوان «{{ $pack['name_ar'] }}» على الصفحة الرئيسية دون تغيير المحتوى أو العناصر. المتابعة؟"
                                    >تفعيل</button>
                                @endif
                                @if (empty($pack['builtin']))
                                    <button
                                        type="button"
                                        class="admin-btn-secondary admin-btn-secondary--sm"
                                        wire:click="deleteCustom('{{ $pack['id'] }}')"
                                        wire:confirm="حذف قالب «{{ $pack['name_ar'] }}»؟ لن يؤثر ذلك على المحتوى."
                                    >حذف</button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

</div>

@include('partials.admin.shell-end')
