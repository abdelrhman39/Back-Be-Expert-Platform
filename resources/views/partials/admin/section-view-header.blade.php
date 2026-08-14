@php
    use App\Support\AcademicSectionOptions;

    $statusClass = $section->status === 'active'
        ? 'admin-badge--success'
        : 'admin-badge--danger';

    $fillPercent = $section->fillPercent();
@endphp

<div class="admin-batch-view-head">
    <div class="admin-batch-view-head__main">
        <div class="admin-batch-view-head__title-row">
            <h1 class="admin-batch-view-title">{{ $section->name }}</h1>
            <span @class(['admin-badge', $statusClass])>{{ AcademicSectionOptions::statusLabel($section->status) }}</span>
        </div>

        <p class="admin-batch-view-meta">
            <code class="admin-code">{{ $section->code }}</code>
            <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
            <span>{{ $section->displaySemester() }}</span>
            @if ($section->course)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <a href="{{ route('admin.academic-courses.show', $section->course) }}" class="admin-link">{{ $section->course->name_ar }}</a>
            @elseif ($section->subtitle)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <span>{{ $section->subtitle }}</span>
            @endif
        </p>

        <ul class="admin-batch-view-facts">
            @if ($section->batch)
                <li>
                    <span class="admin-batch-view-facts__label">الدفعة</span>
                    <a href="{{ route('admin.batches.show', $section->batch) }}" class="admin-link">{{ $section->batch->code }}</a>
                </li>
            @endif
            @if ($section->program)
                <li>
                    <span class="admin-batch-view-facts__label">البرنامج</span>
                    <a href="{{ route('admin.programs.show', $section->program) }}" class="admin-link">{{ $section->program->name_ar }}</a>
                </li>
            @endif
            @if ($section->level)
                <li>
                    <span class="admin-batch-view-facts__label">المستوى</span>
                    <span class="admin-batch-view-facts__value">{{ $section->level->name_ar }}</span>
                </li>
            @endif
            @if ($section->period)
                <li>
                    <span class="admin-batch-view-facts__label">الفترة</span>
                    <span class="admin-tag">{{ AcademicSectionOptions::periodLabel($section->period) }}</span>
                </li>
            @endif
            @if ($section->supervisor)
                <li>
                    <span class="admin-batch-view-facts__label">مشرف الشعبة</span>
                    <span class="admin-batch-view-facts__value">{{ $section->supervisor }}</span>
                </li>
            @endif
            @if ($section->trainerName() !== '—')
                <li>
                    <span class="admin-batch-view-facts__label">عضو هيئة التدريس</span>
                    <span class="admin-batch-view-facts__value">{{ $section->trainerName() }}</span>
                </li>
            @endif
        </ul>
    </div>

    @if ($fillPercent !== null)
        <div class="admin-batch-view-capacity" aria-label="نسبة امتلاء الشعبة">
            <div class="admin-batch-view-capacity__head">
                <span class="admin-batch-view-capacity__title">الإشغال</span>
                <span class="admin-batch-view-capacity__count" dir="ltr">
                    {{ $section->students_count }} / {{ $section->max_capacity }}
                </span>
            </div>
            <div class="admin-batch-view-capacity__bar" role="progressbar" aria-valuenow="{{ $fillPercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $fillPercent }}% من السعة">
                <span style="width: {{ $fillPercent }}%"></span>
            </div>
            <div class="admin-batch-view-capacity__foot">
                <span class="admin-batch-view-capacity__pct">{{ $fillPercent }}%</span>
                @if ($section->availableSeats() !== null)
                    <span class="admin-batch-view-capacity__seats">{{ $section->availableSeats() }} مقعد متاح</span>
                @endif
            </div>
        </div>
    @endif
</div>
