<?php

use App\Services\CertificateService;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('شهاداتي | مركز التعلم المستمر')]
class extends Component
{
    public function mount(): void
    {
        abort_unless(CertificateAccessSettings::portalEnabled(), 404);
    }

    #[Computed]
    public function certificates()
    {
        return app(CertificateService::class)->forUser(auth()->user());
    }

    #[Computed]
    public function stats(): array
    {
        $items = $this->certificates;

        return [
            'total' => $items->count(),
            'active' => $items->where('status', 'active')->count(),
        ];
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'certificates', 'portalTitle' => 'شهاداتي'])

<div class="portal-dashboard certificates-page">
    <section class="certificates-hero">
        <div class="certificates-hero__copy">
            <span class="certificates-hero__eyebrow">الوثائق الرسمية</span>
            <h1>شهاداتي</h1>
            <p>استعرض شهاداتك الصادرة، حمّل نسخة PDF، أو تحقق من الرمز لدى المنصة.</p>
        </div>
        <a href="{{ route('certificate-verify', ['locale' => $locale]) }}" class="certificates-hero__cta">
            <i class="fa-solid fa-shield-halved"></i>
            التحقق من شهادة
        </a>
    </section>

    @if ($this->certificates->isEmpty())
        <section class="certificates-empty">
            <i class="fa-solid fa-award" aria-hidden="true"></i>
            <h2>لا توجد شهادات صادرة حالياً</h2>
            @php
                $mode = CertificateAccessSettings::defaultVisibilityMode();
            @endphp
            <p>
                {{ match ($mode) {
                    'after_exam_pass' => 'تُصدر الشهادة تلقائياً بعد اجتياز الاختبار المطلوب.',
                    'after_graduation' => 'تُصدر الشهادات بعد إتمام البرنامج واعتماد التخرج.',
                    'after_graduation_and_exam' => 'تُصدر الشهادة بعد اعتماد التخرج واجتياز الاختبار المطلوب.',
                    default => 'تُصدر الشهادات تلقائياً عند استيفاء شروط الإصدار.',
                } }}
            </p>
        </section>
    @else
        <section class="certificates-kpis" aria-label="ملخص الشهادات">
            <article class="certificates-kpi">
                <span class="certificates-kpi__icon certificates-kpi__icon--award"><i class="fa-solid fa-award"></i></span>
                <div>
                    <strong>{{ $this->stats['total'] }}</strong>
                    <small>إجمالي الشهادات</small>
                </div>
            </article>
            <article class="certificates-kpi">
                <span class="certificates-kpi__icon certificates-kpi__icon--ok"><i class="fa-solid fa-circle-check"></i></span>
                <div>
                    <strong>{{ $this->stats['active'] }}</strong>
                    <small>سارية</small>
                </div>
            </article>
        </section>

        <div class="certificates-list">
            @foreach ($this->certificates as $certificate)
                <article class="certificate-card" wire:key="cert-{{ $certificate->id }}">
                    <header class="certificate-card__head">
                        <span class="certificate-card__mark" aria-hidden="true">
                            <i class="fa-solid {{ $certificate->isExternal() ? 'fa-building-columns' : 'fa-award' }}"></i>
                        </span>
                        <div class="certificate-card__title">
                            <h2>{{ $certificate->program_name }}</h2>
                            <p>
                                {{ $certificate->isExternal() ? 'شهادة خارجية' : 'شهادة المنصة' }}
                                · صدرت {{ $certificate->issued_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        @if ($certificate->isValid())
                            <span class="certificate-card__status is-valid">سارية</span>
                        @else
                            <span class="certificate-card__status is-muted">{{ $certificate->status }}</span>
                        @endif
                    </header>

                    <div class="certificate-card__meta">
                        <div>
                            <span>اسم الحاصل</span>
                            <strong>{{ $certificate->holder_name }}</strong>
                        </div>
                        <div>
                            <span>رمز التحقق</span>
                            <strong dir="ltr">{{ $certificate->code }}</strong>
                        </div>
                        @if ($certificate->isExternal())
                            <div>
                                <span>جهة الاعتماد</span>
                                <strong>{{ $certificate->external_issuer ?: '—' }}</strong>
                            </div>
                            @if ($certificate->external_credential_id)
                                <div>
                                    <span>رقم الاعتماد</span>
                                    <strong dir="ltr">{{ $certificate->external_credential_id }}</strong>
                                </div>
                            @endif
                        @elseif (CertificateAccessPolicy::showDetails($certificate) && ($certificate->program_started_at || $certificate->program_ended_at))
                            <div>
                                <span>فترة البرنامج</span>
                                <strong dir="ltr">
                                    {{ $certificate->program_started_at?->format('Y-m-d') ?? '—' }}
                                    →
                                    {{ $certificate->program_ended_at?->format('Y-m-d') ?? '—' }}
                                </strong>
                            </div>
                        @endif
                        <div>
                            <span>تاريخ الإصدار</span>
                            <strong>{{ $certificate->issued_at->translatedFormat('d M Y') }}</strong>
                        </div>
                    </div>

                    @if ($certificate->student_note)
                        <div class="certificate-card__note">
                            <i class="fa-solid fa-message" aria-hidden="true"></i>
                            <p>{{ $certificate->student_note }}</p>
                        </div>
                    @endif

                    <footer class="certificate-card__actions">
                        <a href="{{ route('certificates.show', ['locale' => $locale, 'certificate' => $certificate->id]) }}" class="certificate-btn certificate-btn--primary">
                            <i class="fa-solid fa-eye"></i> عرض
                        </a>
                        @if (CertificateAccessPolicy::canDownload($certificate))
                            <a href="{{ route('certificates.download', ['locale' => $locale, 'certificate' => $certificate->id]) }}" class="certificate-btn">
                                <i class="fa-solid fa-file-arrow-down"></i>
                                {{ $certificate->isExternal() ? 'تنزيل الملف' : 'تنزيل PDF' }}
                            </a>
                        @endif
                        @if ($certificate->isExternal() && $certificate->external_verification_url)
                            <a href="{{ $certificate->external_verification_url }}" target="_blank" rel="noopener noreferrer" class="certificate-btn">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> تحقق لدى الجهة
                            </a>
                        @endif
                        <a href="{{ $certificate->verifyUrl() }}" class="certificate-btn">
                            <i class="fa-solid fa-shield-halved"></i> تحقق
                        </a>
                    </footer>
                </article>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
    .certificates-page{display:flex;flex-direction:column;gap:1.1rem}
    .certificates-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:1.25rem;padding:1.35rem 1.45rem;border-radius:18px;background:linear-gradient(135deg,#0f5132 0%,#1b8354 62%,#b8943f 160%);color:#fff;box-shadow:0 14px 32px rgba(15,81,50,.16)}
    .certificates-hero__eyebrow{display:block;margin-bottom:.35rem;font-size:.72rem;font-weight:800;opacity:.8}
    .certificates-hero h1{margin:0 0 .4rem;font-size:clamp(1.3rem,2.4vw,1.9rem);font-weight:900;color:#fff}
    .certificates-hero p{margin:0;max-width:38rem;font-size:.86rem;line-height:1.75;color:rgba(255,255,255,.88)}
    .certificates-hero__cta{display:inline-flex;align-items:center;gap:.45rem;padding:.7rem 1rem;border-radius:12px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);color:#fff;font-size:.8rem;font-weight:800;text-decoration:none;white-space:nowrap}
    .certificates-hero__cta:hover{background:rgba(255,255,255,.22);color:#fff}

    .certificates-kpis{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem;max-width:34rem}
    .certificates-kpi{display:flex;align-items:center;gap:.85rem;padding:1rem 1.1rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff;box-shadow:0 8px 20px rgba(15,23,42,.04)}
    .certificates-kpi__icon{width:2.6rem;height:2.6rem;display:grid;place-items:center;border-radius:12px;font-size:1rem}
    .certificates-kpi__icon--award{background:#fff7ed;color:#c2410c}
    .certificates-kpi__icon--ok{background:#ecfdf5;color:#166534}
    .certificates-kpi strong{display:block;font-size:1.35rem;line-height:1;color:#0f172a}
    .certificates-kpi small{display:block;margin-top:.25rem;color:#64748b;font-size:.72rem;font-weight:700}

    .certificates-list{display:flex;flex-direction:column;gap:1rem}
    .certificate-card{overflow:hidden;border:1px solid #dbe7de;border-radius:18px;background:#fff;box-shadow:0 12px 28px rgba(15,81,50,.06)}
    .certificate-card__head{display:flex;align-items:center;gap:.9rem;padding:1.05rem 1.15rem;background:linear-gradient(180deg,#f7fbf8 0%,#fff 100%);border-bottom:1px solid #eef2f0}
    .certificate-card__mark{flex-shrink:0;width:2.75rem;height:2.75rem;display:grid;place-items:center;border-radius:14px;background:#166534;color:#fff;font-size:1.1rem;box-shadow:0 8px 18px rgba(22,101,52,.25)}
    .certificate-card__title{min-width:0;flex:1}
    .certificate-card__title h2{margin:0 0 .2rem;font-size:1.02rem;font-weight:900;color:#0f5132;line-height:1.4}
    .certificate-card__title p{margin:0;color:#64748b;font-size:.74rem;font-weight:700}
    .certificate-card__status{flex-shrink:0;padding:.32rem .7rem;border-radius:999px;font-size:.7rem;font-weight:800;white-space:nowrap}
    .certificate-card__status.is-valid{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
    .certificate-card__status.is-muted{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}

    .certificate-card__meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;padding:1.05rem 1.15rem}
    .certificate-card__meta>div{display:grid;gap:.22rem;padding:.7rem .8rem;border:1px solid #eef2f6;border-radius:12px;background:#f8fafc}
    .certificate-card__meta span{color:#94a3b8;font-size:.68rem;font-weight:800}
    .certificate-card__meta strong{color:#0f172a;font-size:.84rem;font-weight:800;line-height:1.45;word-break:break-word}

    .certificate-card__note{display:flex;align-items:flex-start;gap:.55rem;margin:0 1.15rem 1rem;padding:.7rem .8rem;border:1px solid #fde68a;border-radius:12px;background:#fffbeb;color:#92400e}
    .certificate-card__note i{margin-top:.15rem;color:#d97706}
    .certificate-card__note p{margin:0;font-size:.76rem;line-height:1.7;font-weight:600}

    .certificate-card__actions{display:flex;flex-wrap:wrap;gap:.5rem;padding:0 1.15rem 1.15rem}
    .certificate-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem .85rem;border-radius:10px;border:1px solid #d1e7d8;background:#fff;color:#145a38;font-size:.76rem;font-weight:800;text-decoration:none;line-height:1}
    .certificate-btn:hover{background:#f0fdf4;color:#14532d}
    .certificate-btn--primary{background:#166534;border-color:#166534;color:#fff}
    .certificate-btn--primary:hover{background:#14532d;color:#fff}

    .certificates-empty{text-align:center;padding:2.8rem 1.25rem;border:1px solid #e2e8f0;border-radius:18px;background:#fff}
    .certificates-empty>i{font-size:2.1rem;color:#94a3b8;margin-bottom:.85rem}
    .certificates-empty h2{margin:0 0 .4rem;font-size:1.05rem;color:#0f172a}
    .certificates-empty p{margin:0 auto;max-width:28rem;color:#64748b;font-size:.84rem;line-height:1.75}

    @media(max-width:720px){
        .certificates-hero{align-items:flex-start;flex-direction:column}
        .certificates-hero__cta{width:100%;justify-content:center}
        .certificates-kpis{max-width:none;grid-template-columns:1fr}
        .certificate-card__head{align-items:flex-start;flex-wrap:wrap}
        .certificate-card__meta{grid-template-columns:1fr}
        .certificate-card__actions .certificate-btn{flex:1 1 calc(50% - .5rem);justify-content:center}
    }
</style>
@endpush

@include('partials.portal.shell-end')
