<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmContact extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'do_not_contact' => 'boolean',
            'assigned_at' => 'datetime',
            'first_contacted_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
            'lost_at' => 'datetime',
            'payment_receipt_uploaded_at' => 'datetime',
            'paid_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function hasPaymentReceipt(): bool
    {
        return filled($this->payment_receipt_path);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(CrmImport::class, 'import_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'contact_id');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if (! \App\Support\CrmAccess::canViewAll($user)) {
            $query->where('owner_id', $user->id);
        }

        return $query;
    }

    public function isOverdue(): bool
    {
        return $this->next_follow_up_at?->isPast()
            && ! \App\Support\CrmOptions::isClosed($this->status);
    }
}
