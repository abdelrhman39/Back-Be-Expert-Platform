<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fellowship extends Model
{
    protected $fillable = [
        'slug',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'status',
        'application_open',
        'legacy_slug',
        'sort_order',
        'form_fields',
        'file_upload_settings',
    ];

    protected function casts(): array
    {
        return [
            'application_open' => 'boolean',
            'form_fields' => 'array',
            'file_upload_settings' => 'array',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(RegistrationApplication::class);
    }

    public function displayTitle(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'en' && filled($this->title_en)) {
            return $this->title_en;
        }

        return $this->title_ar;
    }

    public function displayDescription(): ?string
    {
        $locale = app()->getLocale();

        if ($locale === 'en' && filled($this->description_en)) {
            return $this->description_en;
        }

        return $this->description_ar;
    }

    public function acceptsApplications(): bool
    {
        return $this->application_open && $this->status === 'open';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
