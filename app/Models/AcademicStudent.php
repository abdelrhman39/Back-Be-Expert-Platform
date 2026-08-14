<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicStudent extends Model
{
    protected $fillable = [
        'batch_id',
        'section_id',
        'user_id',
        'name_ar',
        'name_en',
        'academic_id',
        'national_id',
        'mobile',
        'email',
        'gender',
        'city',
        'nationality',
        'employment_status',
        'study_period',
        'study_status',
        'academic_status',
        'graduated_at',
        'login_allowed',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'login_allowed' => 'boolean',
            'joined_at' => 'datetime',
            'graduated_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicBatch::class, 'batch_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canBeImpersonated(): bool
    {
        return $this->impersonationBlockReason() === null;
    }

    /** Why «دخول كطالب» is unavailable, or null when allowed. */
    public function impersonationBlockReason(): ?string
    {
        $this->loadMissing('user');

        if (! $this->user) {
            return 'لا يوجد حساب بوابة مرتبط بهذا الطالب.';
        }

        if ($this->user->status !== 'active') {
            return 'حساب البوابة غير مفعّل.';
        }

        if (! $this->login_allowed) {
            return 'الدخول موقوف لهذا الطالب.';
        }

        return null;
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'student_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'academic_student_id');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name_ar)) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($parts[0] ?? 'ط', 0, 1));
    }

    /** @return Builder<Order> */
    public function ordersQuery()
    {
        if ($this->user_id) {
            return Order::query()->where('user_id', $this->user_id);
        }

        if ($this->email) {
            $userId = User::query()->where('email', $this->email)->value('id');

            if ($userId) {
                return Order::query()->where('user_id', $userId);
            }
        }

        return Order::query()->whereRaw('0 = 1');
    }

    /** @return Collection<int, AcademicCourse> */
    public function programCourses()
    {
        $program = $this->batch?->program;

        if (! $program) {
            return collect();
        }

        return $program->courses()->with('level')->orderBy('code')->get();
    }
}
