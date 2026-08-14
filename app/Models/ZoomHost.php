<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZoomHost extends Model
{
    protected $fillable = [
        'academic_staff_id', 'zoom_user_id', 'email', 'license_type', 'is_active',
        'priority', 'pool', 'last_synced_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function academicStaff(): BelongsTo
    {
        return $this->belongsTo(AcademicStaff::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ZoomMeeting::class);
    }
}
