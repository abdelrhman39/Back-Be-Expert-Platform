@php
    use App\Support\AttendanceOptions;

    $rate = $attendanceSummary['rate'];
    $rateTone = $rate >= 80 ? 'good' : ($rate >= 60 ? 'warn' : 'bad');
@endphp

<div class="student-attendance-tab">
    <section class="attendance-overview admin-crud-card">
        <div class="attendance-overview__inner">
            <div class="attendance-ring attendance-ring--{{ $rateTone }}" style="--attendance-rate: {{ min(100, max(0, $rate)) }}">
                <div class="attendance-ring__center">
                    <strong class="attendance-ring__value">{{ $rate }}%</strong>
                    <span class="attendance-ring__label">نسبة الحضور</span>
                </div>
            </div>

            <div class="attendance-overview__body">
                <div class="attendance-overview__head">
                    <h3 class="attendance-overview__title">ملخص الحضور والغياب</h3>
                    <p class="attendance-overview__desc">
                        {{ $attendanceSummary['sessions'] }} جلسة مسجّلة
                        @if ($student->section)
                            — شعبة <a href="{{ route('admin.sections.show', ['section' => $student->section, 'tab' => 'attendance']) }}" class="admin-link">{{ $student->section->name }}</a>
                        @endif
                    </p>
                </div>

                <div class="dash-status-row attendance-kpi-row">
                    <div class="dash-status-pill dash-status-pill--green attendance-kpi-pill">
                        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></div>
                        <strong>{{ $attendanceSummary['present'] }}</strong>
                        <span>حاضر</span>
                    </div>
                    <div class="dash-status-pill dash-status-pill--orange attendance-kpi-pill">
                        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
                        <strong>{{ $attendanceSummary['late'] }}</strong>
                        <span>متأخر</span>
                    </div>
                    <div class="dash-status-pill dash-status-pill--red attendance-kpi-pill">
                        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></div>
                        <strong>{{ $attendanceSummary['absent'] }}</strong>
                        <span>غائب</span>
                    </div>
                    <div class="dash-status-pill dash-status-pill--blue attendance-kpi-pill">
                        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                        <strong>{{ $attendanceSummary['excused'] }}</strong>
                        <span>معذور</span>
                    </div>
                    <div class="dash-status-pill dash-status-pill--gray attendance-kpi-pill">
                        <div class="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                        <strong>{{ $attendanceSummary['sessions'] }}</strong>
                        <span>إجمالي الجلسات</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($student->section)
            <div class="attendance-context">
                @if ($student->section->course)
                    <span class="attendance-context__chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>
                        {{ $student->section->course->name_ar }}
                    </span>
                @endif
                <span class="attendance-context__chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                    {{ $student->section->name }}
                </span>
                @canAdmin('attendance.manage')
                    <a href="{{ route('admin.sections.show', ['section' => $student->section, 'tab' => 'attendance']) }}" class="admin-btn-secondary admin-btn-secondary--sm">إدارة حضور الشعبة</a>
                @endcanAdmin
            </div>
        @endif
    </section>

    <section class="student-profile-card attendance-log-card">
        <header class="student-profile-card__head">
            <span class="student-profile-card__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </span>
            <h2 class="student-profile-card__title">سجل الحضور <span class="admin-crud-card__meta">— {{ $attendanceRecords->count() }} جلسة</span></h2>
        </header>
        <div class="student-profile-card__body">
            @if ($attendanceRecords->isEmpty())
                <div class="attendance-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <p>لا يوجد سجل حضور لهذا الطالب بعد.</p>
                    @if ($student->section)
                        @canAdmin('attendance.manage')
                            <a href="{{ route('admin.sections.show', ['section' => $student->section, 'tab' => 'attendance']) }}" class="admin-btn-primary admin-btn-primary--sm">تسجيل حضور من الشعبة</a>
                        @endcanAdmin
                    @endif
                </div>
            @else
                <div class="admin-table-wrap attendance-table-wrap">
                    <table class="admin-data-table attendance-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>الجلسة</th>
                                <th>الحالة</th>
                                <th>المصدر</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendanceRecords as $index => $record)
                                <tr @class(['attendance-table__row--'.$record->status])>
                                    <td>{{ $index + 1 }}</td>
                                    <td dir="ltr">{{ $record->session?->session_date?->format('Y-m-d') ?? '—' }}</td>
                                    <td>
                                        <strong>{{ $record->session?->displayTitle() ?? '—' }}</strong>
                                        @if ($record->session?->section && $record->session->section_id !== $student->section_id)
                                            <span class="admin-table-sub">{{ $record->session->section->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span @class(['admin-badge', 'attendance-badge', AttendanceOptions::recordBadgeClass($record->status)])>
                                            {{ AttendanceOptions::recordStatusLabel($record->status) }}
                                        </span>
                                    </td>
                                    <td><span class="attendance-source">{{ AttendanceOptions::sourceLabel($record->source) }}</span></td>
                                    <td>{{ $record->notes ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
</div>
