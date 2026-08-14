@php
    use App\Support\AcademicProgramOptions;

    $statusClass = match ($program->status) {
        'active' => 'admin-badge--success',
        'inactive' => 'admin-badge--danger',
        default => 'admin-badge--warn',
    };
@endphp

<div class="admin-batch-view-head">
    <div class="admin-batch-view-head__main">
        <div class="admin-batch-view-head__title-row">
            <h1 class="admin-batch-view-title">{{ $program->name_ar }}</h1>
            <span @class(['admin-badge', $statusClass])>{{ AcademicProgramOptions::statusLabel($program->status) }}</span>
        </div>

        <p class="admin-batch-view-meta">
            <code class="admin-code">{{ $program->code }}</code>
            @if ($program->symbol)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <span>{{ $program->symbol }}</span>
            @endif
            <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
            <span>{{ AcademicProgramOptions::typeLabel($program->type) }}</span>
            @if ($program->name_en)
                <span class="admin-batch-view-meta__sep" aria-hidden="true">·</span>
                <span dir="ltr">{{ $program->name_en }}</span>
            @endif
        </p>

        <ul class="admin-batch-view-facts">
            <li>
                <span class="admin-batch-view-facts__label">المدة</span>
                <span class="admin-tag">{{ $program->displayDuration() }}</span>
            </li>
            @if ($program->coordinator)
                <li>
                    <span class="admin-batch-view-facts__label">المنسق</span>
                    <span class="admin-batch-view-facts__value">{{ $program->coordinator }}</span>
                </li>
            @endif
            @if ($program->city)
                <li>
                    <span class="admin-batch-view-facts__label">المدينة</span>
                    <span class="admin-batch-view-facts__value">{{ $program->city }}</span>
                </li>
            @endif
            @if ($program->start_date)
                <li>
                    <span class="admin-batch-view-facts__label">تاريخ البدء</span>
                    <span class="admin-batch-view-facts__value" dir="ltr">{{ $program->start_date->format('Y-m-d') }}</span>
                </li>
            @endif
            @if ($program->study_status)
                <li>
                    <span class="admin-batch-view-facts__label">حالة الدراسة</span>
                    <span class="admin-batch-view-facts__value">{{ $program->study_status }}</span>
                </li>
            @endif
        </ul>
    </div>

    <div class="admin-batch-view-capacity admin-program-view-summary" aria-label="ملخص البرنامج">
        <div class="admin-batch-view-capacity__head">
            <span class="admin-batch-view-capacity__title">الملخص</span>
        </div>
        <ul class="admin-program-view-summary__list">
            <li><span>مستويات</span><strong>{{ $stats['levels'] ?? $program->levels->count() }}</strong></li>
            <li><span>مقررات</span><strong>{{ $stats['courses'] ?? $program->courses->count() }}</strong></li>
            <li><span>دفعات</span><strong>{{ $stats['batches'] ?? $program->batches->count() }}</strong></li>
            <li><span>ساعات</span><strong dir="ltr">{{ $stats['hours'] ?? (int) $program->courses->sum('credit_hours') }}</strong></li>
        </ul>
    </div>
</div>
