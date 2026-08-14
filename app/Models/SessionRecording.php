<?php

namespace App\Models;

use App\Support\RecordingOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionRecording extends Model
{
    protected $fillable = [
        'attendance_session_id',
        'teams_meeting_id',
        'graph_recording_id',
        'recording_url',
        'download_url',
        'duration_seconds',
        'file_size_bytes',
        'recorded_at',
        'synced_at',
        'source',
        'provider',
        'external_recording_id',
        'share_url',
        'play_url',
        'recording_passcode',
        'storage_destination',
        'storage_disk',
        'storage_path',
        'provider_payload',
        'status',
        'published_at',
        'published_by',
        'expires_at',
        'view_count',
        'raw_graph_payload',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'synced_at' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'raw_graph_payload' => 'array',
            'recording_passcode' => 'encrypted',
            'provider_payload' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function material(): HasOne
    {
        return $this->hasOne(SessionMaterial::class, 'session_recording_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isAvailable(): bool
    {
        return in_array($this->status, ['available', 'published'], true)
            && (filled($this->recording_url) || filled($this->storage_path));
    }

    public function formattedDuration(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $hours = intdiv($this->duration_seconds, 3600);
        $minutes = intdiv($this->duration_seconds % 3600, 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function statusLabel(): string
    {
        return RecordingOptions::statusLabel($this->status);
    }
}
