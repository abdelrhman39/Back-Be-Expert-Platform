<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CatalogCourseLesson;
use App\Models\CatalogEnrollment;
use App\Services\AdminCourseContentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogLessonFileController extends Controller
{
    public function __invoke(
        CatalogEnrollment $enrollment,
        CatalogCourseLesson $lesson,
        AdminCourseContentService $service,
    ): StreamedResponse {
        abort_unless(auth()->check(), 403);
        abort_unless($enrollment->user_id === auth()->id(), 403);
        abort_unless(in_array($enrollment->status, ['active', 'completed'], true), 404);

        abort_unless(
            $lesson->module && (int) $lesson->module->course_id === (int) $enrollment->course_id,
            404
        );

        $path = $service->lessonFilePath($lesson);
        abort_unless($path, 404);

        return Storage::disk('local')->download($path, $lesson->file_name ?? basename($path));
    }
}
