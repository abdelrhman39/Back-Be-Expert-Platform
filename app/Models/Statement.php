<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statement extends Model
{
    protected $fillable = [
        'reference_no',
        'user_id',
        'academic_student_id',
        'type',
        'title',
        'status',
        'student_notes',
        'admin_notes',
        'payload',
        'requested_at',
        'issued_at',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'requested_at' => 'datetime',
            'issued_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicStudent(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
