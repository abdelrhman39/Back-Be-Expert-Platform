<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstallmentDunningPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_default',
        'process_time',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(InstallmentDunningStep::class, 'policy_id')->orderBy('sort_order');
    }

    public function executions(): HasMany
    {
        return $this->hasMany(InstallmentDunningExecution::class, 'policy_id');
    }

    public static function defaultPolicy(): ?self
    {
        return static::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->with('steps')
            ->first()
            ?? static::query()->where('is_active', true)->with('steps')->orderBy('id')->first();
    }
}
