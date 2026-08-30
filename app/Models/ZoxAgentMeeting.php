<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoxAgentMeeting extends Model
{
    protected $table = 'zoxagent_meetings';

    protected $fillable = [
        'attendance_session_id',
        'room_id',
        'room_code',
        'join_url',
        'auto_record',
        'last_started_at',
        'last_ended_at',
        'attendance_synced_at',
        'recordings_synced_at',
        'last_synced_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'auto_record' => 'boolean',
            'last_started_at' => 'datetime',
            'last_ended_at' => 'datetime',
            'attendance_synced_at' => 'datetime',
            'recordings_synced_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }
}
