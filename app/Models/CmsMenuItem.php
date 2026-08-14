<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsMenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'sort_order',
        'label_ar',
        'label_en',
        'link_type',
        'route_name',
        'page_id',
        'url',
        'open_in_new_tab',
        'is_active',
        'permission',
    ];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en' && filled($this->label_en)) {
            return $this->label_en;
        }

        return $this->label_ar;
    }
}
