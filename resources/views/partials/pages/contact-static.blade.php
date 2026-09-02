@php
    use App\Models\PlatformSetting;

    $locale = app()->getLocale();
    $supportEmail = PlatformSetting::get('support_email', 'info@domain.edu.sa') ?? 'info@domain.edu.sa';
    $supportPhone = PlatformSetting::get('support_phone', '966543406744') ?? '966543406744';
    $whatsappNumber = PlatformSetting::get('whatsapp_number', $supportPhone) ?? $supportPhone;
    $phoneDigits = preg_replace('/\D+/', '', $supportPhone);
    $whatsappDigits = preg_replace('/\D+/', '', $whatsappNumber);
    $phoneDisplay = str_starts_with($phoneDigits, '966') ? '+'.$phoneDigits : '+966'.ltrim($phoneDigits, '0');
    $whatsappDisplay = str_starts_with($whatsappDigits, '966') ? '+'.$whatsappDigits : '+966'.ltrim($whatsappDigits, '0');
    $mapEmbedUrl = \App\Support\CampusMap::embedUrl();
@endphp

<div class="breadcrumb-bar">
    <div class="breadcrumb-img">
        <div class="breadcrumb-left">
            <img src="{{ static_asset(platform_campus_path('entrance')) }}" alt="{{ platform_org() }}">
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">تواصل معنا</li>
                    </ol>
                </nav>
                <h1 class="breadcrumb-title">تواصل معنا</h1>
            </div>
        </div>
    </div>
</div>

<div class="contact-page-intro">
    <div class="container py-4 py-md-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h2 class="h5 fw-bold text-dark mb-3">نرحب بتواصلكم</h2>
                <p class="lead text-muted mb-0">يسر فريق {{ platform_org() }} استقبال استفساراتكم بخصوص البرامج التدريبية، التسجيل، والدعم الفني. نسعى للرد خلال أوقات العمل الرسمية، ولطلبات تقنية يمكنكم فتح تذكرة دعم.</p>
            </div>
            <div class="col-lg-5">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a class="btn btn-outline-primary" href="{{ route('support.faq', ['locale' => $locale]) }}">الأسئلة الشائعة</a>
                    <a class="btn btn-primary" href="{{ route('support.ticket.new', ['locale' => $locale]) }}">فتح تذكرة دعم</a>
                    <a class="btn btn-outline-secondary" href="{{ route('support.ticket.search', ['locale' => $locale]) }}">متابعة تذكرة</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="contact-bottom">
    <div class="container">
        <div class="row g-4">
            <div class="col-xl-3 col-md-6 col-sm-6 d-flex">
                <div class="contact-grid con-info w-100">
                    <div class="contact-content">
                        <div class="contact-icon">
                            <span>
                                <img src="{{ static_asset('assets/contact-mail.svg') }}" alt="">
                            </span>
                        </div>
                        <div class="contact-details">
                            <h6>البريد الإلكتروني للتواصل</h6>
                            <p><a href="mailto:{{ $supportEmail }}" dir="ltr">{{ $supportEmail }}</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 d-flex">
                <div class="contact-grid con-info w-100">
                    <div class="contact-content">
                        <div class="contact-icon">
                            <span>
                                <img src="{{ static_asset('assets/contact-phone.svg') }}" alt="">
                            </span>
                        </div>
                        <div class="contact-details">
                            <h6>رقم الجوال</h6>
                            <a href="tel:{{ $phoneDisplay }}" dir="ltr">{{ $phoneDisplay }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 d-flex">
                <div class="contact-grid con-info w-100">
                    <div class="contact-content">
                        <div class="contact-icon">
                            <span>
                                <i class="fa-brands fa-whatsapp fa-lg"></i>
                            </span>
                        </div>
                        <div class="contact-details">
                            <h6>واتساب</h6>
                            <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener" dir="ltr">{{ $whatsappDisplay }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-sm-6 d-flex">
                <div class="contact-grid con-info w-100">
                    <div class="contact-content">
                        <div class="contact-icon">
                            <span>
                                <img src="{{ static_asset('assets/contact-map.svg') }}" alt="">
                            </span>
                        </div>
                        <div class="contact-details contact-details-address">
                            <h6>العنوان</h6>
                            <p>{{ platform_org() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="contact-section">
    <div class="contact-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6 col-md-12 d-flex">
                    <div class="contact-map w-100">
                        <iframe
                            src="{{ $mapEmbedUrl }}"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ platform_org() }}"
                        ></iframe>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 d-flex">
                    <div class="team-form w-100" id="contact-us-Form">
                        <div class="team-form-heading">
                            <h3>ابقَ على تواصل</h3>
                        </div>
                        <form
                            action="#"
                            id="contact-form"
                            class="cms-contact-form"
                            method="post"
                            novalidate
                            data-support-email="{{ $supportEmail }}"
                            data-ticket-url="{{ route('support.ticket.new', ['locale' => $locale]) }}"
                            data-complain-value="complain"
                            data-max-length="150"
                        >
                            @csrf
                            <div class="form-group">
                                <label class="form-label" for="contact-name">الاسم<span class="text-danger">*</span></label>
                                <input class="form-control" name="name" id="contact-name" type="text" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-email">البريد الإلكتروني<span class="text-danger">*</span></label>
                                <input class="form-control" name="email" id="contact-email" type="email" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-phone">رقم الجوال<span class="text-danger">*</span></label>
                                <input class="form-control" name="phone" id="contact-phone" type="tel" dir="ltr" placeholder="+9665XXXXXXXX" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-reason">سبب التواصل<span class="text-danger">*</span></label>
                                <select class="form-control" name="reason_for_connect" id="contact-reason" required>
                                    <option value="asking">استفسار</option>
                                    <option value="partnership">شراكة</option>
                                    <option value="complain">شكوى</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact-message">
                                    رسالة<span class="text-danger">*</span>
                                    <span class="alert-span">(يجب ألا تتعدى 150 حرفاً)</span>
                                </label>
                                <textarea name="message" class="form-control" id="contact-message" cols="30" rows="4" maxlength="150" required></textarea>
                                <div class="char-counter">
                                    <span id="char-count">0</span>/150 حرف
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3">إرسال</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>