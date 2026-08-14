<?php

use App\Models\PaymentSetting;
use App\Support\BnplSettings;
use App\Support\MoyasarSettings;
use App\Support\PaymentGatewaySettings;
use App\Support\PaymentMethods;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'إعدادات طرق الدفع',
    'adminPageDesc' => 'تحكم في التحويل البنكي وبوابة Moyasar للدفع الإلكتروني',
    'adminLayout' => 'app',
    'adminBreadcrumb' => [
        ['href' => '/admin', 'label' => 'الرئيسية'],
        ['label' => 'إعدادات طرق الدفع'],
    ],
])]
#[Title('إعدادات الدفع | لوحة التحكم')]
class extends Component
{
    public string $bankInstructionsAr = '';

    public string $bankInstructionsEn = '';

    public bool $moyasarEnabled = false;

    public string $moyasarSecretKey = '';

    public string $moyasarPublishableKey = '';

    public string $moyasarWebhookSecret = '';

    public string $moyasarCurrency = 'SAR';

    public bool $hasStoredSecretKey = false;

    public bool $hasStoredWebhookSecret = false;

    /** @var array<string, bool> */
    public array $gatewayEnabled = [];

    public string $tabbyPublicKey = '';

    public string $tabbySecretKey = '';

    public string $tabbyMerchantCode = '';

    public string $tamaraApiToken = '';

    public string $tamaraNotificationToken = '';

    public bool $hasStoredTabbySecret = false;

    public bool $hasStoredTamaraToken = false;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $this->bankInstructionsAr = PaymentSetting::get('bank_transfer_instructions_ar', '') ?? '';
        $this->bankInstructionsEn = PaymentSetting::get('bank_transfer_instructions_en', '') ?? '';

        $this->moyasarEnabled = MoyasarSettings::isEnabled();
        $this->moyasarPublishableKey = MoyasarSettings::publishableKey() ?? '';
        $this->moyasarCurrency = MoyasarSettings::currency();
        $this->hasStoredSecretKey = MoyasarSettings::hasStoredSecretKey();
        $this->hasStoredWebhookSecret = MoyasarSettings::hasStoredWebhookSecret();
        $this->gatewayEnabled = PaymentGatewaySettings::togglesForAdmin();

        $this->tabbyPublicKey = BnplSettings::tabbyPublicKey() ?? '';
        $this->tabbyMerchantCode = BnplSettings::tabbyMerchantCode() ?? '';
        $this->hasStoredTabbySecret = BnplSettings::hasStoredTabbySecret();
        $this->hasStoredTamaraToken = BnplSettings::hasStoredTamaraToken();

