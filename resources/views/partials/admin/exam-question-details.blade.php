@php
    $answerKey = $question->answer_key ?? [];
    $correctKeys = collect((array) ($answerKey['correct'] ?? []))->map(fn ($key) => (string) $key);
    $orderedKeys = collect($answerKey['order'] ?? []);
    $optionsByKey = $question->options->keyBy('option_key');
@endphp

<div class="admin-exam-detail-grid">
    <section class="admin-exam-detail-block admin-exam-detail-block--prompt">
        <span class="admin-exam-detail-label"><i class="fa-regular fa-circle-question"></i> نص السؤال الكامل</span>
        <div class="admin-exam-detail-prompt">{!! nl2br(e($question->prompt)) !!}</div>
    </section>

    @if (in_array($question->type, ['single_choice', 'multiple_choice', 'true_false'], true))
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-list-check"></i> الخيارات والإجابة الصحيحة</span>
            <div class="admin-exam-detail-options">
                @foreach ($question->options as $option)
                    @php($isCorrect = $option->is_correct || $correctKeys->contains((string) $option->option_key))
                    <div @class(['is-correct' => $isCorrect])>
                        <span class="admin-exam-detail-option-key">{{ $option->option_key }}</span>
                        <span>{{ $option->content }}</span>
                        @if ($isCorrect)<strong><i class="fa-solid fa-circle-check"></i> صحيحة</strong>@endif
                    </div>
                @endforeach
            </div>
        </section>
    @elseif ($question->type === 'short_text')
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-spell-check"></i> الإجابات المقبولة</span>
            <div class="admin-exam-detail-tags">
                @foreach (($answerKey['accepted'] ?? []) as $accepted)<span>{{ $accepted }}</span>@endforeach
            </div>
        </section>
    @elseif ($question->type === 'fill_blank')
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-ellipsis"></i> إجابات الفراغات</span>
            <div class="admin-exam-detail-structured">
                @foreach (($answerKey['blanks'] ?? []) as $index => $accepted)
                    <div><strong>الفراغ {{ $index + 1 }}</strong><span>{{ implode(' / ', (array) $accepted) }}</span></div>
                @endforeach
            </div>
        </section>
    @elseif ($question->type === 'matching')
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-code-compare"></i> أزواج المطابقة</span>
            <div class="admin-exam-detail-structured">
                @foreach ($question->options as $option)
                    <div><strong>{{ $option->content }}</strong><i class="fa-solid fa-arrow-left"></i><span>{{ $option->match_data['target'] ?? '—' }}</span></div>
                @endforeach
            </div>
        </section>
    @elseif ($question->type === 'ordering')
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-arrow-down-1-9"></i> الترتيب الصحيح</span>
            <ol class="admin-exam-detail-order">
                @foreach ($orderedKeys as $optionKey)
                    <li>{{ $optionsByKey->get($optionKey)?->content ?? $optionKey }}</li>
                @endforeach
            </ol>
        </section>
    @elseif ($question->type === 'numeric')
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-calculator"></i> الإجابة الرقمية</span>
            <div class="admin-exam-detail-numeric">
                <strong>{{ $answerKey['value'] ?? '—' }}</strong>
                <span>هامش الخطأ المسموح: ± {{ $answerKey['tolerance'] ?? 0 }}</span>
            </div>
        </section>
    @else
        <section class="admin-exam-detail-block">
            <span class="admin-exam-detail-label"><i class="fa-solid fa-user-pen"></i> طريقة التصحيح</span>
            <div class="admin-exam-detail-manual">يحتاج هذا السؤال إلى تصحيح يدوي من المدرب أو الإدارة.</div>
        </section>
    @endif

    @if ($question->explanation)
        <section class="admin-exam-detail-block admin-exam-detail-block--explanation">
            <span class="admin-exam-detail-label"><i class="fa-regular fa-lightbulb"></i> شرح الإجابة</span>
            <p>{!! nl2br(e($question->explanation)) !!}</p>
        </section>
    @endif

    <footer class="admin-exam-detail-footer">
        <span><i class="fa-solid fa-layer-group"></i> الإصدار {{ $question->version }}</span>
        <span><i class="fa-solid fa-star"></i> {{ $question->pivot->points }} درجة</span>
        <span><i class="fa-solid fa-gauge-high"></i> {{ \App\Support\ExamOptions::difficulties()[$question->difficulty] ?? $question->difficulty }}</span>
    </footer>
</div>
