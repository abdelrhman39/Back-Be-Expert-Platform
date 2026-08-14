<?php

namespace App\Services;

use App\Models\CatalogContentProgress;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use App\Models\CatalogEnrollment;
use Illuminate\Support\Carbon;

class CourseContentService
{
    /** @return \Illuminate\Support\Collection<int, CatalogCourseModule> */
    public function curriculumForEnrollment(CatalogEnrollment $enrollment)
    {
        return CatalogCourseModule::query()
            ->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])
            ->where('course_id', $enrollment->course_id)
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get()
            ->map(function (CatalogCourseModule $module) {
                $module->setRelation(
                    'lessons',
                    $module->lessons->filter(fn (CatalogCourseLesson $lesson) => $lesson->isPublished())->values(),
                );

                return $module;
            })
            ->filter(fn (CatalogCourseModule $module) => $module->lessons->isNotEmpty() || $module->is_optional);
    }

    public function progressMap(CatalogEnrollment $enrollment): array
    {
        return CatalogContentProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->pluck('status', 'lesson_id')
            ->all();
    }

    public function moduleAccessMap(CatalogEnrollment $enrollment): array
    {
        $progressMap = $this->progressMap($enrollment);
        $map = [];

        foreach ($this->curriculumForEnrollment($enrollment) as $module) {
            $map[$module->id] = $this->moduleAccessState($enrollment, $module, $progressMap);
        }

        return $map;
    }

    /** @return array{accessible: bool, reason: ?string} */
    public function moduleAccessState(CatalogEnrollment $enrollment, CatalogCourseModule $module, ?array $progressMap = null): array
    {
        $progressMap ??= $this->progressMap($enrollment);

        if (! $module->isPublished()) {
            return ['accessible' => false, 'reason' => 'hidden'];
        }

        if ($module->unlock_at instanceof Carbon && now()->lt($module->unlock_at)) {
            return ['accessible' => false, 'reason' => 'scheduled'];
        }

        if ($module->drip_days && $enrollment->enrolled_at) {
            $unlockDate = $enrollment->enrolled_at->copy()->addDays($module->drip_days);
            if (now()->lt($unlockDate)) {
                return ['accessible' => false, 'reason' => 'drip'];
            }
        }

        foreach ($module->prerequisiteIds() as $prerequisiteId) {
            $prerequisite = CatalogCourseModule::query()
                ->where('course_id', $enrollment->course_id)
                ->find($prerequisiteId);

            if ($prerequisite && ! $this->isModuleComplete($prerequisite, $progressMap)) {
                return ['accessible' => false, 'reason' => 'prerequisite'];
            }
        }

        return ['accessible' => true, 'reason' => null];
    }

    /** @param  array<int, string>  $progressMap */
    public function isModuleComplete(CatalogCourseModule $module, array $progressMap): bool
    {
        if ($module->lessons->isEmpty()) {
            return false;
        }

        $completed = $module->lessons->filter(
            fn (CatalogCourseLesson $lesson) => ($progressMap[$lesson->id] ?? '') === 'completed',
        )->count();

        return match ($module->completion_rule ?? 'all_lessons') {
            'any_lesson' => $completed > 0,
            'manual' => true,
            default => $completed === $module->lessons->count(),
        };
    }

    public function totalLessons(CatalogEnrollment $enrollment): int
    {
        return CatalogCourseLesson::query()
            ->whereHas('module', fn ($q) => $q->where('course_id', $enrollment->course_id))
            ->count();
    }

    public function completedLessons(CatalogEnrollment $enrollment): int
    {
        return CatalogContentProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->count();
    }

    public function recalculateEnrollmentProgress(CatalogEnrollment $enrollment): void
    {
        $total = $this->totalLessons($enrollment);

        if ($total === 0) {
            return;
        }

        $completed = $this->completedLessons($enrollment);
        $percent = (int) round(($completed / $total) * 100);

        $enrollment->update([
            'progress_percent' => $percent,
            'status' => $percent >= 100 ? 'completed' : 'active',
        ]);
    }

    public function markLessonComplete(CatalogEnrollment $enrollment, CatalogCourseLesson $lesson): void
    {
        abort_unless(
            $lesson->module?->course_id === $enrollment->course_id,
            404,
        );

        $module = $lesson->module;
        if ($module) {
            $access = $this->moduleAccessState($enrollment, $module);
            abort_unless($access['accessible'], 403);
        }

        CatalogContentProgress::query()->updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => 'completed',
                'completed_at' => now(),
            ],
        );

        $this->recalculateEnrollmentProgress($enrollment->fresh());
    }

    public function firstLessonId(CatalogEnrollment $enrollment): ?int
    {
        $progressMap = $this->progressMap($enrollment);

        foreach ($this->curriculumForEnrollment($enrollment) as $module) {
            if (! $this->moduleAccessState($enrollment, $module, $progressMap)['accessible']) {
                continue;
            }

            $lesson = $module->lessons->first();

            if ($lesson) {
                return $lesson->id;
            }
        }

        return null;
    }
}