        // لا نعرض المفاتيح السرية في الواجهة — تُترك فارغة للإبقاء على القيمة المحفوظة
        $this->moyasarSecretKey = '';
        $this->moyasarWebhookSecret = '';
        $this->tabbySecretKey = '';
        $this->tamaraApiToken = '';
        $this->tamaraNotificationToken = '';
    }

    public function save(): void
    {
        $this->validate([
            'bankInstructionsAr' => ['required', 'string', 'min:10'],
            'bankInstructionsEn' => ['nullable', 'string'],
            'moyasarPublishableKey' => ['required_if:moyasarEnabled,true', 'nullable', 'string', 'max:255'],
            'moyasarSecretKey' => ['nullable', 'string', 'max:255'],
            'moyasarWebhookSecret' => ['nullable', 'string', 'max:255'],
            'moyasarCurrency' => ['required', 'string', 'in:'.implode(',', MoyasarSettings::allowedCurrencies())],
        ], [], [
            'bankInstructionsAr' => 'إرشادات التحويل البنكي (عربي)',
            'bankInstructionsEn' => 'إرشادات التحويل البنكي (إنجليزي)',
            'moyasarPublishableKey' => 'المفتاح العام (Publishable Key)',
            'moyasarSecretKey' => 'المفتاح السري (Secret Key)',
            'moyasarWebhookSecret' => 'سر Webhook',
            'moyasarCurrency' => 'العملة',
        ]);

        if ($this->moyasarEnabled
            && ! $this->hasStoredSecretKey
            && blank($this->moyasarSecretKey)
            && blank(config('moyasar.secret_key'))) {
            $this->addError('moyasarSecretKey', 'المفتاح السري مطلوب عند تفعيل Moyasar.');

            return;
        }

        if ($this->moyasarEnabled && blank($this->moyasarPublishableKey)) {
            $this->addError('moyasarPublishableKey', 'المفتاح العام مطلوب عند تفعيل Moyasar.');

            return;
        }

        if ($this->moyasarEnabled && ! $this->hasStoredWebhookSecret && blank($this->moyasarWebhookSecret) && blank(config('moyasar.webhook_secret'))) {
            $this->addError('moyasarWebhookSecret', 'سر Webhook مطلوب لحماية إشعارات الدفع عند تفعيل Moyasar.');

            return;
        }

        PaymentSetting::set('bank_transfer_instructions_ar', $this->bankInstructionsAr);
        PaymentSetting::set('bank_transfer_instructions_en', $this->bankInstructionsEn ?: $this->bankInstructionsAr);

        MoyasarSettings::setEnabled($this->moyasarEnabled);
        MoyasarSettings::setPublishableKey($this->moyasarPublishableKey);
        MoyasarSettings::setSecretKey($this->moyasarSecretKey);
        MoyasarSettings::setWebhookSecret($this->moyasarWebhookSecret);
        MoyasarSettings::setCurrency($this->moyasarCurrency);

        foreach ($this->gatewayEnabled as $methodId => $enabled) {
            PaymentGatewaySettings::setEnabled($methodId, (bool) $enabled);
        }

        BnplSettings::setTabbyPublicKey($this->tabbyPublicKey);
        BnplSettings::setTabbySecretKey($this->tabbySecretKey);
        BnplSettings::setTabbyMerchantCode($this->tabbyMerchantCode);
        BnplSettings::setTamaraApiToken($this->tamaraApiToken);
        BnplSettings::setTamaraNotificationToken($this->tamaraNotificationToken);

        $this->hasStoredSecretKey = MoyasarSettings::hasStoredSecretKey();
        $this->hasStoredWebhookSecret = MoyasarSettings::hasStoredWebhookSecret();
        $this->hasStoredTabbySecret = BnplSettings::hasStoredTabbySecret();
        $this->hasStoredTamaraToken = BnplSettings::hasStoredTamaraToken();
        $this->moyasarSecretKey = '';
        $this->moyasarWebhookSecret = '';
        $this->tabbySecretKey = '';
        $this->tamaraApiToken = '';
        $this->tamaraNotificationToken = '';

        $this->savedMessage = 'تم حفظ إعدادات الدفع بنجاح.';
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.payment-settings'),
    'shellActiveHeader' => 'settings',
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'إعدادات طرق الدفع'],
    ],
])

@php
    $moyasarReady = MoyasarSettings::isConfigured();
    $webhookReady = filled(MoyasarSettings::webhookSecret());
    $activeMethods = collect(PaymentMethods::all())
        ->filter(fn (array $method) => PaymentGatewaySettings::isEnabled($method['id']))
        ->count();
    $methodIcons = [
        'bank_transfer' => 'fa-solid fa-building-columns',
        'mada' => 'fa-solid fa-credit-card',
        'visa' => 'fa-brands fa-cc-visa',
        'mastercard' => 'fa-brands fa-cc-mastercard',
        'apple_pay' => 'fa-brands fa-apple-pay',
        'tabby' => 'fa-solid fa-calendar-check',
        'tamara' => 'fa-solid fa-clock',
        'platform_installment' => 'fa-solid fa-layer-group',
    ];
@endphp

