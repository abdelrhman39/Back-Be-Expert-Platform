@php
    use App\Models\PlatformSetting;

    $platformName = PlatformSetting::get('platform_name_ar', config('app.name')) ?? config('app.name');
@endphp

<div class="portal-print-certificate">
    <div class="portal-print-certificate__frame">
        <div class="portal-print-certificate__header">
            <div class="portal-print-certificate__logo">{{ $platformName }}</div>
            <p class="portal-print-certificate__subtitle">شهادة إتمام برنامج</p>
        </div>

        <div class="portal-print-certificate__body">
            <p class="portal-print-certificate__lead">يشهد {{ $platformName }} بأن</p>
            <h2 class="portal-print-certificate__name">{{ $certificate->holder_name }}</h2>
            <p class="portal-print-certificate__program">قد أتم بنجاح متطلبات البرنامج:</p>
            <h3 class="portal-print-certificate__program-name">{{ $certificate->program_name }}</h3>
        </div>

        <div class="portal-print-certificate__footer">
            <div>
                <span class="label">تاريخ الإصدار</span>
                <strong>{{ $certificate->issued_at->translatedFormat('d F Y') }}</strong>
            </div>
            <div>
                <span class="label">رمز التحقق</span>
                <strong dir="ltr">{{ $certificate->code }}</strong>
            </div>
            <div>
                <span class="label">الحالة</span>
                <strong>{{ $certificate->isValid() ? 'سارية' : $certificate->status }}</strong>
            </div>
        </div>

        <p class="portal-print-certificate__verify">للتحقق: {{ $certificate->verifyUrl() }}</p>
    </div>
</div>

<style>
    .portal-print-certificate { max-width: 820px; margin: 0 auto; }
    .portal-print-certificate__frame {
        border: 3px double #165d31;
        border-radius: 4px;
        padding: 2.5rem 2rem;
        background: linear-gradient(180deg, #fff 0%, #fafffb 100%);
        box-shadow: 0 12px 40px rgba(22, 93, 49, 0.1);
        text-align: center;
    }
    .portal-print-certificate__logo { font-size: 1.35rem; font-weight: 900; color: #165d31; }
    .portal-print-certificate__subtitle { margin: 0.35rem 0 1.5rem; color: #64748b; font-size: 0.95rem; }
    .portal-print-certificate__lead { margin: 0; color: #475569; }
    .portal-print-certificate__name { margin: 0.5rem 0 1rem; font-size: 1.75rem; font-weight: 900; color: #1a1a1a; }
    .portal-print-certificate__program { margin: 0; color: #64748b; }
    .portal-print-certificate__program-name { margin: 0.35rem 0 1.5rem; font-size: 1.2rem; font-weight: 800; color: #165d31; }
    .portal-print-certificate__footer {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;
        border-top: 1px solid #e2e8f0; padding-top: 1rem; margin-top: 1rem; text-align: center;
    }
    .portal-print-certificate__footer .label { display: block; font-size: 0.72rem; color: #94a3b8; margin-bottom: 0.15rem; }
    .portal-print-certificate__verify { margin: 1rem 0 0; font-size: 0.72rem; color: #94a3b8; word-break: break-all; }
    @media (max-width: 640px) {
        .portal-print-certificate__footer { grid-template-columns: 1fr; }
    }
</style>
