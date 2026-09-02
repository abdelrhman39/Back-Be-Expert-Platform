<?php

use App\Models\CatalogCourseLesson;
use App\Models\CatalogEnrollment;
use App\Services\CourseContentService;
use App\Support\CatalogEnrollmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('متابعة التعلم | مركز التعلم المستمر')]
class extends Component
{
    public CatalogEnrollment $enrollment;

    #[Url(as: 'lesson')]
    public ?int $activeLessonId = null;

    /** @var array<int> */
    public array $expandedModuleIds = [];

    public function mount(CatalogEnrollment $enrollment, CourseContentService $content): void
    {
        abort_unless($enrollment->user_id === auth()->id(), 403);
        abort_unless(in_array($enrollment->status, ['active', 'completed'], true), 404);

        $this->enrollment = $enrollment->load(['course', 'order']);
        $this->expandedModuleIds = $content->curriculumForEnrollment($this->enrollment)->pluck('id')->all();

        if (! $this->activeLessonId) {
            $this->activeLessonId = $content->firstLessonId($this->enrollment);
        }

        if ($this->activeLessonId) {
            $lesson = CatalogCourseLesson::query()->with('module')->find($this->activeLessonId);
            if ($lesson?->module_id && ! in_array($lesson->module_id, $this->expandedModuleIds, true)) {
                $this->expandedModuleIds[] = $lesson->module_id;
            }
        }
    }

    public function isModuleExpanded(int $moduleId): bool
    {
        return in_array($moduleId, $this->expandedModuleIds, true);
    }

    public function toggleModuleCollapse(int $moduleId): void
    {
        if ($this->isModuleExpanded($moduleId)) {
            $this->expandedModuleIds = array_values(array_filter(
                $this->expandedModuleIds,
                fn (int $id) => $id !== $moduleId,
            ));
        } else {
            $this->expandedModuleIds[] = $moduleId;
        }
    }

    #[Computed]
    public function curriculum()
    {
        return app(CourseContentService::class)->curriculumForEnrollment($this->enrollment);
    }

    #[Computed]
    public function moduleAccessMap(): array
    {
        return app(CourseContentService::class)->moduleAccessMap($this->enrollment);
    }

    #[Computed]
    public function progressMap(): array
    {
        return app(CourseContentService::class)->progressMap($this->enrollment);
    }

    #[Computed]
    public function flatLessons()
    {
        return $this->curriculum->flatMap(fn ($module) => $module->lessons)->values();
    }

    #[Computed]
    public function lessonNav(): array
    {
        $lessons = $this->flatLessons;
        $index = $lessons->search(fn ($lesson) => $lesson->id === $this->activeLessonId);

        return [
            'prev' => is_int($index) && $index > 0 ? $lessons[$index - 1]->id : null,
            'next' => is_int($index) && $index < $lessons->count() - 1 ? $lessons[$index + 1]->id : null,
            'position' => is_int($index) ? $index + 1 : 0,
            'total' => $lessons->count(),
        ];
    }

    #[Computed]
    public function activeLesson(): ?CatalogCourseLesson
    {
        if (! $this->activeLessonId) {
            return null;
        }

        return CatalogCourseLesson::query()
            ->with('module')
            ->whereHas('module', fn ($q) => $q->where('course_id', $this->enrollment->course_id))
            ->find($this->activeLessonId);
    }

    #[Computed]
    public function stats(): array
    {
        $content = app(CourseContentService::class);

        return [
            'total' => $content->totalLessons($this->enrollment),
            'completed' => $content->completedLessons($this->enrollment),
            'percent' => $this->enrollment->progress_percent,
        ];
    }

    public function selectLesson(int $lessonId): void
    {
        $lesson = CatalogCourseLesson::query()
            ->with('module')
            ->whereHas('module', fn ($q) => $q->where('course_id', $this->enrollment->course_id))
            ->find($lessonId);

        if (! $lesson || ! $lesson->module) {
            return;
        }

        $access = app(CourseContentService::class)->moduleAccessState(
            $this->enrollment,
            $lesson->module,
            $this->progressMap,
        );

        if (! $access['accessible']) {
            return;
        }

        $this->activeLessonId = $lessonId;
        if ($lesson->module_id && ! $this->isModuleExpanded($lesson->module_id)) {
            $this->expandedModuleIds[] = $lesson->module_id;
        }
        unset($this->lessonNav, $this->activeLesson);
    }

    public function goToAdjacent(string $direction): void
    {
        $target = $direction === 'prev' ? $this->lessonNav['prev'] : $this->lessonNav['next'];

        if ($target) {
            $this->selectLesson($target);
        }
    }