<div class="payment-settings-page">
    @if ($savedMessage)
        <div class="admin-alert admin-alert--success is-visible payment-save-message" role="status">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>{{ $savedMessage }}</span>
        </div>
    @endif

    <section class="payment-hero" aria-labelledby="payment-settings-title">
        <div class="payment-hero__main">
            <span class="payment-hero__icon"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <span class="payment-hero__eyebrow">مركز المدفوعات</span>
                <h1 id="payment-settings-title">إدارة بوابات وطرق الدفع</h1>
                <p>فعّل وسائل السداد، اربط حساب Moyasar، وحدّث تعليمات التحويل البنكي من مكان واحد.</p>
            </div>
        </div>
        <div class="payment-hero__stats">
            <div class="payment-stat">
                <span class="payment-stat__label">حالة Moyasar</span>
                <strong class="{{ $moyasarReady ? 'is-success' : ($moyasarEnabled ? 'is-warning' : '') }}">
                    <i class="fa-solid {{ $moyasarReady ? 'fa-circle-check' : ($moyasarEnabled ? 'fa-triangle-exclamation' : 'fa-circle-pause') }}"></i>
                    {{ $moyasarReady ? 'جاهزة' : ($moyasarEnabled ? 'تحتاج إعداد' : 'معطّلة') }}
                </strong>
            </div>
            <div class="payment-stat">
                <span class="payment-stat__label">الوسائل النشطة</span>
                <strong>{{ $activeMethods }} <small>من {{ count(PaymentMethods::all()) }}</small></strong>
            </div>
            <div class="payment-stat">
                <span class="payment-stat__label">عملة التحصيل</span>
                <strong dir="ltr">{{ $moyasarCurrency }}</strong>
            </div>
        </div>
    </section>

    <nav class="payment-section-nav" aria-label="أقسام إعدادات الدفع">
        <a href="#moyasar-settings"><i class="fa-solid fa-link"></i> ربط Moyasar</a>
        <a href="#payment-methods"><i class="fa-solid fa-credit-card"></i> وسائل الدفع</a>
        <a href="#bank-transfer"><i class="fa-solid fa-building-columns"></i> التحويل البنكي</a>
        <a href="{{ route('admin.installment-settings') }}"><i class="fa-solid fa-layer-group"></i> إعدادات التقسيط</a>
    </nav>

    <section id="moyasar-settings" class="payment-panel">
        <div class="payment-panel__head">
            <div class="payment-panel__title">
                <span class="payment-step">01</span>
                <div>
                    <h2>ربط بوابة Moyasar</h2>
                    <p>الدفع بالبطاقات ومدى وApple Pay مع تحقق آمن من إشعارات العمليات.</p>
                </div>
            </div>
            <label class="payment-master-toggle">
                <span>
                    <strong>تشغيل البوابة</strong>
                    <small>{{ $moyasarEnabled ? 'مسموح باستقبال المدفوعات' : 'لن تظهر وسائل Moyasar للطلاب' }}</small>
                </span>
                <input type="checkbox" wire:model.live="moyasarEnabled">
                <span class="payment-switch" aria-hidden="true"></span>
            </label>
        </div>

        <div class="payment-readiness">
            <div class="{{ filled($moyasarPublishableKey) ? 'is-ready' : '' }}">
                <i class="fa-solid {{ filled($moyasarPublishableKey) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                المفتاح العام
            </div>
            <div class="{{ $hasStoredSecretKey || filled(config('moyasar.secret_key')) ? 'is-ready' : '' }}">
                <i class="fa-solid {{ $hasStoredSecretKey || filled(config('moyasar.secret_key')) ? 'fa-circle-check' : 'fa-circle' }}"></i>
                المفتاح السري
            </div>
            <div class="{{ $webhookReady ? 'is-ready' : '' }}">
                <i class="fa-solid {{ $webhookReady ? 'fa-circle-check' : 'fa-circle' }}"></i>
                حماية Webhook
            </div>
        </div>

        <div class="payment-config-grid">
            <div class="payment-config-fields">
                <div class="admin-field">
                    <label for="moyasarPublishableKey">المفتاح العام <span>Publishable Key</span></label>
                    <div class="payment-input">
                        <i class="fa-solid fa-key"></i>
                        <input type="text"
                            id="moyasarPublishableKey"
                            class="admin-control"
                            wire:model="moyasarPublishableKey"
                            dir="ltr"
                            placeholder="pk_test_..."
                            autocomplete="off">
                    </div>
                    @error('moyasarPublishableKey')
                        <div class="admin-field-hint is-visible">{{ $message }}</div>
                    @enderror
                </div>

                <div class="admin-field">
                    <label for="moyasarCurrency">عملة التحصيل</label>
                    <div class="payment-input">
                        <i class="fa-solid fa-coins"></i>
                        <select id="moyasarCurrency" class="admin-control" wire:model="moyasarCurrency">
                            @foreach (MoyasarSettings::allowedCurrencies() as $currency)
                                <option value="{{ $currency }}">{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('moyasarCurrency')
                        <div class="admin-field-hint is-visible">{{ $message }}</div>
                    @enderror
                </div>

                <div class="admin-field">
                    <label for="moyasarSecretKey">المفتاح السري <span>Secret Key</span></label>
                    <div class="payment-input">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password"
                            id="moyasarSecretKey"
                            class="admin-control"
                            wire:model="moyasarSecretKey"
                            dir="ltr"
                            placeholder="{{ $hasStoredSecretKey ? 'محفوظ — اتركه فارغاً للإبقاء عليه' : 'sk_test_...' }}"
                            autocomplete="new-password">
                    </div>
                    @if ($hasStoredSecretKey)
                        <div class="payment-field-state is-ready"><i class="fa-solid fa-shield-halved"></i> محفوظ ومشفّر</div>
                    @endif
                    @error('moyasarSecretKey')
                        <div class="admin-field-hint is-visible">{{ $message }}</div>
                    @enderror
                </div>

                <div class="admin-field">
                    <label for="moyasarWebhookSecret">سر Webhook <span>Secret Token</span></label>
                    <div class="payment-input">
                        <i class="fa-solid fa-fingerprint"></i>
                        <input type="password"
                            id="moyasarWebhookSecret"
                            class="admin-control"
                            wire:model="moyasarWebhookSecret"
                            dir="ltr"
                            placeholder="{{ $hasStoredWebhookSecret ? 'محفوظ — اتركه فارغاً للإبقاء عليه' : 'Webhook secret token' }}"
                            autocomplete="new-password">
                    </div>
                    @if ($hasStoredWebhookSecret)
                        <div class="payment-field-state is-ready"><i class="fa-solid fa-shield-halved"></i> محفوظ ومشفّر</div>
                    @endif
                    @error('moyasarWebhookSecret')
                        <div class="admin-field-hint is-visible">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <aside class="payment-webhook-card">
                <span class="payment-webhook-card__icon"><i class="fa-solid fa-code-branch"></i></span>
                <h3>إعداد Webhook</h3>
                <p>انسخ الرابط إلى لوحة Moyasar، ثم استخدم Secret Token نفسه في الطرفين.</p>
                <label for="moyasarWebhookUrl">رابط استقبال الإشعارات</label>
                <div class="payment-copy-field">
                    <input id="moyasarWebhookUrl" type="text" readonly dir="ltr" value="{{ url('/webhooks/moyasar') }}">
                    <button type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('moyasarWebhookUrl').value); this.querySelector('span').textContent='تم النسخ';">
                        <i class="fa-regular fa-copy"></i>
                        <span>نسخ</span>
                    </button>
                </div>
                <ul>
                    <li><i class="fa-solid fa-check"></i> يجب أن يكون الرابط متاحاً عبر HTTPS في الإنتاج.</li>
                    <li><i class="fa-solid fa-check"></i> لا تُشارك المفتاح السري أو Secret Token.</li>
                    <li><i class="fa-solid fa-check"></i> تُراجع العملية من خادم Moyasar قبل اعتمادها.</li>
                </ul>
            </aside>
        </div>
    </section>

    <section id="bnpl-gateways" class="payment-panel">
        <div class="payment-panel__head">
            <div class="payment-panel__title">
                <span class="payment-step">02b</span>
                <div>
                    <h2>Tabby و Tamara (تقسيط البوابة)</h2>
                    <p>يُرسل كامل مبلغ الطلب للبوابة، وهي تتولى التقسيط للمشتري. لا تُنشأ خطط أقساط داخل المنصة لهذه الوسائل.</p>
                </div>
            </div>
        </div>

        <div class="payment-config-grid">
            <div class="payment-config-fields">
                <h3 class="mb-3">Tabby</h3>
                <div class="admin-field">
                    <label class="admin-label">Merchant Code</label>
                    <input type="text" class="admin-control" wire:model="tabbyMerchantCode" dir="ltr" placeholder="merchant_code">
                </div>
                <div class="admin-field">
                    <label class="admin-label">Public Key</label>
                    <input type="text" class="admin-control" wire:model="tabbyPublicKey" dir="ltr">
                </div>
                <div class="admin-field">
                    <label class="admin-label">Secret Key {{ $hasStoredTabbySecret ? '(محفوظ — اتركه فارغاً للإبقاء)' : '' }}</label>
                    <input type="password" class="admin-control" wire:model="tabbySecretKey" dir="ltr" autocomplete="new-password">
                </div>
                <div class="payment-copy-field mb-3">
                    <input type="text" readonly dir="ltr" value="{{ url('/webhooks/tabby') }}">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ url('/webhooks/tabby') }}')">نسخ Webhook</button>
                </div>

                <h3 class="mb-3 mt-4">Tamara</h3>
                <div class="admin-field">
                    <label class="admin-label">API Token {{ $hasStoredTamaraToken ? '(محفوظ — اتركه فارغاً للإبقاء)' : '' }}</label>
                    <input type="password" class="admin-control" wire:model="tamaraApiToken" dir="ltr" autocomplete="new-password">
                </div>
                <div class="admin-field">
                    <label class="admin-label">Notification Token</label>
                    <input type="password" class="admin-control" wire:model="tamaraNotificationToken" dir="ltr" autocomplete="new-password">
                </div>
                <div class="payment-copy-field">
                    <input type="text" readonly dir="ltr" value="{{ url('/webhooks/tamara') }}">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ url('/webhooks/tamara') }}')">نسخ Webhook</button>
                </div>
            </div>
            <aside class="payment-webhook-card">
                <span class="payment-webhook-card__icon"><i class="fa-solid fa-circle-info"></i></span>
                <h3>قاعدة مهمة</h3>
                <ul>
                    <li><i class="fa-solid fa-check"></i> السداد المباشر وبطاقات Moyasar = كامل المبلغ.</li>
                    <li><i class="fa-solid fa-check"></i> تقسيط المنصة = خطط نشطة داخل النظام فقط.</li>
                    <li><i class="fa-solid fa-check"></i> Tabby/Tamara = كامل المبلغ للبوابة وهي تدير الأقساط.</li>
                </ul>
            </aside>
        </div>
    </section>

    <section id="payment-methods" class="payment-panel">
        <div class="payment-panel__head">
            <div class="payment-panel__title">
                <span class="payment-step">02</span>
                <div>
                    <h2>وسائل الدفع الظاهرة للطالب</h2>
                    <p>تظهر الوسيلة في صفحة السداد بعد تفعيلها واكتمال متطلبات البوابة المرتبطة بها.</p>
                </div>
            </div>
        </div>

        <div class="payment-methods-grid">
            @foreach (PaymentMethods::all() as $method)
                @php
                    $isLive = PaymentGatewaySettings::isEnabled($method['id']);
                    $isSelected = $gatewayEnabled[$method['id']] ?? false;
                @endphp
                <article class="payment-method-card {{ $isLive ? 'is-live' : '' }}">
                    <div class="payment-method-card__top">
                        <span class="payment-method-card__icon">
                            <i class="{{ $methodIcons[$method['id']] ?? 'fa-solid fa-money-check-dollar' }}"></i>
                        </span>
                        <label class="payment-card-toggle" title="تفعيل {{ $method['label'] }}">
                            <input type="checkbox" wire:model="gatewayEnabled.{{ $method['id'] }}">
                            <span class="payment-switch" aria-hidden="true"></span>
                        </label>
                    </div>
                    <h3>{{ $method['label'] }}</h3>
                    <p>{{ $method['description'] }}</p>
                    <div class="payment-method-card__status">
                        @if ($isLive)
                            <span class="is-success"><i class="fa-solid fa-circle-check"></i> نشطة وجاهزة</span>
                        @elseif ($isSelected)
                            <span class="is-warning"><i class="fa-solid fa-circle-exclamation"></i> تحتاج إكمال الإعداد</span>
                        @else
                            <span><i class="fa-solid fa-circle-minus"></i> معطّلة</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section id="bank-transfer" class="payment-panel">
        <div class="payment-panel__head">
            <div class="payment-panel__title">
                <span class="payment-step">03</span>
                <div>
                    <h2>تعليمات التحويل البنكي</h2>
                    <p>المحتوى الذي يشاهده الطالب عند اختيار التحويل البنكي في صفحة إتمام الشراء.</p>
                </div>
            </div>
            <span class="payment-security-note"><i class="fa-solid fa-eye"></i> يظهر للطالب كما تكتبه هنا</span>
        </div>

        <div class="payment-editor-panel">
            <div class="payment-editor-panel__head">
                <span class="payment-language">AR</span>
                <div>
                    <h3>التعليمات العربية</h3>
                    <p>النسخة الأساسية والمطلوبة.</p>
                </div>
            </div>
            <div class="admin-field admin-field--wide">
                @include('partials.admin.wysiwyg', [
                    'model' => 'bankInstructionsAr',
                    'value' => $bankInstructionsAr,
                    'direction' => 'rtl',
                    'language' => 'ar',
                    'height' => 340,
                ])
                @error('bankInstructionsAr')
                    <div class="admin-field-hint is-visible">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <details class="payment-editor-panel payment-editor-panel--optional" open>
            <summary>
                <span class="payment-language">EN</span>
                <span>
                    <strong>التعليمات الإنجليزية</strong>
                    <small>اختيارية — اضغط للتحرير</small>
                </span>
                <i class="fa-solid fa-chevron-down"></i>
            </summary>
            <div class="admin-field admin-field--wide payment-editor-panel__body">
                @include('partials.admin.wysiwyg', [
                    'model' => 'bankInstructionsEn',
                    'value' => $bankInstructionsEn,
                    'direction' => 'ltr',
                    'language' => 'en',
                    'height' => 300,
                ])
                @error('bankInstructionsEn')
                    <div class="admin-field-hint is-visible">{{ $message }}</div>
                @enderror
            </div>
        </details>
    </section>

    <div class="payment-save-bar">
        <div>
            <i class="fa-solid fa-circle-info"></i>
            <span>لن تُطبّق التغييرات على صفحة السداد حتى تحفظ الإعدادات.</span>
        </div>
        <div class="payment-save-bar__actions">
            <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk"></i> حفظ إعدادات الدفع</span>
                <span wire:loading.inline-flex wire:target="save"><i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...</span>
            </button>
        </div>
    </div>
