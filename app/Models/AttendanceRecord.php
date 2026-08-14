<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'status',
        'source',
        'provider',
        'external_participant_id',
        'attendance_seconds',
        'joined_at',
        'left_at',
        'provider_payload',
        'recorded_by',
        'notes',
        'teams_attendance_seconds',
        'teams_joined_at',
    ];

    protected function casts(): array
    {
        return [
            'teams_joined_at' => 'datetime',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'provider_payload' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class, 'student_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
