<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmStatus extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_initial' => 'boolean',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
            'is_closed' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
