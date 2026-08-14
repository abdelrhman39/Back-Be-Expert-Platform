@php
    use App\Support\FooterSettings;
@endphp

<div class="admin-filter-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <div>
        <h3 style="font-size:1rem; margin:0 0 1rem;">العربية</h3>
        @foreach (FooterSettings::textFields() as $stem => $field)
            <div class="admin-field">
                <label for="footer_{{ $stem }}_ar">{{ $field['label_ar'] }}</label>
                @if ($stem === 'about')
                    <textarea id="footer_{{ $stem }}_ar" class="admin-control" rows="3" wire:model="footerTexts.footer_{{ $stem }}_ar" dir="rtl"></textarea>
                @else
                    <input id="footer_{{ $stem }}_ar" type="text" class="admin-control" wire:model="footerTexts.footer_{{ $stem }}_ar" dir="rtl">
                @endif
                @error('footerTexts.footer_'.$stem.'_ar')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endforeach

        <div class="admin-field">
            <label for="footer_copyright_ar">حقوق النشر (عربي)</label>
            <textarea id="footer_copyright_ar" class="admin-control" rows="3" wire:model="footerTexts.footer_copyright_ar" dir="rtl"></textarea>
            @error('footerTexts.footer_copyright_ar')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
    </div>

    <div>
        <h3 style="font-size:1rem; margin:0 0 1rem;">English</h3>
        @foreach (FooterSettings::textFields() as $stem => $field)
            <div class="admin-field">
                <label for="footer_{{ $stem }}_en">{{ $field['label_ar'] }}</label>
                @if ($stem === 'about')
                    <textarea id="footer_{{ $stem }}_en" class="admin-control" rows="3" wire:model="footerTexts.footer_{{ $stem }}_en" dir="ltr"></textarea>
                @else
                    <input id="footer_{{ $stem }}_en" type="text" class="admin-control" wire:model="footerTexts.footer_{{ $stem }}_en" dir="ltr">
                @endif
                @error('footerTexts.footer_'.$stem.'_en')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endforeach

        <div class="admin-field">
            <label for="footer_copyright_en">Copyright (English)</label>
            <textarea id="footer_copyright_en" class="admin-control" rows="3" wire:model="footerTexts.footer_copyright_en" dir="ltr"></textarea>
            @error('footerTexts.footer_copyright_en')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.25rem;">
    @foreach (FooterSettings::linkFields() as $stem => $field)
        <div class="admin-field">
            <label for="footer_link_{{ $stem }}_url_ar">{{ $field['label_ar'] }} (عربي)</label>
            <input id="footer_link_{{ $stem }}_url_ar" type="text" class="admin-control" wire:model="footerTexts.footer_link_{{ $stem }}_url_ar" dir="ltr" placeholder="{{ $field['default_ar'] }}">
            <div class="admin-field-hint">مسار legacy مثل <code dir="ltr">ar/statment.html</code> أو اسم route مثل <code dir="ltr">certificate-verify</code></div>
        </div>
        <div class="admin-field">
            <label for="footer_link_{{ $stem }}_url_en">{{ $field['label_ar'] }} (English)</label>
            <input id="footer_link_{{ $stem }}_url_en" type="text" class="admin-control" wire:model="footerTexts.footer_link_{{ $stem }}_url_en" dir="ltr" placeholder="{{ $field['default_en'] }}">
        </div>
    @endforeach
</div>

<div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 1.25rem;">
    @foreach (FooterSettings::socialFields() as $stem => $field)
        <div class="admin-field">
            <label for="footer_social_{{ $stem }}">{{ $field['label_ar'] }}</label>
            <input id="footer_social_{{ $stem }}" type="url" class="admin-control" wire:model="footerTexts.footer_social_{{ $stem }}" dir="ltr" placeholder="{{ $field['default'] ?: 'https://' }}">
        </div>
    @endforeach
</div>

<div class="admin-filter-grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 1.25rem;">
    <label class="admin-check">
        <input type="checkbox" wire:model="footerShowPaymentIcons">
        <span>إظهار أيقونات الدفع</span>
    </label>
    <label class="admin-check">
        <input type="checkbox" wire:model="footerShowContactSection">
        <span>إظهار قسم التواصل</span>
    </label>
    <label class="admin-check">
        <input type="checkbox" wire:model="footerShowSocialLinks">
        <span>إظهار روابط التواصل الاجتماعي</span>
    </label>
</div>

<p class="admin-field-hint" style="margin-top: 1rem;">
    قوائم «البرامج» و«السياسات» تُدار من <a href="{{ route('admin.cms-menus') }}">قوائم المحتوى (CMS)</a> وتظهر تلقائياً حسب اللغة.
    أرقام التواصل والبريد تُؤخذ من <strong>الإعدادات العامة</strong> أعلاه.
</p>
