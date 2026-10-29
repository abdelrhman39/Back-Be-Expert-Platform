@php
    $swatches = \App\Support\IdentityThemes::swatches($pack);
    $isActive = \App\Support\IdentityThemes::isActive($pack['id']);
    $hero = $pack['colors']['theme_color_primary_dark'] ?? '#135f3d';
    $mid = $pack['colors']['theme_color_primary'] ?? '#1b8354';
    $logoUrl = \App\Support\IdentityThemes::logoUrl($pack);
    $branded = \App\Support\IdentityThemes::hasBranding($pack);
    $confirm = $branded
        ? 'سيتم تفعيل هوية «'.$pack['name_ar'].'» (الشعار واسم الجهة والألوان). الهوية الأخرى تبقى محفوظة ويمكن الرجوع إليها. المتابعة؟'
        : 'سيتم تطبيق ألوان «'.$pack['name_ar'].'» على الصفحة الرئيسية دون تغيير الشعار أو اسم الجهة. المتابعة؟';
@endphp
<article @class(['identity-card', 'is-active' => $isActive, 'identity-card--branded' => $branded]) data-pack="{{ $pack['id'] }}">
    <div class="identity-card__preview">
        <div class="identity-card__hero" style="background: linear-gradient(115deg, {{ $hero }} 0%, {{ $mid }} 58%, {{ $pack['colors']['theme_color_primary_light'] ?? $mid }} 100%);"></div>
        @if ($logoUrl)
            <div class="identity-card__logo-wrap">
                <img src="{{ $logoUrl }}" alt="{{ $pack['name_ar'] }}" class="identity-card__logo">
            </div>
        @else
            <div class="identity-card__bar"></div>
            <div class="identity-card__title-block">
                <div class="identity-card__line"></div>
                <div class="identity-card__line identity-card__line--short"></div>
            </div>
        @endif
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
                    wire:confirm="{{ $confirm }}"
                >تفعيل</button>
            @endif
            @if (empty($pack['builtin']))
                <button
                    type="button"
                    class="admin-btn-secondary admin-btn-secondary--sm"
                    wire:click="deleteCustom('{{ $pack['id'] }}')"
                    wire:confirm="حذف تقسيم «{{ $pack['name_ar'] }}»؟ الهوية النشطة الأخرى لن تُحذف."
                >حذف</button>
            @endif
        </div>
    </div>
</article>