    public function completeLesson(CourseContentService $content): void
    {
        $lesson = $this->activeLesson;

        if (! $lesson) {
            return;
        }

        $nextId = $this->lessonNav['next'];

        $content->markLessonComplete($this->enrollment, $lesson);
        $this->enrollment->refresh();
        unset($this->progressMap, $this->stats, $this->lessonNav, $this->activeLesson);

        $this->dispatch('portal-message', message: 'تم تسجيل إكمال الدرس.');

        if ($nextId) {
            $this->activeLessonId = $nextId;
        }
    }
};
?>

@php
    $locale = app()->getLocale();
    $lesson = $this->activeLesson;
    $progressMap = $this->progressMap;
    $stats = $this->stats;
    $nav = $this->lessonNav;
    $percent = min(100, max(0, $stats['percent']));
    $lessonDone = $lesson && ($progressMap[$lesson->id] ?? '') === 'completed';
@endphp

@include('partials.portal.shell-start', ['portalActive' => 'learning-list', 'portalTitle' => $enrollment->displayTitle()])

<div class="portal-dashboard portal-player-page">
    {{-- Hero --}}
    <header class="portal-player-hero">
        @if ($enrollment->displayImage())
            <div class="portal-player-hero__bg" style="background-image: url('{{ static_asset($enrollment->displayImage()) }}')"></div>
        @endif
        <div class="portal-player-hero__overlay"></div>
        <div class="portal-player-hero__orbs" aria-hidden="true">
            <span></span><span></span><span></span>
        </div>

        <div class="portal-player-hero__content">
            <nav class="portal-player-crumb" aria-label="مسار التنقل">
                <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="portal-player-crumb__link">
                    <i class="fa-solid fa-arrow-right"></i> قائمة التعلم
                </a>
                <span class="portal-player-crumb__sep"><i class="fa-solid fa-chevron-left"></i></span>
                <span class="portal-player-crumb__current">{{ Str::limit($enrollment->displayTitle(), 48) }}</span>
            </nav>

            <div class="portal-player-hero__row">
                <div class="portal-player-hero__info">
                    <div class="portal-player-hero__chips">
                        <span @class(['portal-enrollment-badge', CatalogEnrollmentOptions::statusBadgeClass($enrollment->status)])>
                            {{ CatalogEnrollmentOptions::statusLabel($enrollment->status) }}
                        </span>
                        <span class="portal-player-chip">
                            <i class="fa-solid fa-laptop"></i>
                            {{ CatalogEnrollmentOptions::deliveryLabel($enrollment->delivery_type) }}
                        </span>
                        @if ($enrollment->enrolled_at)
                            <span class="portal-player-chip">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $enrollment->enrolled_at->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="portal-player-hero__title">{{ $enrollment->displayTitle() }}</h1>
                    <p class="portal-player-hero__subtitle">
                        {{ $stats['completed'] }} من {{ $stats['total'] }} دروس مكتملة
                        @if ($nav['position'] && $nav['total'])
                            · الدرس {{ $nav['position'] }} من {{ $nav['total'] }}
                        @endif
                    </p>
                </div>

                <div class="portal-player-hero__stats">
                    <div class="portal-completion-ring portal-completion-ring--hero" aria-hidden="true">
                        <svg viewBox="0 0 36 36">
                            <path class="portal-completion-ring__bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="portal-completion-ring__fill" stroke-dasharray="{{ $percent }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <span class="portal-completion-ring__pct">{{ $percent }}%</span>
                    </div>
                    <div class="portal-player-hero__mini-stats">
                        <div class="portal-player-mini-stat">
                            <strong>{{ $stats['completed'] }}</strong>
                            <span>مكتمل</span>
                        </div>
                        <div class="portal-player-mini-stat">
                            <strong>{{ max(0, $stats['total'] - $stats['completed']) }}</strong>
                            <span>متبقي</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portal-player-hero__bar" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                <span style="width: {{ $percent }}%"></span>
            </div>
        </div>
    </header>

    @if ($this->curriculum->isEmpty())
        <div class="portal-panel">
            <div class="portal-empty portal-empty--lg">
                <div class="portal-empty__icon"><i class="fa-solid fa-book"></i></div>
                <p>محتوى هذه الدورة قيد الإعداد</p>
                <span class="portal-empty__hint">سيتم إضافة الدروس قريباً — تابع قائمة التعلم</span>
            </div>
        </div>
    @else
        <div class="portal-player-layout">
            {{-- Sidebar: curriculum --}}
            <aside class="portal-player-sidebar">
                <div class="portal-player-sidebar__head">
                    <h2 class="portal-player-sidebar__title">
                        <i class="fa-solid fa-layer-group"></i> محتوى الدورة
                    </h2>
                    <span class="portal-player-sidebar__count">{{ $stats['completed'] }}/{{ $stats['total'] }}</span>
                </div>

                <div class="portal-player-curriculum">
                    @php $lessonIndex = 0; @endphp
                    @foreach ($this->curriculum as $module)
                        @php
                            $moduleAccess = $this->moduleAccessMap[$module->id] ?? ['accessible' => true, 'reason' => null];
                            $moduleLocked = ! $moduleAccess['accessible'];
                            $moduleExpanded = $this->isModuleExpanded($module->id);
                            $moduleDone = $module->lessons->filter(fn ($l) => ($this->progressMap[$l->id] ?? '') === 'completed')->count();
                            $moduleTotal = $module->lessons->count();
                            $modulePct = $moduleTotal ? (int) round(($moduleDone / $moduleTotal) * 100) : 0;
                        @endphp
                        <section @class(['portal-player-module', 'portal-player-module--locked' => $moduleLocked, 'portal-player-module--collapsed' => ! $moduleExpanded])>
                            <header class="portal-player-module__head">
                                <button type="button"
                                    class="portal-player-module__toggle"
                                    wire:click="toggleModuleCollapse({{ $module->id }})"
                                    aria-expanded="{{ $moduleExpanded ? 'true' : 'false' }}">
                                    <span>{{ $moduleExpanded ? '▾' : '▸' }}</span>
                                </button>
                                <div class="portal-player-module__info">
                                    <h3 class="portal-player-module__title">
                                        @if ($module->icon)
                                            <i class="fa-solid {{ $module->icon }}"></i>
                                        @endif
                                        {{ $module->displayTitle() }}
                                        @if ($module->is_optional)
                                            <small class="portal-player-module__optional">(اختيارية)</small>
                                        @endif
                                    </h3>
                                    @if ($module->displaySummary())
                                        <p class="portal-player-module__summary">{{ $module->displaySummary() }}</p>
                                    @endif
                                    <span class="portal-player-module__meta">
                                        {{ $moduleDone }}/{{ $moduleTotal }} دروس
                                        @if ($module->estimated_duration_minutes)
                                            · {{ $module->estimated_duration_minutes }} د
                                        @endif
                                    </span>
                                    @if ($moduleLocked)
                                        <span class="portal-player-module__lock">
                                            <i class="fa-solid fa-lock"></i>
                                            @if ($moduleAccess['reason'] === 'drip')
                                                تُفتح بعد {{ $module->drip_days }} يوماً من التسجيل
                                            @elseif ($moduleAccess['reason'] === 'scheduled')
                                                تُفتح في {{ $module->unlock_at?->translatedFormat('d M Y H:i') }}
                                            @elseif ($moduleAccess['reason'] === 'prerequisite')
                                                أكمل الوحدات السابقة أولاً
                                            @else
                                                الوحدة غير متاحة حالياً
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <div class="portal-player-module__progress" aria-hidden="true">
                                    <span style="width: {{ $modulePct }}%"></span>
                                </div>
                            </header>

                            @if ($moduleExpanded)
                            <ul class="portal-player-lessons">
                                @foreach ($module->lessons as $item)
                                    @php
                                        $lessonIndex++;
                                        $done = ($this->progressMap[$item->id] ?? '') === 'completed';
                                        $active = $activeLessonId === $item->id;
                                    @endphp
                                    <li>
                                        <button type="button"
                                            wire:click="selectLesson({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="selectLesson({{ $item->id }})"
                                            @disabled($moduleLocked)
                                            @class(['portal-player-lesson', 'portal-player-lesson--active' => $active, 'portal-player-lesson--done' => $done, 'portal-player-lesson--locked' => $moduleLocked])>
                                            <span class="portal-player-lesson__num">{{ $lessonIndex }}</span>
                                            <span class="portal-player-lesson__icon">
                                                @if ($done)
                                                    <i class="fa-solid fa-circle-check"></i>
                                                @else
                                                    <i class="fa-solid {{ $item->typeIcon() }}"></i>
                                                @endif
                                            </span>
                                            <span class="portal-player-lesson__body">
                                                <strong>{{ $item->displayTitle() }}</strong>
                                                <span class="portal-player-lesson__meta">
                                                    <em>{{ $item->typeLabel() }}</em>
                                                    @if ($item->duration_minutes)
                                                        · {{ $item->duration_minutes }} د
                                                    @endif
                                                </span>
                                            </span>
                                            @if ($active)
                                                <span class="portal-player-lesson__playing"><i class="fa-solid fa-play"></i></span>
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            @endif
                        </section>
                    @endforeach
                </div>
            </aside>

            {{-- Main lesson viewer --}}
            <main class="portal-player-main">
                @if ($lesson)
                    <div class="portal-player-lesson-head">
                        <div class="portal-player-lesson-head__info">
                            <span class="portal-player-lesson-head__module">
                                <i class="fa-solid fa-folder-open"></i>
                                {{ $lesson->module?->displayTitle() }}
                            </span>
                            <h2 class="portal-player-lesson-head__title">{{ $lesson->displayTitle() }}</h2>
                            @if ($lesson->displaySummary())
                                <p class="portal-player-lesson-head__summary">{{ $lesson->displaySummary() }}</p>
                            @endif
                            <div class="portal-player-lesson-head__tags">
                                <span @class(['portal-player-type', $lesson->typeBadgeClass()])>
                                    <i class="fa-solid {{ $lesson->typeIcon() }}"></i>
                                    {{ $lesson->typeLabel() }}
                                </span>
                                @if ($lesson->duration_minutes)
                                    <span class="portal-player-duration">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ $lesson->duration_minutes }} دقيقة
                                    </span>
                                @endif
                                @if ($lessonDone)
                                    <span class="portal-status-pill portal-status-pill--paid">
                                        <i class="fa-solid fa-check"></i> مكتمل
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="portal-player-stage">
                        @if ($lesson->type === 'video' && $lesson->videoEmbedUrl())
                            <div class="portal-player-video">
                                <iframe src="{{ $lesson->videoEmbedUrl() }}" title="{{ $lesson->displayTitle() }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                            </div>
                        @elseif ($lesson->type === 'document')
                            <div class="portal-player-doc-banner">
                                <span class="portal-player-doc-banner__icon"><i class="fa-solid fa-file-lines"></i></span>
                                <div>
                                    <strong>درس قراءة</strong>
                                    <p>اقرأ المحتوى أدناه ثم أكّد إكمال الدرس للمتابعة.</p>
                                    @if ($lesson->file_path && $lesson->file_name)
                                        <p class="mb-0 mt-2">
                                            <a href="{{ route('catalog.lesson-file', ['locale' => app()->getLocale(), 'enrollment' => $enrollment->id, 'lesson' => $lesson->id]) }}" class="portal-btn-secondary portal-btn-secondary--sm" target="_blank">
                                                <i class="fa-solid fa-download"></i> تحميل {{ $lesson->file_name }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($lesson->displayBody())
                            <article class="portal-player-article">
                                {!! $lesson->displayBody() !!}
                            </article>
                        @elseif ($lesson->type !== 'video')
                            <div class="portal-empty portal-empty--sm">
                                <div class="portal-empty__icon"><i class="fa-solid fa-file-circle-xmark"></i></div>
                                <p>لا يوجد محتوى نصي لهذا الدرس.</p>
                            </div>
                        @endif

                        @if ($lesson->resource_url)
                            <p class="portal-player-resource">
                                <a href="{{ $lesson->resource_url }}" class="portal-btn-secondary portal-btn-secondary--sm" target="_blank" rel="noopener">
                                    <i class="fa-solid fa-link"></i> مورد إضافي
                                </a>
                            </p>
                        @endif
                    </div>

                    <footer class="portal-player-footer">
                        <button type="button"
                            class="btn btn-outline-secondary btn-sm portal-player-footer__nav"
                            wire:click="goToAdjacent('prev')"
                            @disabled(! $nav['prev'])>
                            <i class="fa-solid fa-arrow-right"></i> الدرس السابق
                        </button>

                        <div class="portal-player-footer__center">
                            @if (! $lessonDone)
                                <button type="button" class="btn btn-primary portal-player-footer__complete" wire:click="completeLesson" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="completeLesson">
                                        <i class="fa-solid fa-circle-check"></i> تم إكمال الدرس
                                    </span>
                                    <span wire:loading wire:target="completeLesson">جاري الحفظ…</span>
                                </button>
                            @else
                                <span class="portal-player-footer__done">
                                    <i class="fa-solid fa-circle-check"></i> أحسنت! أكملت هذا الدرس
                                </span>
                            @endif
                        </div>

                        <button type="button"
                            class="btn btn-outline-secondary btn-sm portal-player-footer__nav"
                            wire:click="goToAdjacent('next')"
                            @disabled(! $nav['next'])>
                            الدرس التالي <i class="fa-solid fa-arrow-left"></i>
                        </button>
                    </footer>
                @else
                    <div class="portal-empty portal-empty--lg">
                        <div class="portal-empty__icon"><i class="fa-solid fa-hand-pointer"></i></div>
                        <p>اختر درساً من قائمة المحتوى للبدء</p>
                        <span class="portal-empty__hint">يمكنك التنقل بين الدروس من الشريط الجانبي</span>
                    </div>
                @endif
            </main>
        </div>
    @endif
</div>

@include('partials.portal.shell-end')
