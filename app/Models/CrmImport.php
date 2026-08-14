<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmImport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'errors' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class, 'import_id');
    }
}