</div>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .payment-settings-page {
        --pay-primary: #166534;
        --pay-primary-soft: #f0fdf4;
        --pay-border: #e2e8f0;
        --pay-muted: #64748b;
        --pay-text: #172033;
        display: grid;
        gap: 1rem;
        padding-bottom: 5.5rem;
    }
    .payment-save-message {
        display: flex !important;
        align-items: center;
        gap: .6rem;
        margin: 0;
    }
    .payment-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
        gap: 1.5rem;
        align-items: center;
        padding: 1.5rem;
        color: #fff;
        background: #123f2a;
        border-radius: 16px;
        overflow: hidden;
    }
    .payment-hero__main {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }
    .payment-hero__icon {
        display: inline-flex;
        width: 3.5rem;
        height: 3.5rem;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 14px;
        background: rgba(255,255,255,.1);
        font-size: 1.35rem;
    }
    .payment-hero__eyebrow {
        display: block;
        margin-bottom: .2rem;
        color: #bbf7d0;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
    }
    .payment-hero h1 {
        margin: 0 0 .35rem;
        color: #fff;
        font-size: 1.3rem;
    }
    .payment-hero p {
        max-width: 650px;
        margin: 0;
        color: rgba(255,255,255,.72);
        font-size: .82rem;
        line-height: 1.7;
    }
    .payment-hero__stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 12px;
        background: rgba(0,0,0,.1);
    }
    .payment-stat {
        min-width: 0;
        padding: .85rem;
        text-align: center;
    }
    .payment-stat + .payment-stat { border-inline-start: 1px solid rgba(255,255,255,.14); }
    .payment-stat__label {
        display: block;
        margin-bottom: .28rem;
        color: rgba(255,255,255,.62);
        font-size: .66rem;
    }
    .payment-stat strong {
        color: #fff;
        font-size: .86rem;
        white-space: nowrap;
    }
    .payment-stat strong.is-success { color: #86efac; }
    .payment-stat strong.is-warning { color: #fde68a; }
    .payment-stat strong small { color: rgba(255,255,255,.55); font-size: .65rem; font-weight: 500; }
    .payment-section-nav {
        display: flex;
        gap: .5rem;
        padding: .55rem;
        overflow-x: auto;
        border: 1px solid var(--pay-border);
        border-radius: 12px;
        background: #fff;
    }
    .payment-section-nav a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .55rem .8rem;
        color: #475569;
        border-radius: 8px;
        text-decoration: none;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .payment-section-nav a:hover { color: var(--pay-primary); background: var(--pay-primary-soft); }
    .payment-panel {
        scroll-margin-top: 1rem;
        padding: 1.25rem;
        border: 1px solid var(--pay-border);
        border-radius: 14px;
        background: #fff;
    }
    .payment-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #edf1f5;
    }
    .payment-panel__title {
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .payment-step {
        display: inline-flex;
        width: 2.3rem;
        height: 2.3rem;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 9px;
        color: var(--pay-primary);
        background: var(--pay-primary-soft);
        font: 800 .72rem/1 monospace;
    }
    .payment-panel h2, .payment-panel h3 { color: var(--pay-text); }
    .payment-panel__title h2 { margin: 0 0 .2rem; font-size: 1rem; }
    .payment-panel__title p { margin: 0; color: var(--pay-muted); font-size: .74rem; line-height: 1.55; }
    .payment-master-toggle {
        display: flex;
        align-items: center;
        gap: .8rem;
        cursor: pointer;
    }
    .payment-master-toggle > span:first-child { text-align: end; }
    .payment-master-toggle strong, .payment-master-toggle small { display: block; }
    .payment-master-toggle strong { color: var(--pay-text); font-size: .78rem; }
    .payment-master-toggle small { margin-top: .15rem; color: var(--pay-muted); font-size: .66rem; }
    .payment-master-toggle input, .payment-card-toggle input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }
    .payment-switch {
        position: relative;
        display: inline-block;
        width: 2.7rem;
        height: 1.48rem;
        flex: 0 0 auto;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background .2s;
    }
    .payment-switch::after {
        position: absolute;
        top: .19rem;
        inset-inline-start: .2rem;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 50%;
        background: #fff;
        content: "";
        transition: transform .2s;
    }
    input:checked + .payment-switch { background: #16a34a; }
    input:checked + .payment-switch::after { transform: translateX(-1.2rem); }
    [dir="ltr"] input:checked + .payment-switch::after { transform: translateX(1.2rem); }
    input:focus-visible + .payment-switch { outline: 3px solid rgba(22,163,74,.2); outline-offset: 2px; }
    .payment-readiness {
        display: flex;
        gap: .5rem;
        margin-bottom: 1rem;
        padding: .65rem;
        border-radius: 10px;
        background: #f8fafc;
    }
    .payment-readiness > div {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .65rem;
        color: #94a3b8;
        border: 1px solid var(--pay-border);
        border-radius: 999px;
        background: #fff;
        font-size: .68rem;
        font-weight: 700;
    }
    .payment-readiness > div.is-ready { color: #15803d; border-color: #bbf7d0; background: #f0fdf4; }
    .payment-config-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(270px, .7fr);
        gap: 1rem;
        align-items: start;
    }
    .payment-config-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .payment-config-fields .admin-field { margin: 0; }
    .payment-config-fields label {
        display: flex;
        justify-content: space-between;
        gap: .5rem;
        color: #334155;
        font-size: .74rem;
        font-weight: 750;
    }
    .payment-config-fields label span { color: #94a3b8; font: 500 .63rem/1.4 monospace; }
    .payment-input { position: relative; margin-top: .35rem; }
    .payment-input > i {
        position: absolute;
        top: 50%;
        inset-inline-start: .75rem;
        z-index: 1;
        color: #94a3b8;
        font-size: .72rem;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .payment-input .admin-control { width: 100%; padding-inline-start: 2.2rem; }
    .payment-field-state {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        margin-top: .35rem;
        font-size: .65rem;
    }
    .payment-field-state.is-ready { color: #15803d; }
    .payment-webhook-card {
        padding: 1rem;
        border: 1px solid #dbeafe;
        border-radius: 12px;
        background: #f8fbff;
    }
    .payment-webhook-card__icon {
        display: inline-flex;
        width: 2.4rem;
        height: 2.4rem;
        align-items: center;
        justify-content: center;
        margin-bottom: .65rem;
        color: #1d4ed8;
        border-radius: 9px;
        background: #dbeafe;
    }
    .payment-webhook-card h3 { margin: 0 0 .25rem; font-size: .86rem; }
    .payment-webhook-card > p { margin: 0 0 .8rem; color: var(--pay-muted); font-size: .7rem; line-height: 1.6; }
    .payment-webhook-card > label { display: block; margin-bottom: .3rem; color: #334155; font-size: .66rem; font-weight: 700; }
    .payment-copy-field { display: flex; direction: ltr; }
    .payment-copy-field input {
        width: 100%;
        min-width: 0;
        padding: .55rem .65rem;
        border: 1px solid #cbd5e1;
        border-right: 0;
        border-radius: 8px 0 0 8px;
        color: #475569;
        background: #fff;
        font-size: .66rem;
    }
    .payment-copy-field button {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: 0 .7rem;
        border: 0;
        border-radius: 0 8px 8px 0;
        color: #fff;
        background: #1d4ed8;
        font-size: .66rem;
        cursor: pointer;
    }
    .payment-webhook-card ul { display: grid; gap: .4rem; margin: .85rem 0 0; padding: 0; list-style: none; }
    .payment-webhook-card li { display: flex; gap: .4rem; color: #64748b; font-size: .65rem; line-height: 1.5; }
    .payment-webhook-card li i { margin-top: .18rem; color: #16a34a; }
    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .payment-method-card {
        min-width: 0;
        padding: 1rem;
        border: 1px solid var(--pay-border);
        border-radius: 12px;
        background: #fff;
        transition: border-color .15s, transform .15s;
    }
    .payment-method-card:hover { border-color: #bbf7d0; transform: translateY(-1px); }
    .payment-method-card.is-live { border-color: #bbf7d0; background: #fcfffd; }
    .payment-method-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .85rem; }
    .payment-method-card__icon {
        display: inline-flex;
        width: 2.45rem;
        height: 2.45rem;
        align-items: center;
        justify-content: center;
        color: #475569;
        border-radius: 9px;
        background: #f1f5f9;
        font-size: 1rem;
    }
    .payment-method-card.is-live .payment-method-card__icon { color: var(--pay-primary); background: var(--pay-primary-soft); }
    .payment-card-toggle { display: inline-flex; cursor: pointer; }
    .payment-card-toggle .payment-switch { width: 2.25rem; height: 1.25rem; }
    .payment-card-toggle .payment-switch::after { top: .17rem; width: .92rem; height: .92rem; }
    .payment-card-toggle input:checked + .payment-switch::after { transform: translateX(-.98rem); }
    .payment-method-card h3 { margin: 0 0 .25rem; font-size: .82rem; }
    .payment-method-card > p { min-height: 2.5em; margin: 0; color: var(--pay-muted); font-size: .67rem; line-height: 1.55; }
    .payment-method-card__status {
        margin-top: .75rem;
        padding-top: .65rem;
        border-top: 1px solid #f1f5f9;
        color: #94a3b8;
        font-size: .65rem;
        font-weight: 700;
    }
    .payment-method-card__status .is-success { color: #15803d; }
    .payment-method-card__status .is-warning { color: #b45309; }
    .payment-security-note {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .65rem;
        color: #475569;
        border-radius: 8px;
        background: #f8fafc;
        font-size: .67rem;
    }
    .payment-editor-panel {
        padding: 1rem;
        border: 1px solid var(--pay-border);
        border-radius: 12px;
        background: #f8fafc;
    }
    .payment-editor-panel__head, .payment-editor-panel--optional > summary {
        display: flex;
        align-items: center;
        gap: .65rem;
        margin-bottom: .85rem;
    }
    .payment-editor-panel__head h3 { margin: 0 0 .15rem; font-size: .8rem; }
    .payment-editor-panel__head p { margin: 0; color: var(--pay-muted); font-size: .66rem; }
    .payment-language {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: var(--pay-primary);
        border-radius: 8px;
        background: var(--pay-primary-soft);
        font: 800 .68rem/1 monospace;
    }
    .payment-editor-panel--optional { margin-top: .75rem; padding: 0; overflow: hidden; }
    .payment-editor-panel--optional > summary {
        margin: 0;
        padding: .85rem 1rem;
        cursor: pointer;
        list-style: none;
    }
    .payment-editor-panel--optional > summary::-webkit-details-marker { display: none; }
    .payment-editor-panel--optional > summary > span:nth-child(2) { flex: 1; }
    .payment-editor-panel--optional > summary strong, .payment-editor-panel--optional > summary small { display: block; }
    .payment-editor-panel--optional > summary strong { color: var(--pay-text); font-size: .76rem; }
    .payment-editor-panel--optional > summary small { margin-top: .15rem; color: var(--pay-muted); font-size: .64rem; }
    .payment-editor-panel--optional > summary > i { color: #94a3b8; transition: transform .2s; }
    .payment-editor-panel--optional[open] > summary > i { transform: rotate(180deg); }
    .payment-editor-panel__body { padding: 0 1rem 1rem; }
    .payment-editor-panel .tox-tinymce {
        border-color: #dbe2ea !important;
        border-radius: 9px !important;
    }
    .payment-save-bar {
        position: sticky;
        bottom: .75rem;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: .75rem 1rem;
        border: 1px solid #d8e0e8;
        border-radius: 12px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 8px 28px rgba(15,23,42,.1);
        backdrop-filter: blur(10px);
    }
    .payment-save-bar > div:first-child { display: flex; align-items: center; gap: .45rem; color: var(--pay-muted); font-size: .7rem; }
    .payment-save-bar > div:first-child i { color: #2563eb; }
    .payment-save-bar__actions { display: flex; gap: .5rem; }
    .payment-save-bar button span { align-items: center; gap: .4rem; }
    /* لا نفرض display على عناصر wire:loading حتى لا نتجاوز إخفاء Livewire الافتراضي (التنسيق المُقيَّد يرفع الأولوية) */
    .payment-save-bar button span:not([wire\:loading]):not([wire\:loading\.inline-flex]) { display: inline-flex; }
    @media (max-width: 1199.98px) {
        .payment-hero { grid-template-columns: 1fr; }
        .payment-config-grid { grid-template-columns: 1fr; }
        .payment-methods-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .payment-settings-page { padding-bottom: 1rem; }
        .payment-hero { padding: 1.1rem; }
        .payment-hero__main { align-items: flex-start; }
        .payment-hero__icon { width: 2.8rem; height: 2.8rem; }
        .payment-hero__stats { grid-template-columns: 1fr; }
        .payment-stat + .payment-stat { border-inline-start: 0; border-top: 1px solid rgba(255,255,255,.14); }
        .payment-panel { padding: 1rem; }
        .payment-panel__head { align-items: flex-start; flex-direction: column; }
        .payment-master-toggle { width: 100%; justify-content: space-between; }
        .payment-master-toggle > span:first-child { text-align: start; }
        .payment-readiness { overflow-x: auto; }
        .payment-readiness > div { white-space: nowrap; }
        .payment-config-fields, .payment-methods-grid { grid-template-columns: 1fr; }
        .payment-save-bar { position: static; align-items: stretch; flex-direction: column; }
        .payment-save-bar__actions { justify-content: stretch; }
        .payment-save-bar__actions > * { flex: 1; text-align: center; }
        .payment-security-note { display: none; }
    }
    .tox-tinymce {
        border-radius: 8px !important;
        border-color: var(--admin-border, #e2e8f0) !important;
    }
</style>
@endpush
