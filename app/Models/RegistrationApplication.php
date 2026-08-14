<?php

namespace App\Models;

use App\Support\RegistrationApplicationOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationApplication extends Model
{
    protected $fillable = [
        'application_no',
        'type',
        'status',
        'user_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'approved_role',
        'course_name',
        'course_id',
        'fellowship_id',
        'payload',
        'attachments',
        'reviewer_id',
        'reviewed_at',
        'admin_notes',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attachments' => 'array',
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function fellowship(): BelongsTo
    {
        return $this->belongsTo(Fellowship::class);
    }

    public function typeLabel(): string
    {
        return RegistrationApplicationOptions::typeLabel($this->type);
    }

    public function statusLabel(): string
    {
        return RegistrationApplicationOptions::statusLabel($this->status);
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->payload, $key, $default);
    }

    public function canReview(): bool
    {
        return in_array($this->status, ['pending', 'under_review'], true);
    }

    public function listRoute(): string
    {
        return RegistrationApplicationOptions::listRoute($this->type);
    }
}
