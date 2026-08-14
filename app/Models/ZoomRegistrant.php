<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoomRegistrant extends Model
{
    protected $fillable = [
        'zoom_meeting_id', 'student_id', 'registrant_id', 'email', 'join_url',
        'status', 'raw_payload', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'join_url' => 'encrypted',
            'raw_payload' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(ZoomMeeting::class, 'zoom_meeting_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class);
    }
}
