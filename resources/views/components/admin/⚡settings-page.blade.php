<?php

use App\Models\PlatformSetting;
use App\Services\LogoImageProcessor;
use App\Support\FooterSettings;
use App\Support\LogoSettings;
use App\Support\PosterSettings;
use App\Support\ThemeSettings;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات المنصة',
    'adminPageDesc' => 'التحكم في الإعدادات العامة للموقع',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'إعدادات المنصة'],
    ],
])]
#[Title('إعدادات المنصة | لوحة التحكم')]
class extends Component
{
    use WithFileUploads;

    public string $platformNameAr = '';

    public string $platformNameEn = '';

    public string $platformOrgAr = '';

    public string $platformOrgEn = '';

    public string $supportEmail = '';

    public string $supportPhone = '';

    public string $whatsappNumber = '';

    public string $defaultLocale = 'ar';

    public bool $maintenanceMode = false;

    public string $defaultPosterImage = '';

    public string $logoPrimary = '';

    public string $logoSecondary = '';

    public string $logoFooter = '';

    public string $logoVision = '';

    public string $favicon = '';

    public $logoPrimaryFile = null;

    public $logoSecondaryFile = null;

    public $logoFooterFile = null;

    public $logoVisionFile = null;

    public $faviconFile = null;

    public bool $showLogoPrimary = true;

    public bool $showLogoSecondary = true;

    public bool $showLogoFooter = true;

    public bool $showLogoVision = false;

    public string $footerLogosMode = 'first';

    /** @var array<string, string> */
    public array $themeColors = [];

    /** @var array<string, string> */
    public array $footerTexts = [];

    public bool $footerShowPaymentIcons = true;

    public bool $footerShowContactSection = true;

