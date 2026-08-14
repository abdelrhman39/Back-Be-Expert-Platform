@php
    use App\Support\AcademicBatchOptions;
    use App\Support\AcademicProgramOptions;
    use App\Support\AcademicScheduleOptions;
    use App\Support\AcademicSectionOptions;
    use App\Support\AcademicStudentOptions;

    $program = $student->batch?->program;
    $batch = $student->batch;
    $section = $student->section;
    $schedule = $section?->schedule;
@endphp

<div class="student-academic-tab">
    <div class="student-academic-stats">
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $academicSummary['courses_count'] }}</span>
            <span class="student-academic-stat__label">مقررات البرنامج</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $academicSummary['total_hours'] }}</span>
            <span class="student-academic-stat__label">إجمالي الساعات</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $academicSummary['levels_count'] }}</span>
            <span class="student-academic-stat__label">المستويات</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">@include('partials.admin.student-status-badge', ['status' => $student->academic_status])</span>
            <span class="student-academic-stat__label">الحالة الأكاديمية</span>
        </div>
    </div>

    <div class="student-profile-board student-profile-board--academic">
        <section class="student-profile-card student-profile-card--academic">
            <header class="student-profile-card__head">
                <span class="student-profile-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
                </span>
                <h2 class="student-profile-card__title">البرنامج الدراسي</h2>
            </header>
            <div class="student-profile-card__body">
                @if ($program)
                    <div class="admin-detail-grid admin-detail-grid--sections">
                        <section class="admin-detail-section">
                            <div class="admin-detail-fields admin-detail-fields--2">
                                @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'اسم البرنامج', 'value' => '<a href="'.route('admin.programs.show', $program).'" class="admin-link">'.e($program->name_ar).'</a>'])
                                @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'رمز البرنامج', 'value' => '<code class="admin-code">'.e($program->code).'</code>'])
                                @include('partials.admin.detail-field', ['icon' => 'layers', 'label' => 'نوع البرنامج', 'value' => AcademicProgramOptions::typeLabel($program->type)])
                                @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'المدة', 'value' => $program->displayDuration()])
                                @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'حالة البرنامج', 'value' => AcademicProgramOptions::statusLabel($program->status)])
                                @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'منسق البرنامج', 'value' => $program->coordinator ?: '—'])
                                @if ($program->city)
                                    @include('partials.admin.detail-field', ['icon' => 'pin', 'label' => 'المدينة', 'value' => $program->city])
                                @endif
                                @if ($program->study_status)
                                    @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'حالة الدراسة', 'value' => $program->study_status])
                                @endif
                            </div>
                        </section>
                    </div>
                @else
                    <p class="admin-detail-empty">لم يُربَط برنامج بعد.</p>
                @endif
            </div>
        </section>

        <section class="student-profile-card">
            <header class="student-profile-card__head">
                <span class="student-profile-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
                </span>
                <h2 class="student-profile-card__title">الدفعة والالتحاق</h2>
            </header>
            <div class="student-profile-card__body">
                @if ($batch)
                    <div class="admin-detail-fields admin-detail-fields--2">
                        @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'الدفعة', 'value' => '<a href="'.route('admin.batches.show', $batch).'" class="admin-link">'.e($batch->name).'</a> <code class="admin-code">'.e($batch->code).'</code>'])
                        @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'فصل القبول', 'value' => $batch->displaySemester()])
                        @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'نمط الدراسة', 'value' => AcademicBatchOptions::studyModeLabel($batch->study_mode)])
                        @include('partials.admin.detail-field', ['icon' => 'flag', 'label' => 'حالة الدفعة', 'value' => AcademicBatchOptions::statusLabel($batch->status)])
                        @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'الإشغال', 'value' => $batch->students_count.($batch->capacity ? ' / '.$batch->capacity.' طالب' : ' طالب')])
                        @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'منسق الدفعة', 'value' => $batch->coordinator ?: '—'])
                        @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'تاريخ الالتحاق', 'value' => $student->joined_at?->format('Y-m-d H:i') ?? '—'])
                        @include('partials.admin.detail-field', ['icon' => 'hash', 'label' => 'الرقم الأكاديمي', 'value' => '<code class="admin-code">'.e($student->academic_id ?? '—').'</code>'])
                    </div>
                @else
                    <p class="admin-detail-empty">لم تُربَط الدفعة بعد.</p>
                @endif
            </div>
        </section>
    </div>

    <section class="student-profile-card">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            </span>
            <h2 class="student-profile-card__title">الشعبة والجدول</h2>
        </header>
        <div class="student-profile-card__body">
            @if ($section)
                <div class="admin-detail-fields admin-detail-fields--2">
                    @include('partials.admin.detail-field', ['icon' => 'bookmark', 'label' => 'الشعبة', 'value' => '<a href="'.route('admin.sections.show', $section).'" class="admin-link">'.e($section->name).'</a> <code class="admin-code">'.e($section->code).'</code>'])
                    @if ($section->course)
                        @include('partials.admin.detail-field', ['icon' => 'book', 'label' => 'المقرر الحالي', 'value' => '<a href="'.route('admin.academic-courses.show', $section->course).'" class="admin-link">'.e($section->course->name_ar).'</a>'])
                    @endif
                    @include('partials.admin.detail-field', ['icon' => 'layers', 'label' => 'المستوى', 'value' => $section->level?->name_ar ?? '—'])
                    @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'الفترة', 'value' => AcademicSectionOptions::periodLabel($section->period)])
                    @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'مشرف الشعبة', 'value' => $section->supervisor ?: '—'])
                    @include('partials.admin.detail-field', ['icon' => 'chart', 'label' => 'إشغال الشعبة', 'value' => $section->students_count.' / '.$section->max_capacity])
                </div>

                @if ($schedule)
                    <div class="student-academic-schedule">
                        <h3 class="student-academic-schedule__title">موعد المحاضرة</h3>
                        <div class="admin-detail-fields admin-detail-fields--2">
                            @include('partials.admin.detail-field', ['icon' => 'calendar', 'label' => 'اليوم', 'value' => AcademicScheduleOptions::dayLabel($schedule->day_of_week)])
                            @include('partials.admin.detail-field', ['icon' => 'clock', 'label' => 'الوقت', 'value' => '<span dir="ltr">'.e(AcademicScheduleOptions::formatTimeRange($schedule->time_start, $schedule->time_end)).'</span>'])
                            @include('partials.admin.detail-field', ['icon' => 'user', 'label' => 'عضو هيئة التدريس', 'value' => $schedule->displayTrainer()])
                        </div>
                    </div>
                @else
                    <p class="admin-detail-empty" style="margin-top:0.75rem;">لم يُربَط جدول دراسي بهذه الشعبة.</p>
                @endif
            @else
                <p class="admin-detail-empty">لم يُسجَّل الطالب في شعبة بعد. @canAdmin('students.manage')<a href="{{ route('admin.students.edit', $student) }}" class="admin-link">تعديل التسجيل</a>@endcanAdmin</p>
            @endif
        </div>
    </section>

    @foreach ($coursesByLevel as $levelName => $levelCourses)
        <section class="student-profile-card">
            <header class="student-profile-card__head">
                <span class="student-profile-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
                </span>
                <h2 class="student-profile-card__title">{{ $levelName }} <span class="admin-crud-card__meta">— {{ $levelCourses->count() }} مقرر · {{ $levelCourses->sum('credit_hours') }} ساعة</span></h2>
            </header>
            <div class="student-profile-card__body">
                <div class="admin-table-wrap">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>الكود</th>
                                <th>المقرر</th>
                                <th>الرمز</th>
                                <th>الساعات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($levelCourses as $course)
                                <tr>
                                    <td><a href="{{ route('admin.academic-courses.show', $course) }}" class="dash-inline-link"><code class="admin-code">{{ $course->code }}</code></a></td>
                                    <td>{{ $course->name_ar }}</td>
                                    <td>{{ $course->symbol_ar ?? '—' }}</td>
                                    <td>{{ $course->credit_hours }}</td>
                                    <td>
                                        <span @class(['admin-badge', 'admin-badge--success' => $course->status === 'active', 'admin-badge--danger' => $course->status !== 'active'])>
                                            {{ $course->status === 'active' ? 'فعال' : 'غير فعال' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endforeach

    @if ($courses->isEmpty())
        <section class="student-profile-card">
            <div class="student-profile-card__body">
                <p class="admin-detail-empty">لا توجد مقررات مرتبطة ببرنامج هذا الطالب.</p>
            </div>
        </section>
    @endif
</div>

@once
    @push('styles')
    <style>
        .student-academic-tab { display: flex; flex-direction: column; gap: 1rem; }
        .student-academic-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
            gap: 0.65rem;
        }
        .student-academic-stat {
            padding: 0.85rem 1rem;
            border-radius: var(--radius-md);
            background: var(--sa-mist);
            border: 1px solid var(--sa-border);
            text-align: center;
        }
        .student-academic-stat__value {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 1.75rem;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--sa-green-dark);
            margin-bottom: 0.2rem;
        }
        .student-academic-stat__label {
            display: block;
            font-size: 0.72rem;
            color: var(--sa-muted);
            font-weight: 600;
        }
        .student-profile-board--academic {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
            gap: 1rem;
        }
        .student-academic-schedule {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--sa-border);
        }
        .student-academic-schedule__title {
            margin: 0 0 0.75rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--sa-ink);
        }
    </style>
    @endpush
@endonce
