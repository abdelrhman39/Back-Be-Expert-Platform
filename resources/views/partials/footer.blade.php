@php
    use App\Support\FooterSettings;
    use App\Support\LogoSettings;

    $locale = app()->getLocale();
    $footerPrograms = app(\App\Services\CmsMenuService::class)->tree('footer_programs', $locale);
    $footerPolicies = app(\App\Services\CmsMenuService::class)->tree('footer_policies', $locale);
    $footerCopyright = FooterSettings::copyrightHtml($locale);
    $socialLinks = FooterSettings::socialLinks($locale);
    $phone = FooterSettings::contactPhone();
    $whatsapp = FooterSettings::contactWhatsapp();
    $email = FooterSettings::contactEmail();
@endphp
<footer id="footer" class="footer" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">

    <div class="section-bg">
        <img src="{{ static_asset('assets/footer-bg-02.png') }}" class="footer-bg-two" alt="">
    </div>

    <div class="container">
        <div class="footer-top">
            <div class="row">

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12" data-aoss="fade-up" data-aoss-delay="500">
                    <div class="footer-widget">
                        @if (LogoSettings::showFooterPrimaryLogo() || LogoSettings::showFooterSecondaryLogo())
                            <div class="row g-3 footer-logos align-items-center">
                                @if (LogoSettings::showFooterPrimaryLogo())
                                    <div class="col-auto d-flex align-items-center justify-content-center footer-logos__item">
                                        <a href="{{ route('home', ['locale' => $locale]) }}">
                                            <img src="{{ platform_logo_url(LogoSettings::KEY_FOOTER) }}"
                                                class="{{ LogoSettings::cssClass(LogoSettings::KEY_FOOTER) }}" alt="{{ \App\Models\PlatformSetting::get('platform_name_ar', 'منصة مركز التعلم المستمر') }}">
                                        </a>
                                    </div>
                                @endif
                                @if (LogoSettings::showFooterSecondaryLogo())
                                    <div class="col-auto d-flex align-items-center justify-content-center footer-logos__item">
                                        <a href="{{ route('home', ['locale' => $locale]) }}">
                                            <img src="{{ platform_logo_url(LogoSettings::KEY_VISION) }}" alt="Vision Logo" class="{{ LogoSettings::cssClass(LogoSettings::KEY_VISION) }} vision-logo">
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (filled(FooterSettings::text('about', $locale)))
                            <p class="footer-about mt-3 mb-0">{{ FooterSettings::text('about', $locale) }}</p>
                        @endif
                    </div>
                </div>

                @if ($footerPrograms->isNotEmpty())
                    <div class="col-xl-3 col-lg-2 col-md-6 col-sm-6" data-aoss="fade-up" data-aoss-delay="600">
                        <div class="footer-widget">
                            <h3>{{ FooterSettings::text('programs_title', $locale) }}</h3>
                            <ul class="menu-items">
                                @include('partials.cms.footer-menu', ['items' => $footerPrograms])
                            </ul>
                        </div>
                    </div>
                @endif

                @if ($footerPolicies->isNotEmpty())
                    <div class="col-xl-5 col-lg-2 col-md-6 col-sm-6" data-aoss="fade-up" data-aoss-delay="600">
                        <div class="footer-widget">
                            <h3>{{ FooterSettings::text('policies_title', $locale) }}</h3>
                            <ul class="menu-items policies-grid">
                                @include('partials.cms.footer-menu', ['items' => $footerPolicies])
                            </ul>
                        </div>
                    </div>
                @endif

                @if (FooterSettings::showPaymentIcons())
                    <div class="col-12">
                        <div class="paypal-icons">
                            <a href="{{ route('home', ['locale' => $locale]) }}#"><img src="{{ static_asset('assets/mada_mini.webp') }}" alt="Mada"></a>
                            <a href="{{ route('home', ['locale' => $locale]) }}#"><img src="{{ static_asset('assets/credit_card_mini.png') }}" alt="Credit Card"></a>
                            <a href="{{ route('home', ['locale' => $locale]) }}#"><img src="{{ static_asset('assets/tabby_installment_mini.png') }}" alt="Tabby"></a>
                            <a href="{{ route('home', ['locale' => $locale]) }}#"><img src="{{ static_asset('assets/jeel.png') }}" alt="Jeel"></a>
                            <a href="{{ route('home', ['locale' => $locale]) }}#"><img src="{{ static_asset('assets/tamara.png') }}" alt="Tamara"></a>
                        </div>
                    </div>
                @endif

            </div>

            @if (FooterSettings::showContactSection() && (filled($phone) || filled($whatsapp) || filled($email)))
                <div class="contact-widget">
                    <div class="row align-items-center mx-0">
                        <div class="col-xl-9">
                            <ul class="location-list">
                                @if (filled($phone))
                                    <li>
                                        <span class="footer-contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                                        <div class="location-info">
                                            <h6>{{ FooterSettings::text('contact_phone_label', $locale) }}</h6>
                                            <p><a href="tel:+{{ ltrim($phone, '+') }}">+{{ ltrim($phone, '+') }}</a></p>
                                        </div>
                                    </li>
                                @endif

                                @if (filled($whatsapp))
                                    <li>
                                        <span class="footer-contact-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></span>
                                        <div class="location-info">
                                            <h6>{{ FooterSettings::text('contact_whatsapp_label', $locale) }}</h6>
                                            <p><a href="https://wa.me/{{ ltrim($whatsapp, '+') }}">+{{ ltrim($whatsapp, '+') }}</a></p>
                                        </div>
                                    </li>
                                @endif

                                @if (filled($email))
                                    <li>
                                        <span class="footer-contact-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                                        <div class="location-info">
                                            <h6>{{ FooterSettings::text('contact_email_label', $locale) }}</h6>
                                            <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        @if (FooterSettings::showSocialLinks() && $socialLinks !== [])
                            <div class="col-xl-3 text-xl-end text-center py-2">
                                <div class="social-links">
                                    <ul>
                                        @foreach ($socialLinks as $social)
                                            <li>
                                                <a target="_blank" rel="noopener" href="{{ $social['url'] }}" aria-label="{{ $social['label'] }}">
                                                    <i class="{{ $social['icon'] }}"></i>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="copy-right">
                        <p>{!! $footerCopyright !!}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer-bottom-links">
                        <ul>
                            <li>
                                <a href="{{ FooterSettings::linkUrl('statement', $locale) }}">
                                    {{ FooterSettings::text('link_statement', $locale) }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ FooterSettings::linkUrl('certificate', $locale) }}">
                                    {{ FooterSettings::text('link_certificate', $locale) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>

<style>
    .vision-logo { max-height: 100px; }
    @media (max-width: 768px) { .vision-logo { max-height: 80px; } }
    .footer-logos {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center;
        gap: 0.75rem 1rem;
        margin: 0;
    }
    .footer-logos.row > [class*="col"] ,
    .footer-logos__item {
        flex: 0 0 auto !important;
        width: auto !important;
        max-width: none !important;
        padding: 0 !important;
    }
    .footer-logos img.platform-logo,
    .footer-widget .footer-logos img {
        width: auto !important;
        height: auto !important;
        max-height: 90px;
        max-width: min(100%, 280px);
        object-fit: contain;
        display: block;
    }
    .footer-logos img.platform-logo--vision,
    .footer-logos img.vision-logo {
        max-height: 80px;
        max-width: min(100%, 200px);
    }
    .footer-about { line-height: 1.7; color: var(--platform-footer-text, #414040); }
    .footer .location-list .footer-contact-icon,
    .footer .location-list li > span {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary, #1b8354);
        background: color-mix(in oklab, var(--primary, #1b8354) 10%, #fff);
        border: 1px solid color-mix(in oklab, var(--primary, #1b8354) 22%, #fff);
        margin-inline-end: 0.65rem;
        flex-shrink: 0;
    }
    .footer .location-list .footer-contact-icon i,
    .footer .location-list li > span i {
        font-size: 1rem;
        line-height: 1;
        font-style: normal;
        display: inline-block;
    }
    .footer .location-list .footer-contact-icon .fa-solid,
    .footer .location-list li > span .fa-solid {
        font-family: "Font Awesome 6 Free" !important;
        font-weight: 900;
    }
    .footer .location-list .footer-contact-icon .fa-brands,
    .footer .location-list li > span .fa-brands {
        font-family: "Font Awesome 6 Brands" !important;
        font-weight: 400;
    }
</style>
