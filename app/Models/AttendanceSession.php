<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceSession extends Model
{
    protected $fillable = [
        'section_id',
        'schedule_id',
        'title',
        'session_number',
        'description',
        'session_date',
        'time_start',
        'time_end',
        'meeting_url',
        'teams_meeting_id',
        'teams_join_web_url',
        'teams_organizer_id',
        'teams_attendance_synced_at',
        'status',
        'source',
        'notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'published_at' => 'datetime',
            'teams_attendance_synced_at' => 'datetime',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AcademicSchedule::class, 'schedule_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(SessionMaterial::class, 'attendance_session_id')->orderBy('sort_order');
    }

    public function publishedMaterials(): HasMany
    {
        return $this->materials()->where('visibility', 'published');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'attendance_session_id');
    }

    public function recording(): HasOne
    {
        return $this->hasOne(SessionRecording::class, 'attendance_session_id');
    }

    public function zoomMeeting(): HasOne
    {
        return $this->hasOne(ZoomMeeting::class, 'attendance_session_id');
    }

    public function displayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->session_number) {
            return 'الحصة '.$this->session_number;
        }

        return 'جلسة '.$this->session_date->format('Y-m-d');
    }

    public function startsAt(): ?Carbon
    {
        $time = $this->time_start ?? $this->section?->schedule?->time_start;

        return $this->combineDateAndTime($this->session_date, $time);
    }

    public function endsAt(): ?Carbon
    {
        $time = $this->time_end ?? $this->section?->schedule?->time_end;
        $endsAt = $this->combineDateAndTime($this->session_date, $time);
        $startsAt = $this->startsAt();

        if ($endsAt && $startsAt && $endsAt->lte($startsAt)) {
            return $startsAt->copy()->addHours(2);
        }

        return $endsAt;
    }

    protected function combineDateAndTime(Carbon|string $date, ?string $time): ?Carbon
    {
        if (! $time) {
            return null;
        }

        $dateStr = $date instanceof Carbon ? $date->toDateString() : (string) $date;

        return Carbon::parse($dateStr.' '.substr($time, 0, 5));
    }
}
