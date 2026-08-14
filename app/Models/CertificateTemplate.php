<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'background_disk',
        'background_path',
        'thumbnail_path',
        'canvas_width',
        'canvas_height',
        'orientation',
        'elements',
        'settings',
        'status',
        'is_default',
        'version',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'elements' => 'array',
            'settings' => 'array',
            'is_default' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function backgroundUrl(): ?string
    {
        if (! $this->background_path) {
            return null;
        }

        try {
            return Storage::disk($this->background_disk ?: 'public')->url($this->background_path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