    public bool $footerShowSocialLinks = true;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $this->platformNameAr = PlatformSetting::get('platform_name_ar', 'منصة مركز التعلم المستمر') ?? '';
        $this->platformNameEn = PlatformSetting::get('platform_name_en', 'Continuing Learning Center Platform') ?? '';
        $this->platformOrgAr = PlatformSetting::get('platform_org_ar', 'جامعة الامير مقرن') ?? '';
        $this->platformOrgEn = PlatformSetting::get('platform_org_en', 'Muqrin University') ?? '';
        $this->supportEmail = PlatformSetting::get('support_email', '') ?? '';
        $this->supportPhone = PlatformSetting::get('support_phone', '') ?? '';
        $this->whatsappNumber = PlatformSetting::get('whatsapp_number', '') ?? '';
        $this->defaultLocale = PlatformSetting::get('default_locale', 'ar') ?? 'ar';
        $this->maintenanceMode = PlatformSetting::get('maintenance_mode', '0') === '1';
        $this->defaultPosterImage = PlatformSetting::get('default_poster_image') ?? PosterSettings::defaultAssetPath();
        $this->footerTexts = FooterSettings::formDefaults();
        $this->footerShowPaymentIcons = FooterSettings::showPaymentIcons();
        $this->footerShowContactSection = FooterSettings::showContactSection();
        $this->footerShowSocialLinks = FooterSettings::showSocialLinks();
        $this->logoPrimary = PlatformSetting::get(LogoSettings::KEY_PRIMARY) ?? LogoSettings::defaultPath(LogoSettings::KEY_PRIMARY);
        $this->logoSecondary = PlatformSetting::get(LogoSettings::KEY_SECONDARY) ?? LogoSettings::defaultPath(LogoSettings::KEY_SECONDARY);
        $this->logoFooter = PlatformSetting::get(LogoSettings::KEY_FOOTER) ?? LogoSettings::defaultPath(LogoSettings::KEY_FOOTER);
        $this->logoVision = PlatformSetting::get(LogoSettings::KEY_VISION) ?? LogoSettings::defaultPath(LogoSettings::KEY_VISION);
        $this->favicon = PlatformSetting::get(LogoSettings::KEY_FAVICON) ?? LogoSettings::defaultPath(LogoSettings::KEY_FAVICON);
        $this->showLogoPrimary = LogoSettings::isVisible(LogoSettings::KEY_PRIMARY);
        $this->showLogoSecondary = LogoSettings::isVisible(LogoSettings::KEY_SECONDARY);
        $this->showLogoFooter = LogoSettings::isVisible(LogoSettings::KEY_FOOTER);
        $this->showLogoVision = LogoSettings::isVisible(LogoSettings::KEY_VISION);
        $this->footerLogosMode = LogoSettings::footerLogosMode();
        $this->themeColors = ThemeSettings::formDefaults();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canAdmin('settings.manage'), 403);

        $this->validate([
            'platformNameAr' => ['required', 'string', 'max:120'],
            'platformNameEn' => ['nullable', 'string', 'max:120'],
            'platformOrgAr' => ['nullable', 'string', 'max:120'],
            'platformOrgEn' => ['nullable', 'string', 'max:120'],
            'supportEmail' => ['nullable', 'email'],
            'supportPhone' => ['nullable', 'string', 'max:20'],
            'whatsappNumber' => ['nullable', 'string', 'max:20'],
            'defaultLocale' => ['required', 'in:ar,en'],
            'defaultPosterImage' => ['nullable', 'string', 'max:500'],
            'footerTexts' => ['array'],
            'footerTexts.*' => ['nullable', 'string', 'max:2000'],
            'footerShowPaymentIcons' => ['boolean'],
            'footerShowContactSection' => ['boolean'],
            'footerShowSocialLinks' => ['boolean'],
            'logoPrimary' => ['nullable', 'string', 'max:500'],
            'logoSecondary' => ['nullable', 'string', 'max:500'],
            'logoFooter' => ['nullable', 'string', 'max:500'],
            'logoVision' => ['nullable', 'string', 'max:500'],
            'favicon' => ['nullable', 'string', 'max:500'],
            'logoPrimaryFile' => ['nullable', 'image', 'max:2048'],
            'logoSecondaryFile' => ['nullable', 'image', 'max:2048'],
            'logoFooterFile' => ['nullable', 'image', 'max:2048'],
            'logoVisionFile' => ['nullable', 'image', 'max:2048'],
            'faviconFile' => ['nullable', 'image', 'max:512'],
            'showLogoPrimary' => ['boolean'],
            'showLogoSecondary' => ['boolean'],
            'showLogoFooter' => ['boolean'],
            'showLogoVision' => ['boolean'],
            'footerLogosMode' => ['required', 'in:first,both'],
            ...ThemeSettings::validationRules(),
        ]);

        foreach ($this->themeColors as $key => $value) {
            if (! array_key_exists($key, ThemeSettings::definitions())) {
                continue;
            }

            $trimmed = trim((string) $value);

            if ($trimmed === '') {
                continue;
            }

            if (strtolower($trimmed) === 'transparent') {
                $this->themeColors[$key] = 'transparent';

                continue;
            }

            $normalized = ThemeSettings::normalizeColor($trimmed);

            if ($normalized === null) {
                $this->addError('themeColors.'.$key, 'صيغة اللون غير صالحة.');

                return;
            }

            $this->themeColors[$key] = $normalized;
        }

        $logoPrimary = $this->resolveLogoPath($this->logoPrimary, $this->logoPrimaryFile, LogoSettings::KEY_PRIMARY);
        $logoSecondary = $this->resolveLogoPath($this->logoSecondary, $this->logoSecondaryFile, LogoSettings::KEY_SECONDARY);
        $logoFooter = $this->resolveLogoPath($this->logoFooter, $this->logoFooterFile, LogoSettings::KEY_FOOTER);
        $logoVision = $this->resolveLogoPath($this->logoVision, $this->logoVisionFile, LogoSettings::KEY_VISION);
        $favicon = $this->resolveLogoPath($this->favicon, $this->faviconFile, LogoSettings::KEY_FAVICON);

        PlatformSetting::set('platform_name_ar', $this->platformNameAr, 'general', 'اسم المنصة (عربي)');
        PlatformSetting::set('platform_name_en', $this->platformNameEn ?: $this->platformNameAr, 'general', 'اسم المنصة (إنجليزي)');
        PlatformSetting::set('platform_org_ar', $this->platformOrgAr, 'general', 'الجهة / الجامعة (عربي)');
        PlatformSetting::set('platform_org_en', $this->platformOrgEn ?: $this->platformOrgAr, 'general', 'الجهة / الجامعة (إنجليزي)');
        PlatformSetting::set('support_email', $this->supportEmail, 'general', 'البريد الرسمي');
        PlatformSetting::set('support_phone', $this->supportPhone, 'general', 'هاتف الدعم');
        PlatformSetting::set('whatsapp_number', $this->whatsappNumber, 'general', 'واتساب');
        PlatformSetting::set('default_locale', $this->defaultLocale, 'general', 'اللغة الافتراضية');
        PlatformSetting::set('maintenance_mode', $this->maintenanceMode ? '1' : '0', 'general', 'وضع الصيانة');
        $this->saveFooterSettings();
        PlatformSetting::set('default_poster_image', $this->defaultPosterImage ?: PosterSettings::defaultAssetPath(), 'general', 'الصورة الافتراضية للبوستر');
        PlatformSetting::set(LogoSettings::KEY_PRIMARY, $logoPrimary, 'branding', 'الشعار الرئيسي');
        PlatformSetting::set(LogoSettings::KEY_SECONDARY, $logoSecondary, 'branding', 'الشعار الثانوي (الهيدر)');
        PlatformSetting::set(LogoSettings::KEY_FOOTER, $logoFooter, 'branding', 'شعار الفوتر');
        PlatformSetting::set(LogoSettings::KEY_VISION, $logoVision, 'branding', 'شعار الرؤية (الفوتر)');
        PlatformSetting::set(LogoSettings::KEY_FAVICON, $favicon, 'branding', 'أيقونة الموقع (Favicon)');
        PlatformSetting::set(LogoSettings::KEY_PRIMARY_VISIBLE, $this->showLogoPrimary ? '1' : '0', 'branding', 'إظهار الشعار الرئيسي');
        PlatformSetting::set(LogoSettings::KEY_SECONDARY_VISIBLE, $this->showLogoSecondary ? '1' : '0', 'branding', 'إظهار الشعار الثانوي');
        PlatformSetting::set(LogoSettings::KEY_FOOTER_VISIBLE, $this->showLogoFooter ? '1' : '0', 'branding', 'إظهار شعار الفوتر');
        PlatformSetting::set(LogoSettings::KEY_VISION_VISIBLE, $this->showLogoVision ? '1' : '0', 'branding', 'إظهار شعار الرؤية');
        $footerLogosMode = $this->footerLogosMode === LogoSettings::FOOTER_LOGOS_BOTH
            ? LogoSettings::FOOTER_LOGOS_BOTH
            : LogoSettings::FOOTER_LOGOS_FIRST;
        PlatformSetting::set(LogoSettings::KEY_FOOTER_LOGOS_MODE, $footerLogosMode, 'branding', 'وضع شعارات الفوتر');
        // Keep mode and visibility in sync for the second footer logo.
        if ($footerLogosMode === LogoSettings::FOOTER_LOGOS_FIRST) {
            $this->showLogoVision = false;
            PlatformSetting::set(LogoSettings::KEY_VISION_VISIBLE, '0', 'branding', 'إظهار شعار الرؤية');
        } elseif ($this->showLogoVision === false) {
            $this->showLogoVision = true;
            PlatformSetting::set(LogoSettings::KEY_VISION_VISIBLE, '1', 'branding', 'إظهار شعار الرؤية');
        }

        foreach (ThemeSettings::definitions() as $key => $definition) {
            $value = trim((string) ($this->themeColors[$key] ?? ''));
            PlatformSetting::set($key, $value, 'theme', $definition['label_ar']);
        }

        $this->logoPrimary = $logoPrimary;
        $this->logoSecondary = $logoSecondary;
        $this->logoFooter = $logoFooter;
        $this->logoVision = $logoVision;
        $this->favicon = $favicon;
        $this->logoPrimaryFile = null;
        $this->logoSecondaryFile = null;
        $this->logoFooterFile = null;
        $this->logoVisionFile = null;
        $this->faviconFile = null;

        $this->savedMessage = 'تم حفظ إعدادات المنصة بنجاح.';
    }

    protected function saveFooterSettings(): void
    {
        foreach (FooterSettings::textFields() as $stem => $field) {
            foreach (['ar', 'en'] as $locale) {
                $key = FooterSettings::textKey($stem, $locale);
                PlatformSetting::set($key, trim((string) ($this->footerTexts[$key] ?? '')), 'footer', $field['label_ar'].' ('.$locale.')');
            }
        }

        PlatformSetting::set('footer_copyright_ar', trim((string) ($this->footerTexts['footer_copyright_ar'] ?? '')), 'footer', 'حقوق النشر في الفوتر (عربي)');
        PlatformSetting::set('footer_copyright_en', trim((string) ($this->footerTexts['footer_copyright_en'] ?? '')), 'footer', 'حقوق النشر في الفوتر (إنجليزي)');

        foreach (FooterSettings::linkFields() as $stem => $field) {
            foreach (['ar', 'en'] as $locale) {
                $key = "footer_link_{$stem}_url_{$locale}";
                PlatformSetting::set($key, trim((string) ($this->footerTexts[$key] ?? '')), 'footer', $field['label_ar'].' ('.$locale.')');
            }
        }

        foreach (FooterSettings::socialFields() as $stem => $field) {
            $key = "footer_social_{$stem}";
            PlatformSetting::set($key, trim((string) ($this->footerTexts[$key] ?? '')), 'footer', $field['label_ar']);
        }

        PlatformSetting::set('footer_show_payment_icons', $this->footerShowPaymentIcons ? '1' : '0', 'footer', 'إظهار أيقونات الدفع');
        PlatformSetting::set('footer_show_contact_section', $this->footerShowContactSection ? '1' : '0', 'footer', 'إظهار قسم التواصل');
        PlatformSetting::set('footer_show_social_links', $this->footerShowSocialLinks ? '1' : '0', 'footer', 'إظهار روابط التواصل الاجتماعي');
    }

    protected function resolveLogoPath(string $path, mixed $file, string $key): string
    {
        $previous = PlatformSetting::get($key);

        if ($file instanceof TemporaryUploadedFile) {
            $next = app(LogoImageProcessor::class)->storeOptimized($file, $key);
            $this->deleteStoredLogoIfReplaced($previous, $next);

            return $next;
        }

        $next = filled($path) ? $path : LogoSettings::defaultPath($key);
        $this->deleteStoredLogoIfReplaced($previous, $next);

        return $next;
    }

    protected function deleteStoredLogoIfReplaced(?string $previous, ?string $next): void
    {
        if (! $previous || $previous === $next) {
            return;
        }

        if (str_starts_with($previous, 'http://') || str_starts_with($previous, 'https://')) {
            return;
        }

        $storagePath = str_starts_with($previous, '/storage/')
            ? ltrim(substr($previous, strlen('/storage/')), '/')
            : (str_starts_with($previous, 'storage/') ? ltrim(substr($previous, strlen('storage/')), '/') : null);

        if ($storagePath) {
            Storage::disk('public')->delete($storagePath);
        }
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.settings'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'إعدادات المنصة'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<div class="admin-settings-panels">

<x-admin.collapsible-card title="الإعدادات العامة">
    <x-slot:meta>
        <p>تظهر بعض القيم في الموقع العام (الاسم، وسائل التواصل). لإعدادات البريد ومتغيرات <code dir="ltr">.env</code> راجع <a href="{{ route('admin.system-settings') }}">مركز إعدادات النظام</a>.</p>
    </x-slot:meta>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="platformNameAr">اسم المنصة (عربي)</label>
            <input id="platformNameAr" type="text" class="admin-control" wire:model="platformNameAr">
            @error('platformNameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="platformNameEn">اسم المنصة (إنجليزي)</label>
            <input id="platformNameEn" type="text" class="admin-control" wire:model="platformNameEn">
        </div>
        <div class="admin-field">
            <label for="platformOrgAr">الجهة / الجامعة (عربي)</label>
            <input id="platformOrgAr" type="text" class="admin-control" wire:model="platformOrgAr">
        </div>
        <div class="admin-field">
            <label for="platformOrgEn">الجهة / الجامعة (إنجليزي)</label>
            <input id="platformOrgEn" type="text" class="admin-control" wire:model="platformOrgEn">
        </div>
        <div class="admin-field">
            <label for="supportEmail">البريد الرسمي</label>
            <input id="supportEmail" type="email" class="admin-control" wire:model="supportEmail">
        </div>
        <div class="admin-field">
            <label for="supportPhone">هاتف الدعم</label>
            <input id="supportPhone" type="text" class="admin-control" wire:model="supportPhone">
        </div>
        <div class="admin-field">
            <label for="whatsappNumber">واتساب</label>
            <input id="whatsappNumber" type="text" class="admin-control" wire:model="whatsappNumber">
        </div>
        <div class="admin-field">
            <label for="defaultLocale">اللغة الافتراضية</label>
            <select id="defaultLocale" class="admin-control" wire:model="defaultLocale">
                <option value="ar">العربية</option>
                <option value="en">English</option>
            </select>
        </div>
        <div class="admin-field admin-field--wide">
            <label class="admin-check">
                <input type="checkbox" wire:model="maintenanceMode">
                <span>تفعيل وضع الصيانة</span>
            </label>
        </div>
    </div>

    <div class="admin-filter-actions">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save">حفظ الإعدادات</button>
        <a href="{{ route('admin.payment-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">إعدادات طرق الدفع</a>
        <a href="{{ route('admin.teams-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">Microsoft Teams</a>
        <a href="{{ route('admin.zoom-settings') }}" class="admin-btn-secondary admin-btn-secondary--sm">Zoom</a>
    </div>
</x-admin.collapsible-card>

<x-admin.collapsible-card title="شعارات المنصة">
    <x-slot:meta>
        <p>تحكم في شعار الهيدر، شعار الفوتر، شعار الرؤية، وأيقونة الموقع. يمكنك إخفاء أي شعار أو رفع صورة جديدة.</p>
    </x-slot:meta>
    @include('partials.admin.logo-setting-field', [
        'id' => 'logoPrimary',
        'label' => 'الشعار الرئيسي',
        'hint' => 'يظهر في الهيدر، القائمة الجانبية للوحة التحكم، وصفحة تسجيل دخول الإدارة.',
        'pathModel' => 'logoPrimary',
        'fileModel' => 'logoPrimaryFile',
        'previewUrl' => resolve_logo_url($logoPrimary, \App\Support\LogoSettings::KEY_PRIMARY),
        'settingKey' => \App\Support\LogoSettings::KEY_PRIMARY,
        'visibleModel' => 'showLogoPrimary',
    ])

    @include('partials.admin.logo-setting-field', [
        'id' => 'logoSecondary',
        'label' => 'الشعار الثانوي (بجانب الرئيسي في الهيدر)',
        'hint' => 'يظهر بجانب الشعار الرئيسي في شريط التنقل العلوي.',
        'pathModel' => 'logoSecondary',
        'fileModel' => 'logoSecondaryFile',
        'previewUrl' => resolve_logo_url($logoSecondary, \App\Support\LogoSettings::KEY_SECONDARY),
        'settingKey' => \App\Support\LogoSettings::KEY_SECONDARY,
        'visibleModel' => 'showLogoSecondary',
    ])

    <div class="admin-field" style="margin-bottom: 1.25rem; padding: 1rem 1.1rem; border: 1px solid var(--sa-border); border-radius: 12px; background: #f8fafc;">
        <label style="display:block; font-weight:700; margin-bottom:0.55rem;">شعارات الفوتر</label>
        <div class="admin-field-hint" style="margin-bottom:0.75rem;">اختر عرض الشعار الأول فقط، أو الشعارين معاً (شعار الفوتر + شعار الرؤية).</div>
        <div style="display:flex; flex-wrap:wrap; gap:1rem 1.5rem;">
            <label class="admin-check">
                <input type="radio" wire:model.live="footerLogosMode" value="first">
                <span>الأول فقط</span>
            </label>
            <label class="admin-check">
                <input type="radio" wire:model.live="footerLogosMode" value="both">
                <span>الاثنان معاً</span>
            </label>
        </div>
    </div>

    @include('partials.admin.logo-setting-field', [
        'id' => 'logoFooter',
        'label' => 'شعار الفوتر (الأول)',
        'hint' => 'الشعار الأساسي في أسفل الموقع.',
        'pathModel' => 'logoFooter',
        'fileModel' => 'logoFooterFile',
        'previewUrl' => resolve_logo_url($logoFooter, \App\Support\LogoSettings::KEY_FOOTER),
        'settingKey' => \App\Support\LogoSettings::KEY_FOOTER,
        'visibleModel' => 'showLogoFooter',
    ])

    @if ($footerLogosMode === 'both')
        @include('partials.admin.logo-setting-field', [
            'id' => 'logoVision',
            'label' => 'شعار الرؤية / الشريك (الثاني)',
            'hint' => 'يظهر بجانب شعار الفوتر عند اختيار «الاثنان معاً».',
            'pathModel' => 'logoVision',
            'fileModel' => 'logoVisionFile',
            'previewUrl' => resolve_logo_url($logoVision, \App\Support\LogoSettings::KEY_VISION),
            'settingKey' => \App\Support\LogoSettings::KEY_VISION,
        ])
    @endif

    @include('partials.admin.logo-setting-field', [
        'id' => 'favicon',
        'label' => 'أيقونة الموقع (Favicon)',
        'hint' => 'الأيقونة الصغيرة التي تظهر في تبويب المتصفح.',
        'pathModel' => 'favicon',
        'fileModel' => 'faviconFile',
        'previewUrl' => resolve_logo_url($favicon, \App\Support\LogoSettings::KEY_FAVICON),
        'settingKey' => \App\Support\LogoSettings::KEY_FAVICON,
    ])

    <div class="admin-filter-actions">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">حفظ الشعارات</span>
            <span wire:loading wire:target="save">جاري الحفظ...</span>
        </button>
    </div>
</x-admin.collapsible-card>

<x-admin.collapsible-card title="ألوان المنصة">
    <x-slot:meta>
        <p>تتحكم في الألوان الأساسية للموقع: الأزرار، الروابط، الخلفيات، والفوتر. اترك الحقل فارغاً للعودة للون الافتراضي.</p>
    </x-slot:meta>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        @foreach (\App\Support\ThemeSettings::group('platform') as $colorKey => $definition)
            @include('partials.admin.theme-color-field', [
                'id' => $colorKey,
                'label' => $definition['label_ar'],
                'colorKey' => $colorKey,
                'default' => $definition['default'],
            ])
        @endforeach
    </div>

    <div class="admin-filter-actions">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save">حفظ الألوان</button>
    </div>
</x-admin.collapsible-card>

<x-admin.collapsible-card title="ألوان الهيدر والنافبار">
    <x-slot:meta>
        <p>تحكم خاص بشريط التنقل العلوي: الخلفية، لون الروابط، والحدود. للصفحة الرئيسية استخدم لوناً فاتحاً للروابط إذا كانت الخلفية داكنة.</p>
    </x-slot:meta>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        @foreach (\App\Support\ThemeSettings::group('header') as $colorKey => $definition)
            @include('partials.admin.theme-color-field', [
                'id' => $colorKey,
                'label' => $definition['label_ar'],
                'colorKey' => $colorKey,
                'default' => $definition['default'],
            ])
        @endforeach
    </div>

    <div class="admin-filter-actions" style="margin-top: 1rem;">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save">حفظ ألوان الهيدر</button>
    </div>
</x-admin.collapsible-card>

<x-admin.collapsible-card title="الصورة الافتراضية للبوستر">
    <x-slot:meta>
        <p>تُعرض تلقائياً عندما لا تتوفر صورة مخصصة للدورة أو الدبلوم أو بطاقات الأخبار والبرامج.</p>
    </x-slot:meta>
    <div class="admin-filter-grid" style="grid-template-columns: 1fr 220px; align-items: start;">
        <div class="admin-field">
            @include('partials.admin.media-field', [
                'wireModel' => 'defaultPosterImage',
                'id' => 'defaultPosterImage',
                'label' => 'مسار الصورة',
                'hint' => 'مسار داخل new-platform/assets أو رابط كامل أو /storage/...',
                'previewUrl' => resolve_poster_url($defaultPosterImage ?: null),
                'placeholder' => 'assets/vendor/images/site-favicon.png',
            ])
            @error('defaultPosterImage')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>معاينة</label>
            <div style="border:1px solid var(--sa-border); border-radius:12px; padding:1rem; background:#fff; min-height:140px; display:flex; align-items:center; justify-content:center;">
                <img src="{{ resolve_poster_url($defaultPosterImage ?: null) }}" alt="" style="max-width:100%; max-height:120px; object-fit:contain;">
            </div>
        </div>
    </div>
</x-admin.collapsible-card>

<x-admin.collapsible-card title="عناصر الفوتر">
    <x-slot:meta>
        <p>تحكم موحّد في الفوتر لجميع صفحات الموقع (العربية والإنجليزية). يظهر نفس الفوتر في الصفحة الرئيسية، الصفحات الداخلية، ولوحة المستخدم.</p>
    </x-slot:meta>
    @include('partials.admin.footer-settings-fields')

    <div class="admin-field admin-field--wide" style="margin-top: 1rem;">
        <p class="admin-field-hint">معاينة حقوق النشر (عربي):</p>
        <div class="admin-field-hint" style="padding:0.75rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;" dir="rtl">{!! $footerTexts['footer_copyright_ar'] ?? '' !!}</div>
    </div>

    <div class="admin-filter-actions">
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save">حفظ عناصر الفوتر</button>
    </div>
</x-admin.collapsible-card>

</div>

@include('partials.admin.shell-end')
