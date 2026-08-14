<?php

namespace App\Models;

use App\Support\InstallmentOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentContract extends Model
{
    protected $fillable = [
        'contract_no',
        'user_id',
        'academic_student_id',
        'program_id',
        'batch_id',
        'template_id',
        'dunning_policy_id',
        'title',
        'total_amount',
        'paid_amount',
        'remaining_balance',
        'currency',
        'status',
        'starts_at',
        'signed_at',
        'student_signed_at',
        'student_signature_path',
        'student_signature_name',
        'student_signature_ip',
        'requires_student_signature',
        'completed_at',
        'suspended_at',
        'suspension_reason',
        'dunning_restrictions',
        'admin_notes',
        'checkout_items',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'starts_at' => 'date',
            'signed_at' => 'datetime',
            'student_signed_at' => 'datetime',
            'requires_student_signature' => 'boolean',
            'completed_at' => 'datetime',
            'suspended_at' => 'datetime',
            'checkout_items' => 'array',
            'dunning_restrictions' => 'array',
        ];
    }

    public function isStudentSigned(): bool
    {
        if (! $this->requires_student_signature) {
            return true;
        }

        return $this->student_signed_at !== null && filled($this->student_signature_path);
    }

    public function needsStudentSignature(): bool
    {
        return $this->requires_student_signature
            && ! $this->isStudentSigned()
            && in_array($this->status, ['pending_signature', 'active'], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(AcademicStudent::class, 'academic_student_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicBatch::class, 'batch_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlanTemplate::class, 'template_id');
    }

    public function dunningPolicy(): BelongsTo
    {
        return $this->belongsTo(InstallmentDunningPolicy::class, 'dunning_policy_id');
    }

    public function hasDunningRestriction(string $key): bool
    {
        return (bool) (($this->dunning_restrictions ?? [])[$key] ?? false);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(InstallmentSchedule::class, 'contract_id')->orderBy('sequence');
    }

    public function statusLabel(): string
    {
        return InstallmentOptions::contractStatusLabel($this->status);
    }

    public function progressPercent(): float
    {
        if ((float) $this->total_amount <= 0) {
            return 0;
        }

        return min(100, round(((float) $this->paid_amount / (float) $this->total_amount) * 100, 1));
    }

    public function nextDueSchedule(): ?InstallmentSchedule
    {
        return $this->schedules()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->orderBy('sequence')
            ->first();
    }
}
