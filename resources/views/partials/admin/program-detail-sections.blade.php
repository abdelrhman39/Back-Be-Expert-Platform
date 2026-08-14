@php
    use App\Support\AcademicProgramOptions;

    $statusBadge = match ($program->status) {
        'active' => '<span class="admin-badge admin-badge--success">فعال</span>',
        'inactive' => '<span class="admin-badge admin-badge--danger">غير فعال</span>',
        default => '<span class="admin-badge">'.e(AcademicProgramOptions::statusLabel($program->status)).'</span>',
    };
@endphp

<div class="admin-detail-grid admin-detail-grid--sections">
    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
            </span>
            المعلومات الأساسية
        </h3>
        <div class="admin-detail-fields admin-detail-fields--3">
            @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'اسم البرنامج', 'value' => $program->name_ar])
            @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'كود البرنامج', 'value' => '<code class="admin-code">'.e($program->code).'</code>'])
            @include('partials.admin.detail-field', ['icon' => 'cert', 'label' => 'اسم البرنامج في الإفادة', 'value' => $program->name_on_certificate ?: $program->name_en ?: '—'])
            @include('partials.admin.detail-field', ['icon' => 'tag', 'label' => 'رمز البرنامج', 'value' => $program->symbol ?: '—'])
            @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'مدة البرنامج', 'value' => $program->displayDuration()])
            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'تاريخ بدء البرنامج', 'value' => $program->start_date?->format('Y-m-d') ?? '—'])
            @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'حالة البرنامج', 'value' => $statusBadge, 'tone' => 'success'])
            @include('partials.admin.detail-field', ['icon' => 'layers', 'label' => 'نوع البرنامج', 'value' => AcademicProgramOptions::typeLabel($program->type)])
        </div>
    </section>

    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
            </span>
            المنسق والتواصل
        </h3>
        <div class="admin-detail-fields admin-detail-fields--2">
            @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'منسق البرنامج', 'value' => $program->coordinator ?: '—'])
            @include('partials.admin.detail-field', ['icon' => 'mail', 'label' => 'البريد الإلكتروني', 'value' => $program->email ? '<a href="mailto:'.e($program->email).'" class="admin-link">'.e($program->email).'</a>' : '—'])
            @include('partials.admin.detail-field', ['icon' => 'phone', 'label' => 'رقم التواصل', 'value' => $program->phone ?: '—'])
            @include('partials.admin.detail-field', ['icon' => 'pin', 'label' => 'المدينة', 'value' => $program->city ?: '—'])
        </div>
    </section>

    <section class="admin-detail-section admin-detail-section--wide">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 6-8"/></svg>
            </span>
            عن البرنامج والمهارات
        </h3>
        @if ($program->summary)
            <p class="admin-detail-text">{{ $program->summary }}</p>
        @else
            <p class="admin-detail-empty">لا توجد نبذة عن البرنامج.</p>
        @endif
        @if ($program->skills)
            <ul class="admin-detail-list">
                @foreach ($program->skills as $skill)
                    <li class="admin-detail-list__item">
                        <span class="admin-detail-list__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                        </span>
                        {{ $skill }}
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </span>
            الحالة الدراسية
        </h3>
        <div class="admin-detail-fields admin-detail-fields--1">
            @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'الوضع الحالي', 'value' => $program->study_status ?: '—', 'tone' => 'info'])
        </div>
    </section>

    @if ($program->media_url)
        <section class="admin-detail-section">
            <h3 class="admin-detail-section__title">
                <span class="admin-detail-section__title-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>
                </span>
                الوسائط والروابط
            </h3>
            <div class="admin-media-block">
                <div class="admin-media-block__thumb" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>
                </div>
                <div class="admin-media-block__body">
                    @include('partials.admin.detail-field', ['icon' => 'link', 'label' => 'رابط البرنامج التعريفي', 'value' => '<a href="'.e($program->media_url).'" class="admin-link" target="_blank" rel="noopener">فتح الرابط</a>'])
                </div>
            </div>
        </section>
    @endif

    <section class="admin-detail-section">
        <h3 class="admin-detail-section__title">
            <span class="admin-detail-section__title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
            </span>
            المرفقات
        </h3>
        <ul class="admin-attach-list">
            @forelse ($program->attachments ?? [] as $file)
                <li class="admin-attach-item">
                    <span class="admin-attach-item__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
                    </span>
                    @if (! empty($file['url']))
                        <a href="{{ $file['url'] }}" class="admin-link" target="_blank" rel="noopener">{{ $file['name'] }}</a>
                    @else
                        <span>{{ $file['name'] }}</span>
                    @endif
                </li>
            @empty
                <li class="admin-detail-empty">لا توجد مرفقات</li>
            @endforelse
        </ul>
    </section>
</div>

@once
    @push('styles')
    <style>
        .admin-detail-grid--sections { grid-template-columns: 1fr; }
        @media (min-width: 1100px) {
            .admin-detail-grid--sections { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    @endpush
@endonce
