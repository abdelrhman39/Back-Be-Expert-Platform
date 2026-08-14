<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationApplication;
use App\Services\RegistrationApplicationService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationApplicationAttachmentController extends Controller
{
    public function __invoke(
        RegistrationApplication $application,
        string $key,
        RegistrationApplicationService $service,
    ): StreamedResponse {
        abort_unless(auth()->user()?->canAdmin('applications.view'), 403);

        $path = $service->attachmentDownloadPath($application, $key);

        abort_unless($path, 404);

        $meta = data_get($application->attachments, $key, []);
        $filename = $meta['original'] ?? basename($path);

        return Storage::disk('local')->download($path, $filename);
    }
}
