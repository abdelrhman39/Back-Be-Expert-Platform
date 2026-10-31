@php
    $locale = app()->getLocale();
    $isEn = $locale === 'en';
    $pillars = \App\Support\TaibahCopy::institutePillars($isEn);
    $programs = \App\Support\TaibahCopy::programs($isEn);
    $branches = \App\Support\TaibahCopy::branches($isEn);
@endphp

<section class="tu-college" aria-label="{{ $isEn ? 'Applied College' : 'الكلية التطبيقية' }}">
    <div class="container">
        <div class="tu-college__panel">
            <div class="tu-college__copy">
                <p class="tu-kicker">{{ $isEn ? 'Taibah University' : 'جامعة طيبة' }}</p>
                <h2>{{ $isEn ? 'About the Applied College' : 'نبذة عن الكلية التطبيقية' }}</h2>
                <p>{{ $isEn
                    ? 'The college offers applied diploma programs and flexible training courses that stay aligned with current and future labor-market needs, regionally and nationally.'
                    : 'تقدم الكلية برامج تطبيقية بدرجة الدبلوم المتوسط ودورات تدريبية مرنة، متوائمة مع متطلبات سوق العمل الحالية والمستقبلية على المستوى المناطقي والوطني.' }}</p>
                <div class="tu-college__stats">
                    <div>
                        <strong>{{ count($programs) }}</strong>
                        <span>{{ $isEn ? 'Diploma programs' : 'برامج دبلوم' }}</span>
                    </div>
                    <div>
                        <strong>{{ count($branches) }}</strong>
                        <span>{{ $isEn ? 'Branches' : 'فروع' }}</span>
                    </div>
                </div>
                <div class="tu-college__branches">
                    <span>{{ $isEn ? 'Branches' : 'الفروع' }}</span>
                    <ul>
                        @foreach ($branches as $branch)
                            <li>{{ $branch }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <ul class="tu-college__programs">
                @foreach ($programs as $program)
                    <li>
                        <span class="tu-college__num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>{{ $program }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="tu-institute" aria-label="{{ $isEn ? 'Institute of Research and Consulting' : 'معهد البحوث والاستشارات' }}">
    <div class="container">
        <header class="tu-institute__head">
            <p class="tu-kicker">{{ $isEn ? 'The university expert house' : 'بيت الخبرة في الجامعة' }}</p>
            <h2>{{ $isEn ? 'Institute of Research and Consulting' : 'معهد البحوث والاستشارات' }}</h2>
            <p class="tu-institute__lead">{{ $isEn
                ? 'The institute links Taibah University with public and private organizations. For more than twenty years it has put the university’s specialists to work on research, consulting, and institutional development.'
                : 'يعد المعهد حلقة الوصل بين جامعة طيبة وقطاعات المجتمع الحكومية والخاصة. يسخّر كفاءات الجامعة في البحث والاستشارة وتطوير القدرات المؤسسية، بخبرة تمتد لأكثر من عشرين عاماً.' }}</p>
        </header>
        <div class="tu-institute__grid">
            @foreach ($pillars as $pillar)
                <article class="tu-institute__card">
                    <span class="tu-institute__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3>{{ $pillar['title'] }}</h3>
                    <p>{{ $pillar['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
