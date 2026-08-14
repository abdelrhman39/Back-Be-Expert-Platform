<?php

use App\Models\Certificate;
use App\Services\CertificateService;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
class extends Component
{
    public Certificate $certificate;

    public function mount(Certificate $certificate): void
    {
        abort_unless(CertificateAccessSettings::portalEnabled(), 404);

        $found = app(CertificateService::class)->findForUser(auth()->user(), $certificate->id);

        abort_unless($found, 404);

        $this->certificate = $found;
    }

    public function layoutData(): array
    {
        return [];
    }
};
?>

@php
    $locale = app()->getLocale();
    $cert = $this->certificate;
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'certificates', 'portalTitle' => 'شهادة — '.$cert->program_name])

<div class="portal-dashboard portal-certificate-view">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $cert->program_name }}</h1>
            <p class="portal-orders-intro__desc">رمز التحقق: <code dir="ltr">{{ $cert->code }}</code></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if (CertificateAccessPolicy::canDownload($cert))
                <a href="{{ route('certificates.download', ['locale' => $locale, 'certificate' => $cert]) }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-file-arrow-down"></i> {{ $cert->isExternal() ? 'تنزيل الملف الأصلي' : 'تنزيل PDF' }}
                </a>
            @endif
            @if ($cert->isExternal() && $cert->external_verification_url)
                <a href="{{ $cert->external_verification_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-arrow-up-right-from-square"></i> تحقق لدى جهة الاعتماد</a>
            @endif
            <a href="{{ route('certificates', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">العودة</a>
        </div>
    </div>

    @if ($cert->student_note)
        <div class="portal-certificate-note"><i class="fa-solid fa-message"></i><div><strong>رسالة من الإدارة</strong><p>{{ $cert->student_note }}</p></div></div>
    @endif

    @if (CertificateAccessPolicy::showDetails($cert))
        <section class="portal-certificate-details">
            @if ($cert->isExternal())
                <div><span>جهة الإصدار</span><strong>{{ $cert->external_issuer }}</strong></div>
                <div><span>رقم الاعتماد</span><strong dir="ltr">{{ $cert->external_credential_id ?: '—' }}</strong></div>
                <div><span>البرنامج المرتبط</span><strong>{{ $cert->related_program_name ?: '—' }}</strong></div>
            @endif
            <div><span>تاريخ الإصدار</span><strong>{{ $cert->issued_at->translatedFormat('d M Y') }}</strong></div>
            @if (! $cert->isExternal())
                <div><span>فترة البرنامج</span><strong dir="ltr">{{ $cert->program_started_at?->format('Y-m-d') ?? '—' }} → {{ $cert->program_ended_at?->format('Y-m-d') ?? '—' }}</strong></div>
            @endif
            <div><span>حالة الشهادة</span><strong>{{ $cert->isValid() ? 'سارية وموثقة' : 'غير سارية' }}</strong></div>
            <div><span>سلامة البيانات</span><strong>{{ $cert->hasValidIntegrity() ? 'مطابقة للبصمة الرقمية' : 'تحتاج إلى مراجعة' }}</strong></div>
        </section>
    @endif

    @if ($cert->isExternal())
        <section class="portal-external-certificate">
            <header><i class="fa-solid fa-building-columns"></i><div><strong>النسخة الأصلية من جهة الاعتماد</strong><small>{{ $cert->external_file_name }}</small></div></header>
            @if (str_starts_with((string) $cert->external_file_mime, 'image/'))
                <img src="{{ route('certificates.download', ['locale' => $locale, 'certificate' => $cert, 'inline' => 1]) }}" alt="{{ $cert->program_name }}">
            @elseif ($cert->external_file_mime === 'application/pdf')
                <iframe src="{{ route('certificates.download', ['locale' => $locale, 'certificate' => $cert, 'inline' => 1]) }}" title="{{ $cert->program_name }}"></iframe>
            @else
                <div class="portal-empty"><p>لا تتوفر معاينة لهذا النوع من الملفات.</p></div>
            @endif
        </section>
    @else
        @include('partials.certificates.dynamic', [
            'certificate' => $cert,
            'render' => app(\App\Services\CertificateRenderService::class)->renderData($cert),
            'forPdf' => false,
        ])
    @endif
</div>

@include('partials.portal.shell-end')

@push('styles')
<style>
    @media print {
        .header, .new-sidebar, .dashboard-header, .portal-orders-intro, .portal-bg-pattern { display: none !important; }
        .new-design-content, .portal-page-content { padding: 0 !important; max-width: 100% !important; }
        .portal-print-certificate { box-shadow: none !important; border: 2px solid #165d31 !important; }
    }
    .portal-certificates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
    .portal-cert-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.1rem; display: flex; flex-direction: column; gap: 0.75rem; }
    .portal-cert-card__badge { width: 2.5rem; height: 2.5rem; border-radius: 12px; background: #ecfdf5; color: #165d31; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .portal-cert-card__body h3 { margin: 0 0 0.5rem; font-size: 1rem; font-weight: 800; }
    .portal-cert-card__body ul { list-style: none; margin: 0; padding: 0; font-size: 0.84rem; }
    .portal-cert-card__body li { display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.25rem 0; border-bottom: 1px dashed #f1f5f9; }
    .portal-cert-card__body li span:first-child { color: #64748b; }
    .portal-cert-card__actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: auto; }
    .portal-badge { display: inline-block; padding: 0.15rem 0.45rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
    .portal-badge--success { background: #dcfce7; color: #166534; }
    .portal-badge--muted { background: #f1f5f9; color: #64748b; }
    .portal-certificate-note{display:flex;align-items:flex-start;gap:.6rem;margin-bottom:1rem;padding:.75rem .85rem;border:1px solid #fde68a;border-radius:12px;background:#fffbeb;color:#92400e}.portal-certificate-note>i{margin-top:.2rem}.portal-certificate-note strong{font-size:.76rem}.portal-certificate-note p{margin:.15rem 0 0;font-size:.72rem;line-height:1.7}
    .portal-certificate-details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.65rem;margin-bottom:1rem}.portal-certificate-details>div{display:grid;gap:.2rem;padding:.7rem .8rem;border:1px solid #e2e8f0;border-radius:11px;background:#fff}.portal-certificate-details span{color:#64748b;font-size:.66rem}.portal-certificate-details strong{color:#17251f;font-size:.73rem}@media(max-width:800px){.portal-certificate-details{grid-template-columns:1fr 1fr}}
    .portal-external-certificate{overflow:hidden;border:1px solid #e2e8f0;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06)}.portal-external-certificate>header{display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-bottom:1px solid #e2e8f0;background:#faf5ff;color:#7c3aed}.portal-external-certificate>header>i{font-size:1rem}.portal-external-certificate>header strong,.portal-external-certificate>header small{display:block}.portal-external-certificate>header strong{font-size:.72rem}.portal-external-certificate>header small{color:#8b5cf6;font-size:.56rem}.portal-external-certificate img{display:block;width:100%;height:auto;max-height:75vh;object-fit:contain;background:#f8fafc}.portal-external-certificate iframe{display:block;width:100%;height:75vh;border:0;background:#f8fafc}
</style>
@endpush
