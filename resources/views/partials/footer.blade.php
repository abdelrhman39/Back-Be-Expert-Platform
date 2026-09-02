@php
    use App\Support\FooterSettings;
    use App\Support\LogoSettings;

    $locale = app()->getLocale();
    $footerPrograms = app(\App\Services\CmsMenuService::class)->tree('footer_programs', $locale);
    $footerPolicies = app(\App\Services\CmsPageService::class)->footerPolicyLinks($locale);
    $footerCopyright = FooterSettings::copyrightHtml($locale);
    $socialLinks = FooterSettings::socialLinks($locale);
    $phone = FooterSettings::contactPhone();
    $whatsapp = FooterSettings::contactWhatsapp();
    $email = FooterSettings::contactEmail();
@endphp
<footer id="footer" class="footer footer--atelier" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">

    <div class="container">
        <div class="footer-top">
            <div class="row">

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                    <div class="footer-widget">
                        @if (LogoSettings::showFooterPrimaryLogo() || LogoSettings::showFooterSecondaryLogo())
                            <div class="row g-3 footer-logos align-items-center">
                                @if (LogoSettings::showFooterPrimaryLogo())
                                    <div class="col-auto d-flex align-items-center justify-content-center footer-logos__item">
                                        <a href="{{ route('home', ['locale' => $locale]) }}">
                                            <img src="{{ platform_logo_url(LogoSettings::KEY_FOOTER) }}"
                                                class="{{ LogoSettings::cssClass(LogoSettings::KEY_FOOTER) }}" alt="{{ platform_name($locale) }}">
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
                            <p class="footer-about">{{ FooterSettings::text('about', $locale) }}</p>
                        @endif
                    </div>
                </div>

                @if ($footerPrograms->isNotEmpty())
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="footer-widget">
                            <h3>{{ FooterSettings::text('programs_title', $locale) }}</h3>
                            <ul class="menu-items">
                                @include('partials.cms.footer-menu', ['items' => $footerPrograms])
                            </ul>
                        </div>
                    </div>
                @endif

                @if ($footerPolicies->isNotEmpty())
                    <div class="col-xl-5 col-lg-4 col-md-12 col-sm-12">
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
                        <div class="footer__payments">
                            <p class="footer__eyebrow">{{ FooterSettings::text('payments_title', $locale) }}</p>
                            <div class="paypal-icons">
                                <span><img src="{{ static_asset('assets/mada_mini.webp') }}" alt="Mada"></span>
                                <span><img src="{{ static_asset('assets/credit_card_mini.png') }}" alt="Visa / Mastercard"></span>
                                <span><img src="{{ static_asset('assets/tabby_installment_mini.png') }}" alt="Tabby"></span>
                                <span><img src="{{ static_asset('assets/jeel.png') }}" alt="Jeel"></span>
                                <span><img src="{{ static_asset('assets/tamara.png') }}" alt="Tamara"></span>
                            </div>
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
