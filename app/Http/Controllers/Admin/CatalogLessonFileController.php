<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Services\AdminCourseContentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogLessonFileController extends Controller
{
    public function __invoke(
        CatalogCourse $course,
        CatalogCourseLesson $lesson,
        AdminCourseContentService $service,
    ): StreamedResponse {
        abort_unless(auth()->user()?->canAdmin('catalog.manage'), 403);

        abort_unless(
            $lesson->module && (int) $lesson->module->course_id === (int) $course->id,
            404
        );

        $path = $service->lessonFilePath($lesson);
        abort_unless($path, 404);

        return Storage::disk('local')->download($path, $lesson->file_name ?? basename($path));
    }
}
