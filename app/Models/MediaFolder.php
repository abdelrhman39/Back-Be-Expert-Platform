<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MediaFolder extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (MediaFolder $folder): void {
            if (! filled($folder->slug)) {
                $folder->slug = Str::slug($folder->name) ?: 'folder-'.Str::lower(Str::random(6));
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(MediaAsset::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
