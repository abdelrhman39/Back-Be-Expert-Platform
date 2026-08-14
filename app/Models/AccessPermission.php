<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessPermission extends Model
{
    protected $fillable = [
        'key',
        'name_ar',
        'description',
        'group_key',
        'scope',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AccessRole::class, 'access_permission_role', 'permission_id', 'role_id');
    }
}
