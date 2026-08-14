<?php

namespace App\Http\Controllers\Sessions;

use App\Http\Controllers\Controller;
use App\Models\SessionRecording;
use App\Services\InstructorService;
use App\Services\SessionRecordingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SessionRecordingController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        SessionRecording $recording,
        SessionRecordingService $recordings,
        InstructorService $instructors,
    ): Response {
        $user = $request->user();
        abort_unless($user, 403);

        $recording->loadMissing('session.section');
        $studentAllowed = $recordings->studentCanView($user, $recording);
        $instructorAllowed = $recording->session
            && $instructors->canAccessSession($user, $recording->session)
            && $user->canInstructor('instructor.recordings.view');

        abort_unless($studentAllowed || $instructorAllowed, 403);
        $recordings->recordView($recording);

        if ($recording->storage_path && $recording->storage_disk) {
            $disk = Storage::disk($recording->storage_disk);

            try {
                return redirect()->away($disk->temporaryUrl($recording->storage_path, now()->addMinutes(15)));
            } catch (\Throwable) {
                abort_unless($disk->exists($recording->storage_path), 404);
                $stream = $disk->readStream($recording->storage_path);
                abort_unless(is_resource($stream), 404);

                return response()->stream(function () use ($stream): void {
                    fpassthru($stream);
                    fclose($stream);
                }, 200, [
                    'Content-Type' => $disk->mimeType($recording->storage_path) ?: 'video/mp4',
                    'Content-Disposition' => 'inline; filename="lecture-recording.mp4"',
                    'Cache-Control' => 'private, no-store',
                ]);
            }
        }

        $url = $recording->play_url ?: $recording->share_url ?: $recording->recording_url;
        abort_unless(filled($url), 404);

        return redirect()->away($url);
    }
}
