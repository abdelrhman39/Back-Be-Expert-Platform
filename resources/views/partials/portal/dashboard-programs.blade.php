{{-- Expects: $locale, $student (AcademicStudent|null), $program, $enrollments (collection), $hasPrograms (bool) --}}
<section class="portal-panel portal-panel--programs">
    <div class="portal-panel__head">
        <h2 class="portal-panel__title"><i class="fa-solid fa-graduation-cap"></i> برامجي ودوراتي</h2>
        @if ($hasPrograms)
            <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="portal-panel__link">قائمة التعلم <i class="fa-solid fa-arrow-left-long"></i></a>
        @endif
    </div>
    <div class="portal-panel__body">
        @if (! $hasPrograms)
            <div class="portal-empty">
                <div class="portal-empty__icon"><i class="fa-solid fa-book-open-reader"></i></div>
                <p>لا توجد برامج أو دورات مسجّلة بعد</p>
                <span class="portal-empty__hint">ستظهر هنا دبلوماتك وبرامجك الأكاديمية ودوراتك المشتراة للوصول السريع</span>
                                <div class="portal-empty__actions">
                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">تصفح الدورات</a>
                    @if (\App\Support\InstallmentSettings::academicRegistrationEnabled())
                        <a href="{{ route('academic-registration', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">التسجيل الأكاديمي</a>
                    @endif
                </div>
            </div>
        @else
            <div class="portal-learning-grid portal-learning-grid--dashboard">
                @if ($program && $student)
                    <article class="portal-learning-card portal-learning-card--academic">
                        <div class="portal-learning-card__media portal-learning-card__media--academic">
                            @if ($program->poster_image)
                                <img src="{{ static_asset($program->poster_image) }}" alt="" loading="lazy">
                            @else
                                <span class="portal-learning-card__placeholder portal-learning-card__placeholder--academic">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </span>
                            @endif
                            <span class="portal-enrollment-badge portal-enrollment-badge--academic">
                                {{ \App\Support\AcademicProgramOptions::typeLabel($program->type ?: 'diploma') }}
                            </span>
                        </div>
                        <div class="portal-learning-card__body">
                            <h3 class="portal-learning-card__title">{{ $program->name_ar }}</h3>
                            <div class="portal-learning-card__meta">
                                @if ($program->code)
                                    <span><i class="fa-solid fa-hashtag"></i> {{ $program->code }}</span>
                                @endif
                                @if ($student->batch?->name)
                                    <span><i class="fa-solid fa-users"></i> {{ $student->batch->name }}</span>
                                @endif
                                @if ($student->section?->name)
                                    <span><i class="fa-solid fa-layer-group"></i> {{ $student->section->name }}</span>
                                @endif
                            </div>
                            <div class="portal-learning-card__status-row">
                                <span class="portal-learning-status">
                                    {{ \App\Support\AcademicStudentOptions::academicStatusLabel($student->academic_status) }}
                                </span>
                            </div>
                            <div class="portal-learning-card__actions">
                                <a href="{{ route('academic-curriculum', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-sitemap"></i> منهج البرنامج
                                </a>
                                <a href="{{ route('sessions', ['locale' => $locale]) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-chalkboard"></i> حصصي
                                </a>
                            </div>
                        </div>
                    </article>
                @endif

                @foreach ($enrollments as $enrollment)
                    <article class="portal-learning-card">
                        <div class="portal-learning-card__media">
                            @if ($enrollment->displayImage())
                                <img src="{{ static_asset($enrollment->displayImage()) }}" alt="" loading="lazy">
                            @else
                                <span class="portal-learning-card__placeholder"><i class="fa-solid fa-graduation-cap"></i></span>
                            @endif
                            <span @class(['portal-enrollment-badge', \App\Support\CatalogEnrollmentOptions::statusBadgeClass($enrollment->status)])>
                                {{ \App\Support\CatalogEnrollmentOptions::statusLabel($enrollment->status) }}
                            </span>
                        </div>
                        <div class="portal-learning-card__body">
                            <h3 class="portal-learning-card__title">{{ $enrollment->displayTitle() }}</h3>
                            <div class="portal-learning-card__meta">
                                <span><i class="fa-solid fa-laptop"></i> {{ \App\Support\CatalogEnrollmentOptions::deliveryLabel($enrollment->delivery_type) }}</span>
                                <span><i class="fa-regular fa-calendar"></i> {{ $enrollment->enrolled_at?->translatedFormat('d M Y') }}</span>
                            </div>
                            <div class="portal-learning-card__progress">
                                <div class="portal-learning-card__progress-head">
                                    <span>التقدم</span>
                                    <strong>{{ $enrollment->progress_percent }}%</strong>
                                </div>
                                <div class="portal-attendance-mini__bar">
                                    <span style="width: {{ min(100, (int) $enrollment->progress_percent) }}%"></span>
                                </div>
                            </div>
                            <div class="portal-learning-card__actions">
                                <a href="{{ route('learning.player', ['locale' => $locale, 'enrollment' => $enrollment->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-play"></i> متابعة التعلم
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
