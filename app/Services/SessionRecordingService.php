<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SessionMaterial;
use App\Models\SessionRecording;
use App\Models\User;
use App\Support\RecordingSettings;

class SessionRecordingService
{
    public function forSession(AttendanceSession $session): ?SessionRecording
    {
        return SessionRecording::query()
            ->where('attendance_session_id', $session->id)
            ->first();
    }

    public function publishedForSession(AttendanceSession $session): ?SessionRecording
    {
        return SessionRecording::query()
            ->where('attendance_session_id', $session->id)
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNotNull('recording_url')
                ->orWhereNotNull('storage_path'))
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function studentCanView(User $user, SessionRecording $recording): bool
    {
        if (! $recording->isPublished() || (! $recording->recording_url && ! $recording->storage_path)) {
            return false;
        }

        if ($recording->expires_at && $recording->expires_at->isPast()) {
            return false;
        }

        $student = $user->academicStudent;
        $session = $recording->session;

        if (! $student || ! $session || $student->section_id !== $session->section_id) {
            return false;
        }

        if (RecordingSettings::accessPolicy() === 'attended_only') {
            $record = AttendanceRecord::query()
                ->where('attendance_session_id', $session->id)
                ->where('student_id', $student->id)
                ->first();

            if (! $record || ! in_array($record->status, ['present', 'late'], true)) {
                return false;
            }
        }

        return true;
    }

    public function setManualUrl(AttendanceSession $session, string $url, User $user): SessionRecording
    {
        $retentionDays = RecordingSettings::retentionDays();

        $recording = SessionRecording::query()->updateOrCreate(
            ['attendance_session_id' => $session->id],
            [
                'teams_meeting_id' => $session->teams_meeting_id,
                'recording_url' => $url,
                'source' => 'teams_manual',
                'status' => 'available',
                'synced_at' => now(),
                'recorded_at' => now(),
                'expires_at' => now()->addDays($retentionDays),
            ],
        );

        return $recording;
    }

    public function publish(SessionRecording $recording, User $publisher): SessionRecording
    {
        $recording->loadMissing('session');

        $recording->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $publisher->id,
        ]);

        $this->ensureMaterial($recording);

        app(NotificationService::class)->notifyRecordingPublished($recording->fresh());

        return $recording->fresh();
    }

    public function hide(SessionRecording $recording): SessionRecording
    {
        $recording->update(['status' => 'hidden']);

        SessionMaterial::query()
            ->where('session_recording_id', $recording->id)
            ->update(['visibility' => 'hidden']);

        return $recording->fresh();
    }

    public function maybeAutoPublish(SessionRecording $recording): void
    {
        if ($recording->status !== 'available' || (! $recording->recording_url && ! $recording->storage_path)) {
            return;
        }

        if (RecordingSettings::publishMode() !== 'auto_delayed') {
            return;
        }

        $hours = RecordingSettings::autoPublishHours();
        $readyAt = ($recording->recorded_at ?? $recording->synced_at)?->copy()->addHours($hours);

        if ($readyAt && now()->greaterThanOrEqualTo($readyAt)) {
            $admin = User::query()->where('role', 'admin')->first();

            if ($admin) {
                $this->publish($recording, $admin);
            }
        }
    }

    public function recordView(SessionRecording $recording): void
    {
        $recording->increment('view_count');
    }

    public function expireDueRecordings(): int
    {
        $expired = SessionRecording::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereNotIn('status', ['expired'])
            ->get();

        foreach ($expired as $recording) {
            $recording->update(['status' => 'expired']);
            SessionMaterial::query()
                ->where('session_recording_id', $recording->id)
                ->update(['visibility' => 'hidden']);
        }

        return $expired->count();
    }

    protected function ensureMaterial(SessionRecording $recording): void
    {
        $session = $recording->session;

        if (! $session) {
            return;
        }

        $provider = $recording->provider ?: (str_starts_with((string) $recording->source, 'zoom') ? 'zoom' : 'teams');

        SessionMaterial::query()->updateOrCreate(
            ['session_recording_id' => $recording->id],
            [
                'attendance_session_id' => $session->id,
                'type' => $provider.'_recording',
                'title' => 'تسجيل المحاضرة — '.$session->displayTitle(),
                'external_url' => $recording->recording_url,
                'visibility' => 'published',
                'published_at' => now(),
                'uploaded_by' => $recording->published_by,
                'sort_order' => 0,
            ],
        );
    }
}
