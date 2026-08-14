<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessRole extends Model
{
    protected $fillable = [
        'key',
        'name_ar',
        'name_en',
        'description',
        'scope',
        'is_system',
        'is_super',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_super' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AccessPermission::class, 'access_permission_role', 'role_id', 'permission_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'access_role_user', 'role_id', 'user_id')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }
}
