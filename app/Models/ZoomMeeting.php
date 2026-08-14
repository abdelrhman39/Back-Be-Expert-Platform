<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZoomMeeting extends Model
{
    protected $fillable = [
        'attendance_session_id', 'zoom_host_id', 'meeting_id', 'meeting_uuid', 'join_url',
        'start_url', 'passcode', 'registration_mode', 'recording_mode', 'status',
        'alternative_hosts', 'settings', 'raw_payload', 'last_synced_at',
        'attendance_synced_at', 'recordings_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'start_url' => 'encrypted',
            'passcode' => 'encrypted',
            'alternative_hosts' => 'array',
            'settings' => 'array',
            'raw_payload' => 'array',
            'last_synced_at' => 'datetime',
            'attendance_synced_at' => 'datetime',
            'recordings_synced_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(ZoomHost::class, 'zoom_host_id');
    }

    public function registrants(): HasMany
    {
        return $this->hasMany(ZoomRegistrant::class);
    }
}
