<?php

namespace App\Models;

use App\Services\InstructorImpersonationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicStaff extends Model
{
    protected $table = 'academic_staff';

    protected $fillable = [
        'user_id',
        'name_ar',
        'name_en',
        'role',
        'permission_preset',
        'specialty',
        'gender',
        'courses_count',
        'hours_per_week',
        'compensation_total',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'compensation_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(AcademicSchedule::class, 'staff_id');
    }

    public function zoomHost(): HasOne
    {
        return $this->hasOne(ZoomHost::class);
    }

    public function impersonationBlockReason(): ?string
    {
        return app(InstructorImpersonationService::class)->blockReason($this);
    }

    public function canBeImpersonated(): bool
    {
        return $this->impersonationBlockReason() === null;
    }
}
