@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $steps = $isEn
        ? [
            ['art' => 'quality', 'title' => 'Content quality', 'text' => 'Each program is reviewed before it is published.'],
            ['art' => 'attendance', 'title' => 'Attendance', 'text' => 'Hours are tracked until the requirement is complete.'],
            ['art' => 'exam', 'title' => 'Assessment', 'text' => 'A test follows the learning content.'],
            ['art' => 'certificate', 'title' => 'Digital certificate', 'text' => 'A certificate that can be verified online.'],
            ['art' => 'partners', 'title' => 'Partner registration', 'text' => 'Programs delivered with training partners.'],
            ['art' => 'payment', 'title' => 'Payment', 'text' => 'Online payment before the seat is confirmed.'],
        ]
        : [
            ['art' => 'quality', 'title' => 'جودة المحتوى', 'text' => 'مراجعة البرنامج قبل نشره للمتدربين.'],
            ['art' => 'attendance', 'title' => 'متابعة الحضور', 'text' => 'رصد الساعات حتى اكتمال المتطلب.'],
            ['art' => 'exam', 'title' => 'الاختبار', 'text' => 'تقييم بعد إنهاء المحتوى التدريبي.'],
            ['art' => 'certificate', 'title' => 'شهادة رقمية', 'text' => 'شهادة يمكن التحقق منها إلكترونياً.'],
            ['art' => 'partners', 'title' => 'تسجيل الشركاء', 'text' => 'برامج تُقدَّم مع جهات التدريب.'],
            ['art' => 'payment', 'title' => 'الدفع', 'text' => 'سداد إلكتروني قبل تأكيد المقعد.'],
        ];
@endphp

<section class="tu-accredit" aria-label="{{ $isEn ? 'Accreditations' : 'الاعتمادات' }}">
    <div class="container">
        <header class="tu-accredit__head">
            <h2>{{ $isEn ? 'Accreditations' : 'الاعتمادات' }}</h2>
            <p>{{ $isEn ? 'Six steps from reviewing the program to issuing a certificate you can verify.' : 'ست خطوات من مراجعة البرنامج حتى إصدار شهادة يمكن التحقق منها.' }}</p>
        </header>
        <ol class="tu-accredit__grid">
            @foreach ($steps as $step)
                <li class="tu-accredit__card">
                    <span class="tu-accredit__num">{{ $loop->iteration }}</span>
                    <span class="tu-accredit__art">
                        @include('partials.catalog.accreditation-icon', ['name' => $step['art']])
                    </span>
                    <strong>{{ $step['title'] }}</strong>
                    <span>{{ $step['text'] }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</section>
