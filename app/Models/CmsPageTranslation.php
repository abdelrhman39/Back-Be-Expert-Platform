<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPageTranslation extends Model
{
    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'meta_title',
        'meta_description',
        'og_image',
        'body',
        'blocks',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }
}
