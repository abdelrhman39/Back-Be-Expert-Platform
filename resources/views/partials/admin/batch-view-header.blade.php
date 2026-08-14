@php
    use App\Support\AcademicBatchOptions;

    $statusClass = match ($batch->status) {
        'active' => 'admin-badge--success',
        'planned' => 'admin-badge--warn',
        'closed' => 'admin-badge--danger',
        default => '',
    };

    $fillPercent = ($batch->capacity && $batch->capacity > 0)
        ? min(100, round(($batch->students_count / $batch->capacity) * 100))
        : null;
@endphp

<div class="admin-batch-view-head">
    <div class="admin-batch-view-head__main">
        <div class="admin-batch-view-head__title-row">
            <h1 class="admin-batch-view-title">{{ $batch->name }}</h1>
            <span @class(['admin-badge', $statusClass])>{{ AcademicBatchOptions::statusLabel($batch->status) }}</span>
        </div>

        <p class="admin-batch-view-meta">
            <code class="admin-code">{{ $batch->code }}</code>
            <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
            <span>{{ $batch->displaySemester() }}</span>
            @if ($batch->program)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <a href="{{ route('admin.programs.show', $batch->program) }}" class="admin-link">{{ $batch->program->name_ar }}</a>
            @endif
        </p>

        <ul class="admin-batch-view-facts">
            @if ($batch->study_mode)
                <li>
                    <span class="admin-batch-view-facts__label">نمط الدراسة</span>
                    <span class="admin-tag">{{ AcademicBatchOptions::studyModeLabel($batch->study_mode) }}</span>
                </li>
            @endif
            <li>
                <span class="admin-batch-view-facts__label">التسجيل</span>
                @if ($batch->enrollment_open)
                    <span class="admin-badge admin-badge--success">مفتوح</span>
                @else
                    <span class="admin-badge admin-badge--danger">مغلق</span>
                @endif
            </li>
            @if ($batch->coordinator)
                <li>
                    <span class="admin-batch-view-facts__label">المنسق</span>
                    <span class="admin-batch-view-facts__value">{{ $batch->coordinator }}</span>
                </li>
            @endif
            @if ($batch->start_date || $batch->end_date)
                <li>
                    <span class="admin-batch-view-facts__label">الفترة</span>
                    <span class="admin-batch-view-facts__value" dir="ltr">
                        {{ $batch->start_date?->format('Y-m-d') ?? '—' }}
                        —
                        {{ $batch->end_date?->format('Y-m-d') ?? '—' }}
                    </span>
                </li>
            @endif
        </ul>
    </div>

    @if ($fillPercent !== null)
        <div class="admin-batch-view-capacity" aria-label="نسبة امتلاء الدفعة">
            <div class="admin-batch-view-capacity__head">
                <span class="admin-batch-view-capacity__title">الإشغال</span>
                <span class="admin-batch-view-capacity__count" dir="ltr">
                    {{ $batch->students_count }} / {{ $batch->capacity }}
                </span>
            </div>
            <div class="admin-batch-view-capacity__bar" role="progressbar" aria-valuenow="{{ $fillPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $fillPercent }}% من السعة">
                <span style="width: {{ $fillPercent }}%"></span>
            </div>
            <div class="admin-batch-view-capacity__foot">
                <span class="admin-batch-view-capacity__pct">{{ $fillPercent }}%</span>
                @if ($batch->availableSeats() !== null)
                    <span class="admin-batch-view-capacity__seats">{{ $batch->availableSeats() }} مقعد متاح</span>
                @endif
            </div>
        </div>
    @endif
</div>
