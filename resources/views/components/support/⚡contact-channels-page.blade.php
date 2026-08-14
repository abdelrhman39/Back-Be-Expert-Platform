<?php

use App\Models\PlatformSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('قنوات التواصل والدعم | مركز التعلم المستمر')]
class extends Component
{
    public function mount(): void
    {
        //
    }

    public function supportEmail(): string
    {
        return PlatformSetting::get('support_email', 'support@domain.test') ?? 'support@domain.test';
    }

    public function supportPhone(): string
    {
        return PlatformSetting::get('support_phone', '0500000000') ?? '0500000000';
    }
};
?>

@php $locale = app()->getLocale(); @endphp

<div class="support-page support-page--channels">
    @include('partials.support.nav', ['active' => 'contact'])

    <section class="support-channels-hero">
        <div class="container support-page__container">
            <h1 class="support-page__title">قنوات التواصل والدعم الفني</h1>
            <p class="support-page__lead">نلتزم بحماية بياناتك. تُستخدم معلومات الاتصال للرد على استفساراتك ودعمك التقني فقط.</p>
        </div>
    </section>

    <div class="container support-page__container pb-5">
        <div class="row g-4">
            <div class="col-md-6">
                <article class="support-channel-card">
                    <div class="support-channel-card__icon"><i class="fa-solid fa-envelope"></i></div>
                    <h2>البريد الإلكتروني</h2>
                    <p>أرسل استفساراتك ومشكلاتك التقنية — سنرد في أقرب وقت.</p>
                    <a href="mailto:{{ $this->supportEmail() }}" dir="ltr">{{ $this->supportEmail() }}</a>
                </article>
            </div>
            <div class="col-md-6">
                <article class="support-channel-card">
                    <div class="support-channel-card__icon"><i class="fa-solid fa-phone"></i></div>
                    <h2>الهاتف</h2>
                    <p>للتواصل المباشر مع فريق الدعم خلال أوقات العمل.</p>
                    <a href="tel:+966{{ ltrim($this->supportPhone(), '0') }}" dir="ltr">{{ $this->supportPhone() }}</a>
                </article>
            </div>
            <div class="col-md-6">
                <article class="support-channel-card">
                    <div class="support-channel-card__icon"><i class="fa-solid fa-ticket"></i></div>
                    <h2>بوابة التذاكر</h2>
                    <p>لفتح طلب دعم ومتابعة حالة المعالجة.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('support.ticket.new', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">فتح تذكرة</a>
                        <a href="{{ route('support.ticket.search', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">متابعة تذكرة</a>
                    </div>
                </article>
            </div>
            <div class="col-md-6">
                <article class="support-channel-card">
                    <div class="support-channel-card__icon"><i class="fa-solid fa-circle-question"></i></div>
                    <h2>الأسئلة الشائعة</h2>
                    <p>إجابات سريعة عن البرامج والتسجيل والمحاضرات.</p>
                    <a href="{{ route('support.faq', ['locale' => $locale]) }}" class="btn btn-outline-primary btn-sm">تصفح الأسئلة</a>
                </article>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}">
@endpush
