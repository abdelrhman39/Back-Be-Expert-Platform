@if ($searched)
    <section class="certificate-verification-result {{ $certificate?->isValid() ? 'is-valid' : ($certificate ? 'is-revoked' : 'is-missing') }}">
        @if ($certificate?->isValid())
            <div class="certificate-verification-result__seal">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="certificate-verification-result__head">
                <span>نتيجة التحقق</span>
                @if ($certificate->isExternal())
                    <h2>اعتماد خارجي مسجّل وساري</h2>
                    <p>هذه شهادة صادرة من جهة خارج المنصة، ومسجّلة رسمياً في سجل الاعتمادات بعد التحقق من ملفها الأصلي.</p>
                @else
                    <h2>شهادة أصلية وسارية</h2>
                    <p>تمت مطابقة رمز التحقق مع السجل الرسمي المحفوظ في المنصة.</p>
                @endif
            </div>
            <dl class="certificate-verification-result__details">
                <div><dt>اسم حامل الشهادة</dt><dd>{{ $certificate->holder_name }}</dd></div>
                <div><dt>{{ $certificate->isExternal() ? 'الشهادة أو الاعتماد' : 'البرنامج' }}</dt><dd>{{ $certificate->program_name }}</dd></div>
                @if ($certificate->isExternal())
                    <div><dt>جهة الإصدار</dt><dd>{{ $certificate->external_issuer }}</dd></div>
                    @if ($certificate->external_credential_id)
                        <div><dt>رقم الاعتماد الخارجي</dt><dd dir="ltr">{{ $certificate->external_credential_id }}</dd></div>
                    @endif
                    @if ($certificate->related_program_name)
                        <div><dt>البرنامج المرتبط</dt><dd>{{ $certificate->related_program_name }}</dd></div>
                    @endif
                @endif
                <div><dt>رقم الشهادة</dt><dd dir="ltr">{{ $certificate->code }}</dd></div>
                <div><dt>تاريخ الإصدار</dt><dd>{{ $certificate->issued_at?->translatedFormat('d F Y') }}</dd></div>
                @if ($certificate->program_started_at)
                    <div><dt>تاريخ بدء البرنامج</dt><dd>{{ $certificate->program_started_at->translatedFormat('d F Y') }}</dd></div>
                @endif
                @if ($certificate->program_ended_at)
                    <div><dt>تاريخ انتهاء البرنامج</dt><dd>{{ $certificate->program_ended_at->translatedFormat('d F Y') }}</dd></div>
                @endif
            </dl>
            @if ($certificate->credential_hash)
                <div class="certificate-verification-result__fingerprint">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span>البصمة الرقمية</span>
                    <code dir="ltr">{{ strtoupper(substr($certificate->credential_hash, 0, 8).'…'.substr($certificate->credential_hash, -12)) }}</code>
                </div>
            @endif
        @elseif ($certificate)
            <div class="certificate-verification-result__seal"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="certificate-verification-result__head">
                <span>نتيجة التحقق</span>
                <h2>الشهادة غير سارية</h2>
                <p>
                    @if (! $certificate->hasValidIntegrity())
                        تعذّرت مطابقة البصمة الرقمية للشهادة، لذلك لا ينبغي اعتمادها.
                    @elseif ($certificate->status === 'revoked')
                        تم إلغاء هذه الشهادة ولا ينبغي اعتمادها.
                    @else
                        انتهت صلاحية هذه الشهادة أو تغيّرت حالتها.
                    @endif
                </p>
                @if ($certificate->revocation_reason)
                    <div class="certificate-verification-result__reason">{{ $certificate->revocation_reason }}</div>
                @endif
            </div>
        @else
            <div class="certificate-verification-result__seal"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="certificate-verification-result__head">
                <span>نتيجة التحقق</span>
                <h2>لم يتم العثور على الشهادة</h2>
                <p>تأكد من كتابة الرمز كما يظهر على الشهادة أو امسح رمز QR مرة أخرى.</p>
            </div>
        @endif
    </section>
@endif

@once
    @push('styles')
        <style>
            .certificate-verification-result{position:relative;margin-top:1.25rem;padding:1.2rem;border:1px solid #dbe5df;border-radius:18px;background:#fff;overflow:hidden}.certificate-verification-result::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:#94a3b8}.certificate-verification-result.is-valid::before{background:linear-gradient(90deg,#15803d,#22c55e)}.certificate-verification-result.is-revoked::before{background:#d97706}.certificate-verification-result.is-missing::before{background:#dc2626}.certificate-verification-result__seal{display:grid;place-items:center;width:3rem;height:3rem;margin:0 auto .7rem;border-radius:50%;background:#f1f5f9;color:#64748b;font-size:1.25rem}.is-valid .certificate-verification-result__seal{background:#dcfce7;color:#15803d}.is-revoked .certificate-verification-result__seal{background:#fef3c7;color:#b45309}.is-missing .certificate-verification-result__seal{background:#fee2e2;color:#dc2626}.certificate-verification-result__head{text-align:center}.certificate-verification-result__head>span{color:#94a3b8;font-size:.64rem;font-weight:900}.certificate-verification-result__head h2{margin:.2rem 0 .35rem;color:#17251f;font-size:1rem;font-weight:900}.is-valid .certificate-verification-result__head h2{color:#166534}.certificate-verification-result__head p{margin:0;color:#64748b;font-size:.7rem;line-height:1.8}.certificate-verification-result__details{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin:1rem 0 0}.certificate-verification-result__details div{padding:.65rem;border:1px solid #e8eeea;border-radius:10px;background:#f8fbf9}.certificate-verification-result__details dt{color:#94a3b8;font-size:.58rem}.certificate-verification-result__details dd{margin:.16rem 0 0;color:#334155;font-size:.7rem;font-weight:800}.certificate-verification-result__fingerprint{display:flex;align-items:center;gap:.45rem;margin-top:.7rem;padding:.6rem .7rem;border-radius:10px;background:#f0fdf4;color:#166534;font-size:.6rem}.certificate-verification-result__fingerprint code{margin-inline-start:auto;color:#166534;font-size:.55rem}.certificate-verification-result__reason{margin-top:.7rem;padding:.6rem;border-radius:9px;background:#fffbeb;color:#92400e;font-size:.68rem}@media(max-width:520px){.certificate-verification-result__details{grid-template-columns:1fr}}
        </style>
    @endpush
@endonce
