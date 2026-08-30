@php
    use App\Support\AttendanceOptions;
    use App\Support\MeetingSettings;
    use App\Support\RecordingOptions;
    use App\Support\TeamsSettings;
    use App\Support\ZoomSettings;
    use App\Support\ZoxAgentSettings;
@endphp

<div class="section-attendance-tab">
    <div class="student-academic-stats">
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $attendanceSectionSummary['sessions'] }}</span>
            <span class="student-academic-stat__label">جلسات مسجّلة</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $attendanceSectionSummary['students'] }}</span>
            <span class="student-academic-stat__label">طلاب الشعبة</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $attendanceSectionSummary['avg_rate'] }}%</span>
            <span class="student-academic-stat__label">متوسط الحضور</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $attendanceSectionSummary['present_total'] }}</span>
            <span class="student-academic-stat__label">حضور (إجمالي)</span>
        </div>
        <div class="student-academic-stat">
            <span class="student-academic-stat__value">{{ $attendanceSectionSummary['absent_total'] }}</span>
            <span class="student-academic-stat__label">غياب (إجمالي)</span>
        </div>
    </div>

    @canAdmin('attendance.manage')
        <div class="admin-filter-actions" style="margin-bottom:0.5rem;">
            @if ($section->schedule)
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="generateUpcomingSessions">
                    توليد حصص قادمة من الجدول
                </button>
            @endif
            <a href="{{ route('admin.sessions', ['sectionId' => $section->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">كل الحصص</a>
            <span class="admin-badge admin-badge--info">المزوّد الافتراضي: {{ MeetingSettings::providers()[MeetingSettings::defaultProvider()] ?? MeetingSettings::defaultProvider() }}</span>
        </div>

        <section class="admin-crud-card admin-crud-card--filter">
            <div class="admin-crud-card__head">
                <h2>جلسة حضور جديدة</h2>
            </div>
            <div class="admin-filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));">
                <div class="admin-field">
                    <label>تاريخ الجلسة *</label>
                    <input type="date" class="admin-control" wire:model="attendanceSessionDate">
                    @error('attendanceSessionDate')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>عنوان الجلسة</label>
                    <input type="text" class="admin-control" wire:model="attendanceSessionTitle" placeholder="محاضرة — اختياري">
                </div>
                <div class="admin-field" style="display:flex;align-items:flex-end;">
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="createAttendanceSession">+ إنشاء جلسة</button>
                </div>
            </div>
        </section>
    @endcanAdmin

    @if ($this->attendanceSessions->isEmpty())
        <section class="student-profile-card">
            <div class="student-profile-card__body">
                <p class="admin-detail-empty">لا توجد جلسات حضور مسجّلة لهذه الشعبة.</p>
            </div>
        </section>
    @else
        <div class="admin-field" style="max-width:20rem;margin-bottom:0.75rem;">
            <label>اختر الجلسة</label>
            <select class="admin-control" wire:model.live="attendanceSessionId">
                @foreach ($this->attendanceSessions as $session)
                    <option value="{{ $session->id }}">{{ $session->session_date->format('Y-m-d') }} — {{ $session->displayTitle() }}</option>
                @endforeach
            </select>
        </div>

        @if ($this->selectedAttendanceSession)
            @php
                $selected = $this->selectedAttendanceSession;
                $zoom = $selected->zoomMeeting;
                $zox = $selected->zoxAgentMeeting;
                $rec = $selected->recording;
            @endphp

            @if (ZoxAgentSettings::enabled() || $zox)
                <section class="admin-crud-card admin-crud-card--filter">
                    <div class="admin-crud-card__head">
                        <h2>ZoxAgent Meet</h2>
                        <p class="admin-crud-card__meta">إنشاء القاعة، دخول الطلاب من حساب المنصة، ومزامنة الحضور.</p>
                    </div>
                    <div class="admin-teams-session-bar">
                        @if ($zox)
                            <span class="admin-badge admin-badge--success">قاعة {{ $zox->room_code }}</span>
                            @if ($zox->last_started_at)
                                <span class="admin-crud-card__meta">آخر بدء: {{ $zox->last_started_at->diffForHumans() }}</span>
                            @endif
                            @if ($zox->attendance_synced_at)
                                <span class="admin-crud-card__meta">آخر مزامنة حضور: {{ $zox->attendance_synced_at->diffForHumans() }}</span>
                            @endif
                            @if ($zox->last_error)
                                <span class="admin-badge admin-badge--warn">{{ $zox->last_error }}</span>
                            @endif
                            <a href="{{ route('admin.sessions.zoxagent.join', $selected) }}" class="admin-btn-secondary admin-btn-secondary--sm">فتح القاعة</a>
                        @else
                            <span class="admin-badge admin-badge--warn">لا توجد قاعة ZoxAgent</span>
                        @endif

                        @canAdmin('attendance.manage')
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="createZoxAgentMeeting" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="createZoxAgentMeeting">{{ $zox ? 'تحديث قاعة ZoxAgent' : 'إنشاء قاعة ZoxAgent' }}</span>
                                <span wire:loading wire:target="createZoxAgentMeeting">جاري الإنشاء…</span>
                            </button>
                            @if ($zox)
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncZoxAgentAttendance" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="syncZoxAgentAttendance">مزامنة الحضور من ZoxAgent</span>
                                    <span wire:loading wire:target="syncZoxAgentAttendance">جاري المزامنة…</span>
                                </button>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="endZoxAgentMeeting" wire:confirm="إنهاء القاعة يوقف البث والتسجيل. متابعة؟">إنهاء القاعة</button>
                            @endif
                        @endcanAdmin
                    </div>
                </section>
            @endif

            @if (ZoomSettings::enabled() || $zoom)
                <section class="admin-crud-card admin-crud-card--filter">
                    <div class="admin-crud-card__head">
                        <h2>Zoom</h2>
                        <p class="admin-crud-card__meta">إنشاء الاجتماع، المضيف، التسجيل الفردي، ومزامنة الحضور والتسجيل.</p>
                    </div>
                    <div class="admin-teams-session-bar">
                        @if ($zoom)
                            <span class="admin-badge admin-badge--success">اجتماع Zoom: {{ $zoom->status ?: 'مرتبط' }}</span>
                            @if ($zoom->host)
                                <span class="admin-crud-card__meta">المضيف: {{ $zoom->host->email ?: $zoom->host->zoom_user_id }}</span>
                            @endif
                            <span class="admin-crud-card__meta" dir="ltr">ID: {{ $zoom->meeting_id }}</span>
                            <span class="admin-crud-card__meta">رمز الدخول: {{ $this->maskedPasscode($zoom->passcode) }}</span>
                            @if ($zoom->registration_mode)
                                <span class="admin-badge admin-badge--info">تسجيل: {{ $zoom->registration_mode }}</span>
                            @endif
                            @if ($zoom->registrants->isNotEmpty())
                                <span class="admin-crud-card__meta">مسجّلون: {{ $zoom->registrants->count() }}</span>
                            @endif
                            @if ($zoom->attendance_synced_at)
                                <span class="admin-crud-card__meta">آخر مزامنة حضور: {{ $zoom->attendance_synced_at->diffForHumans() }}</span>
                            @endif
                            @if ($zoom->recordings_synced_at)
                                <span class="admin-crud-card__meta">آخر مزامنة تسجيل: {{ $zoom->recordings_synced_at->diffForHumans() }}</span>
                            @endif
                            @if ($zoom->join_url)
                                <a href="{{ $zoom->join_url }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm">فتح رابط الانضمام</a>
                            @endif
                        @else
                            <span class="admin-badge admin-badge--warn">لا يوجد اجتماع Zoom</span>
                        @endif

                        @canAdmin('attendance.manage')
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="createZoomMeeting" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="createZoomMeeting">{{ $zoom ? 'تحديث اجتماع Zoom' : 'إنشاء اجتماع Zoom' }}</span>
                                <span wire:loading wire:target="createZoomMeeting">جاري الإنشاء…</span>
                            </button>
                            @if ($zoom)
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncZoomAttendance" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="syncZoomAttendance">مزامنة الحضور من Zoom</span>
                                    <span wire:loading wire:target="syncZoomAttendance">جاري المزامنة…</span>
                                </button>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncZoomRecording" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="syncZoomRecording">مزامنة التسجيل من Zoom</span>
                                    <span wire:loading wire:target="syncZoomRecording">جاري المزامنة…</span>
                                </button>
                            @endif
                        @endcanAdmin
                        @canAdmin('attendance.manage')
                            <a href="{{ route('admin.assignments.create', ['section' => $section->id, 'session' => $selected->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">+ واجب للحصة</a>
                        @endcanAdmin
                    </div>
                </section>
            @endif

            @if (TeamsSettings::isEnabled())
                <section class="admin-crud-card admin-crud-card--filter">
                    <div class="admin-crud-card__head">
                        <h2>Microsoft Teams</h2>
                    </div>
                    <div class="admin-teams-session-bar">
                        @if ($selected->teams_join_web_url)
                            <span class="admin-badge admin-badge--success">اجتماع Teams مرتبط</span>
                            <a href="{{ $selected->teams_join_web_url }}" target="_blank" rel="noopener" class="admin-btn-secondary admin-btn-secondary--sm" dir="ltr">فتح الرابط</a>
                            @if ($selected->teams_attendance_synced_at)
                                <span class="admin-crud-card__meta">آخر مزامنة: {{ $selected->teams_attendance_synced_at->diffForHumans() }}</span>
                            @endif
                        @else
                            <span class="admin-badge admin-badge--warn">لا يوجد اجتماع Teams</span>
                            @canAdmin('attendance.manage')
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="createTeamsMeeting">إنشاء اجتماع Teams</button>
                            @endcanAdmin
                        @endif
                        @canAdmin('attendance.manage')
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncTeamsAttendance" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="syncTeamsAttendance">مزامنة الحضور من Teams</span>
                                <span wire:loading wire:target="syncTeamsAttendance">جاري المزامنة…</span>
                            </button>
                        @endcanAdmin
                    </div>
                </section>
            @endif

            @if (ZoomSettings::enabled() || TeamsSettings::isEnabled() || $rec || $zox)
                <section class="admin-crud-card admin-crud-card--filter">
                    <div class="admin-crud-card__head">
                        <h2>تسجيل المحاضرة</h2>
                    </div>
                    <div class="admin-teams-session-bar" style="margin-bottom:0.75rem;">
                        @if ($rec)
                            <span @class(['admin-badge', RecordingOptions::statusBadgeClass($rec->status)])>{{ $rec->statusLabel() }}</span>
                            @if ($rec->provider)
                                <span class="admin-badge admin-badge--info">{{ $rec->provider }}</span>
                            @endif
                            @if ($rec->formattedDuration())
                                <span class="admin-crud-card__meta">المدة: {{ $rec->formattedDuration() }}</span>
                            @endif
                            @if ($rec->recorded_at)
                                <span class="admin-crud-card__meta">{{ $rec->recorded_at->format('Y-m-d H:i') }}</span>
                            @endif
                            @if ($rec->recording_passcode)
                                <span class="admin-crud-card__meta">رمز التسجيل: {{ $this->maskedPasscode($rec->recording_passcode) }}</span>
                            @endif
                            @if ($rec->recording_url)
                                <a href="{{ $rec->recording_url }}" target="_blank" class="admin-btn-secondary admin-btn-secondary--sm">معاينة</a>
                            @endif
                        @else
                            <span class="admin-badge admin-badge--muted">لا يوجد تسجيل بعد</span>
                        @endif
                        @canAdmin('attendance.manage')
                            @if ($zox)
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncZoxAgentRecording">مزامنة من ZoxAgent</button>
                            @endif
                            @if ($zoom)
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncZoomRecording">مزامنة من Zoom</button>
                            @endif
                            @if (TeamsSettings::isEnabled())
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="syncSessionRecording">مزامنة من Teams</button>
                            @endif
                            @if ($rec && $rec->recording_url && ! $rec->isPublished())
                                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="publishSessionRecording">نشر للطلاب</button>
                            @endif
                            @if ($rec?->isPublished())
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="hideSessionRecording">إخفاء</button>
                            @endif
                        @endcanAdmin
                    </div>
                    @canAdmin('attendance.manage')
                        <div class="admin-filter-grid" style="grid-template-columns:1fr auto;">
                            <div class="admin-field" style="margin:0;">
                                <label>رابط تسجيل يدوي (fallback)</label>
                                <input type="url" class="admin-control" wire:model="recordingManualUrl" dir="ltr" placeholder="https://...">
                                @error('recordingManualUrl')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                            </div>
                            <div class="admin-field" style="display:flex;align-items:flex-end;margin:0;">
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="saveManualRecording">حفظ الرابط</button>
                            </div>
                        </div>
                    @endcanAdmin
                </section>
            @endif

            @canAdmin('attendance.manage')
                <section class="admin-crud-card admin-crud-card--filter">
                    <div class="admin-crud-card__head">
                        <h2>مرفقات الحصة</h2>
                        <p class="admin-crud-card__meta">تظهر للطلاب في صفحة تفاصيل الحصة.</p>
                    </div>

                    @if ($selected->materials->isNotEmpty())
                        <ul class="admin-material-list">
                            @foreach ($selected->materials as $material)
                                <li wire:key="mat-{{ $material->id }}" class="admin-material-list__item">
                                    <span>{{ $material->title }}</span>
                                    <span class="admin-crud-card__meta">{{ $material->type === 'link' ? 'رابط' : 'ملف' }}</span>
                                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteSessionMaterial({{ $material->id }})" wire:confirm="حذف هذا المرفق؟">حذف</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="admin-filter-grid" style="grid-template-columns:repeat(auto-fit,minmax(12rem,1fr));margin-top:0.75rem;">
                        <div class="admin-field">
                            <label>عنوان المرفق</label>
                            <input type="text" class="admin-control" wire:model="materialTitle">
                            @error('materialTitle')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                        <div class="admin-field">
                            <label>رفع ملف</label>
                            <input type="file" class="admin-control" wire:model="materialFile">
                            @error('materialFile')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                        <div class="admin-field" style="display:flex;align-items:flex-end;">
                            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="uploadSessionMaterial" wire:loading.attr="disabled">رفع ملف</button>
                        </div>
                    </div>
                    <div class="admin-filter-grid" style="grid-template-columns:1fr 1fr auto;margin-top:0.5rem;">
                        <div class="admin-field">
                            <label>عنوان الرابط</label>
                            <input type="text" class="admin-control" wire:model="materialTitle">
                        </div>
                        <div class="admin-field">
                            <label>رابط خارجي</label>
                            <input type="url" class="admin-control" wire:model="materialLink" dir="ltr" placeholder="https://">
                            @error('materialLink')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                        <div class="admin-field" style="display:flex;align-items:flex-end;">
                            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="addSessionMaterialLink">إضافة رابط</button>
                        </div>
                    </div>
                </section>
            @endcanAdmin

            <section class="student-profile-card">
                <header class="student-profile-card__head">
                    <span class="student-profile-card__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </span>
                    <h2 class="student-profile-card__title">
                        {{ $selected->displayTitle() }}
                        <span class="admin-crud-card__meta">— {{ $selected->session_date->format('Y-m-d') }}</span>
                    </h2>
                </header>
                <div class="student-profile-card__body">
                    <div class="admin-table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الرقم الأكاديمي</th>
                                    <th>الطالب</th>
                                    <th>الحالة</th>
                                    <th>المصدر</th>
                                    @canAdmin('attendance.manage')
                                        <th>تعديل</th>
                                    @endcanAdmin
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->selectedSessionRecords as $index => $record)
                                    @php
                                        $sourceLabel = match ($record->source) {
                                            'zoom_sync' => 'Zoom',
                                            'teams_sync' => 'Microsoft Teams',
                                            default => $record->source ? AttendanceOptions::sourceLabel($record->source) : '—',
                                        };
                                    @endphp
                                    <tr wire:key="att-{{ $record->student_id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td><code class="admin-code">{{ $record->student?->academic_id ?? '—' }}</code></td>
                                        <td>
                                            @if ($record->student)
                                                <a href="{{ route('admin.students.show', ['student' => $record->student, 'tab' => 'attendance']) }}" class="dash-inline-link">{{ $record->student->name_ar }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <span @class(['admin-badge', AttendanceOptions::recordBadgeClass($record->status)])>
                                                {{ AttendanceOptions::recordStatusLabel($record->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($record->id && $record->source)
                                                <span @class([
                                                    'admin-badge',
                                                    'admin-badge--info' => in_array($record->source, ['teams_sync', 'zoom_sync'], true),
                                                ])>
                                                    {{ $sourceLabel }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        @canAdmin('attendance.manage')
                                            <td>
                                                <select class="admin-control admin-control--inline" wire:change="updateAttendanceStatus({{ $record->student_id }}, $event.target.value)">
                                                    @foreach (AttendanceOptions::recordStatuses() as $value => $label)
                                                        <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        @endcanAdmin
                                        <td>{{ $record->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ auth()->user()?->canAdmin('attendance.manage') ? 7 : 6 }}" style="text-align:center;padding:1.5rem">لا يوجد طلاب في هذه الشعبة.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    @endif
</div>

@once
    @push('styles')
    <style>
        .section-attendance-tab { display: flex; flex-direction: column; gap: 1rem; }
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
            display: block;
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
        .admin-badge--info { background: #eff6ff; color: #1d4ed8; }
        .admin-badge--danger { background: #fef2f2; color: #b91c1c; }
        .admin-badge--warn { background: #fff7ed; color: #c2410c; }
        .admin-control--inline { min-width: 7rem; padding: 0.25rem 0.5rem; font-size: 0.82rem; }
        .admin-teams-session-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .admin-material-list { list-style: none; margin: 0 0 0.5rem; padding: 0; }
        .admin-material-list__item {
            display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0; border-bottom: 1px solid var(--sa-border);
        }
    </style>
    @endpush
@endonce
