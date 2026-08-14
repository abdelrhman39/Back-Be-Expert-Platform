<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseModule;
use App\Services\AdminCourseContentService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogModuleImageController extends Controller
{
    public function __invoke(
        CatalogCourse $course,
        CatalogCourseModule $module,
        AdminCourseContentService $service,
    ): StreamedResponse {
        abort_unless(
            auth()->user()?->canAdmin('catalog.manage') || auth()->user()?->canAdmin('catalog.view'),
            403
        );
        abort_unless((int) $module->course_id === (int) $course->id, 404);

        $path = $service->moduleImagePath($module);
        abort_unless($path, 404);

        return Storage::disk('local')->response($path, $module->image_name ?? basename($path));
    }
}
