<?php

namespace App\Models;

use App\Support\AccessControl;
use App\Support\AdminPermissions;
use App\Support\InstructorPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'name_ar',
    'email',
    'phone',
    'phone_verified_at',
    'national_id',
    'password',
    'locale',
    'notification_preferences',
    'status',
    'role',
    'failed_login_attempts',
    'locked_until',
    'last_login_at',
    'last_login_method',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, \Illuminate\Notifications\HasDatabaseNotifications, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
        ];
    }

    public function displayName(): string
    {
        return $this->name_ar ?: $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function canAdmin(string $permission): bool
    {
        return AdminPermissions::can($this, $permission);
    }

    public function canInstructor(string $permission): bool
    {
        return InstructorPermissions::can($this, $permission);
    }

    public function accessRoles(): BelongsToMany
    {
        return $this->belongsToMany(AccessRole::class, 'access_role_user', 'user_id', 'role_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(AccessPermission::class, 'access_user_permissions', 'user_id', 'permission_id')
            ->withPivot(['effect', 'assigned_by'])
            ->withTimestamps();
    }

    public function canAccess(string $permission): bool
    {
        return AccessControl::can($this, $permission);
    }

    public function academicStaff(): HasOne
    {
        return $this->hasOne(AcademicStaff::class, 'user_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function academicStudent(): HasOne
    {
        return $this->hasOne(AcademicStudent::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function installmentContracts(): HasMany
    {
        return $this->hasMany(InstallmentContract::class);
    }

    public function catalogEnrollments(): HasMany
    {
        return $this->hasMany(CatalogEnrollment::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function microsoftTeamsConnection(): HasOne
    {
        return $this->hasOne(MicrosoftTeamsConnection::class);
    }

    public function initials(): string
    {
        $name = trim($this->displayName());
        $parts = preg_split('/\s+/u', $name) ?: [];

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($parts[0] ?? 'م', 0, 1));
    }
}
