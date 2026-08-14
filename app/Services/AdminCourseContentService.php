<?php

namespace App\Services;

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogCourseModule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminCourseContentService
{
    /** @return \Illuminate\Support\Collection<int, CatalogCourseModule> */
    public function curriculum(CatalogCourse $course)
    {
        return CatalogCourseModule::query()
            ->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])
            ->where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get();
    }

    public function stats(CatalogCourse $course): array
    {
        $modules = CatalogCourseModule::query()->where('course_id', $course->id)->count();
        $lessons = CatalogCourseLesson::query()
            ->whereHas('module', fn ($q) => $q->where('course_id', $course->id))
            ->count();

        return [
            'modules' => $modules,
            'lessons' => $lessons,
        ];
    }

    public function createModule(CatalogCourse $course, array $data, ?UploadedFile $image = null): CatalogCourseModule
    {
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if ($sortOrder <= 0) {
            $sortOrder = (int) CatalogCourseModule::query()
                ->where('course_id', $course->id)
                ->max('sort_order') + 1;
        }

        [$imagePath, $imageName] = [null, null];

        $module = CatalogCourseModule::query()->create($this->modulePayload($course->id, $data, $imagePath, $imageName, $sortOrder));

        if ($image) {
            [$imagePath, $imageName] = $this->storeModuleImage($module, $image);
            $module->update(['image_path' => $imagePath, 'image_name' => $imageName]);
        }

        return $module->fresh();
    }

    public function updateModule(CatalogCourseModule $module, array $data, ?UploadedFile $image = null, bool $removeImage = false): CatalogCourseModule
    {
        $imagePath = $module->image_path;
        $imageName = $module->image_name;

        if ($removeImage && $imagePath) {
            Storage::disk('local')->delete($imagePath);
            $imagePath = null;
            $imageName = null;
        }

        if ($image) {
            if ($imagePath) {
                Storage::disk('local')->delete($imagePath);
            }
            [$imagePath, $imageName] = $this->storeModuleImage($module, $image);
        }

        $module->update(array_merge(
            $this->modulePayload($module->course_id, $data, $imagePath, $imageName, (int) ($data['sort_order'] ?? $module->sort_order)),
            ['image_path' => $imagePath, 'image_name' => $imageName],
        ));

        return $module->fresh();
    }

    public function deleteModule(CatalogCourseModule $module): void
    {
        abort_unless($module->course_id, 404);
        $module->delete();
    }

    public function createLesson(CatalogCourseModule $module, array $data, ?UploadedFile $file = null): CatalogCourseLesson
    {
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        if ($sortOrder <= 0) {
            $sortOrder = (int) CatalogCourseLesson::query()
                ->where('module_id', $module->id)
                ->max('sort_order') + 1;
        }

        [$filePath, $fileName] = $this->storeLessonFile($module, $file);

        return CatalogCourseLesson::query()->create(array_merge(
            $this->lessonPayload($data, $sortOrder),
            [
                'module_id' => $module->id,
                'file_path' => $filePath,
                'file_name' => $fileName,
            ],
        ));
    }

    public function updateLesson(CatalogCourseLesson $lesson, array $data, ?UploadedFile $file = null, bool $removeFile = false): CatalogCourseLesson
    {
        $filePath = $lesson->file_path;
        $fileName = $lesson->file_name;

        if ($removeFile && $filePath) {
            Storage::disk('local')->delete($filePath);
            $filePath = null;
            $fileName = null;
        }

        if ($file) {
            if ($filePath) {
                Storage::disk('local')->delete($filePath);
            }
            [$filePath, $fileName] = $this->storeLessonFile($lesson->module, $file);
        }

        $lesson->update(array_merge(
            $this->lessonPayload($data, (int) ($data['sort_order'] ?? $lesson->sort_order)),
            [
                'file_path' => $filePath,
                'file_name' => $fileName,
            ],
        ));

        return $lesson->fresh();
    }

    public function deleteLesson(CatalogCourseLesson $lesson): void
    {
        $lesson->delete();
    }

    public function moveModule(CatalogCourseModule $module, string $direction): void
    {
        $siblings = CatalogCourseModule::query()
            ->where('course_id', $module->course_id)
            ->orderBy('sort_order')
            ->get();

        $this->swapSortOrder($siblings, $module->id, $direction);
    }

    public function moveLesson(CatalogCourseLesson $lesson, string $direction): void
    {
        $siblings = CatalogCourseLesson::query()
            ->where('module_id', $lesson->module_id)
            ->orderBy('sort_order')
            ->get();

        $this->swapSortOrder($siblings, $lesson->id, $direction);
    }

    /** @param  array<int>  $orderedIds */
    public function reorderModules(CatalogCourse $course, array $orderedIds): void
    {
        DB::transaction(function () use ($course, $orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                CatalogCourseModule::query()
                    ->where('course_id', $course->id)
                    ->whereKey($id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /** @param  array<int>  $orderedIds */
    public function reorderLessons(CatalogCourseModule $module, array $orderedIds): void
    {
        DB::transaction(function () use ($module, $orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                CatalogCourseLesson::query()
                    ->where('module_id', $module->id)
                    ->whereKey($id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    public function duplicateModule(CatalogCourseModule $module): CatalogCourseModule
    {
        return DB::transaction(function () use ($module) {
            $copy = CatalogCourseModule::query()->create(
                $this->modulePayload(
                    $module->course_id,
                    [
                        'title_ar' => $module->title_ar.' (نسخة)',
                        'title_en' => $module->title_en,
                        'code' => $module->code ? $module->code.'-copy' : null,
                        'summary_ar' => $module->summary_ar,
                        'summary_en' => $module->summary_en,
                        'description_ar' => $module->description_ar,
                        'description_en' => $module->description_en,
                        'objectives_ar' => $module->objectives_ar,
                        'objectives_en' => $module->objectives_en,
                        'status' => 'draft',
                        'is_optional' => $module->is_optional,
                        'estimated_duration_minutes' => $module->estimated_duration_minutes,
                        'prerequisite_module_ids' => $module->prerequisite_module_ids,
                        'drip_days' => $module->drip_days,
                        'unlock_at' => $module->unlock_at,
                        'completion_rule' => $module->completion_rule,
                        'icon' => $module->icon,
                        'meta_title_ar' => $module->meta_title_ar,
                        'meta_title_en' => $module->meta_title_en,
                        'meta_description_ar' => $module->meta_description_ar,
                        'meta_description_en' => $module->meta_description_en,
                        'notes_internal' => $module->notes_internal,
                    ],
                    null,
                    null,
                    (int) CatalogCourseModule::query()
                        ->where('course_id', $module->course_id)
                        ->max('sort_order') + 1,
                ),
            );

            foreach ($module->lessons()->orderBy('sort_order')->get() as $lesson) {
                CatalogCourseLesson::query()->create(array_merge(
                    $this->lessonPayload([
                        'title_ar' => $lesson->title_ar,
                        'title_en' => $lesson->title_en,
                        'code' => $lesson->code ? $lesson->code.'-copy' : null,
                        'summary_ar' => $lesson->summary_ar,
                        'summary_en' => $lesson->summary_en,
                        'type' => $lesson->type,
                        'status' => 'draft',
                        'is_preview' => $lesson->is_preview,
                        'completion_required' => $lesson->completion_required,
                        'body_ar' => $lesson->body_ar,
                        'body_en' => $lesson->body_en,
                        'external_url' => $lesson->external_url,
                        'video_provider' => $lesson->video_provider,
                        'resource_url' => $lesson->resource_url,
                        'duration_minutes' => $lesson->duration_minutes,
                        'notes_internal' => $lesson->notes_internal,
                        'meta_title_ar' => $lesson->meta_title_ar,
                        'meta_title_en' => $lesson->meta_title_en,
                        'meta_description_ar' => $lesson->meta_description_ar,
                        'meta_description_en' => $lesson->meta_description_en,
                    ], $lesson->sort_order),
                    [
                        'module_id' => $copy->id,
                        'file_path' => $lesson->file_path,
                        'file_name' => $lesson->file_name,
                    ],
                ));
            }

            return $copy;
        });
    }

    protected function storeLessonFile(CatalogCourseModule $module, ?UploadedFile $file): array
    {
        if (! $file) {
            return [null, null];
        }

        $courseId = $module->course_id;
        $path = $file->store("catalog/courses/{$courseId}/lessons", 'local');

        return [$path, $file->getClientOriginalName()];
    }

    protected function storeModuleImage(CatalogCourseModule $module, UploadedFile $file): array
    {
        $path = $file->store("catalog/courses/{$module->course_id}/modules/{$module->id}", 'local');

        return [$path, $file->getClientOriginalName()];
    }

    public function moduleImagePath(CatalogCourseModule $module): ?string
    {
        if (! $module->image_path || ! Storage::disk('local')->exists($module->image_path)) {
            return null;
        }

        return $module->image_path;
    }

    /** @return array<string, mixed> */
    protected function lessonPayload(array $data, int $sortOrder): array
    {
        $type = $data['type'] ?? 'html';
        $videoProvider = $data['video_provider'] ?? null;
        $externalUrl = $data['external_url'] ?? null;

        if ($type === 'video' && filled($externalUrl)) {
            $externalUrl = \App\Support\CourseContentOptions::normalizeVideoEmbedUrl($externalUrl, $videoProvider);
        }

        return [
            'title_ar' => $data['title_ar'],
            'title_en' => $data['title_en'] ?? null,
            'code' => $data['code'] ?? null,
            'summary_ar' => $data['summary_ar'] ?? null,
            'summary_en' => $data['summary_en'] ?? null,
            'type' => $type,
            'status' => $data['status'] ?? 'published',
            'is_preview' => (bool) ($data['is_preview'] ?? false),
            'completion_required' => (bool) ($data['completion_required'] ?? true),
            'body_ar' => $data['body_ar'] ?? null,
            'body_en' => $data['body_en'] ?? null,
            'external_url' => $externalUrl,
            'video_provider' => $videoProvider,
            'resource_url' => $data['resource_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'notes_internal' => $data['notes_internal'] ?? null,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_title_en' => $data['meta_title_en'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'meta_description_en' => $data['meta_description_en'] ?? null,
            'sort_order' => $sortOrder,
        ];
    }

    /** @return array<string, mixed> */
    protected function modulePayload(int $courseId, array $data, ?string $imagePath, ?string $imageName, int $sortOrder): array
    {
        return [
            'course_id' => $courseId,
            'title_ar' => $data['title_ar'],
            'title_en' => $data['title_en'] ?? null,
            'code' => $data['code'] ?? null,
            'summary_ar' => $data['summary_ar'] ?? null,
            'summary_en' => $data['summary_en'] ?? null,
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'objectives_ar' => $data['objectives_ar'] ?? null,
            'objectives_en' => $data['objectives_en'] ?? null,
            'status' => $data['status'] ?? 'published',
            'is_optional' => (bool) ($data['is_optional'] ?? false),
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'prerequisite_module_ids' => array_values(array_filter(
                array_map('intval', $data['prerequisite_module_ids'] ?? []),
                fn (int $id) => $id > 0,
            )) ?: null,
            'drip_days' => $data['drip_days'] ?? null,
            'unlock_at' => $data['unlock_at'] ?? null,
            'completion_rule' => $data['completion_rule'] ?? 'all_lessons',
            'icon' => $data['icon'] ?? null,
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'meta_title_ar' => $data['meta_title_ar'] ?? null,
            'meta_title_en' => $data['meta_title_en'] ?? null,
            'meta_description_ar' => $data['meta_description_ar'] ?? null,
            'meta_description_en' => $data['meta_description_en'] ?? null,
            'notes_internal' => $data['notes_internal'] ?? null,
            'sort_order' => $sortOrder,
        ];
    }

    public function lessonFilePath(CatalogCourseLesson $lesson): ?string
    {
        if (! $lesson->file_path || ! Storage::disk('local')->exists($lesson->file_path)) {
            return null;
        }

        return $lesson->file_path;
    }

    /** @param \Illuminate\Support\Collection<int, CatalogCourseModule|CatalogCourseLesson> $items */
    protected function swapSortOrder($items, int $id, string $direction): void
    {
        $index = $items->search(fn ($item) => $item->id === $id);

        if ($index === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($targetIndex < 0 || $targetIndex >= $items->count()) {
            return;
        }

        DB::transaction(function () use ($items, $index, $targetIndex) {
            $current = $items[$index];
            $target = $items[$targetIndex];
            $currentOrder = $current->sort_order;
            $targetOrder = $target->sort_order;

            $current->update(['sort_order' => $targetOrder]);
            $target->update(['sort_order' => $currentOrder]);
        });
    }
}
