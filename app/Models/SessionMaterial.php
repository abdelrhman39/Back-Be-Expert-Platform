<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SessionMaterial extends Model
{
    protected $fillable = [
        'attendance_session_id',
        'type',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'external_url',
        'session_recording_id',
        'sort_order',
        'visibility',
        'uploaded_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function sessionRecording(): BelongsTo
    {
        return $this->belongsTo(SessionRecording::class, 'session_recording_id');
    }

    public function isPublished(): bool
    {
        return $this->visibility === 'published';
    }

    public function downloadUrl(): ?string
    {
        if ($this->type === 'link' || $this->type === 'video_embed' || $this->type === 'teams_recording') {
            return $this->external_url;
        }

        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function formattedSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        if ($this->file_size >= 1048576) {
            return round($this->file_size / 1048576, 1).' MB';
        }

        return round($this->file_size / 1024).' KB';
    }
}
