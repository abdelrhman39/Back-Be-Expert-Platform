<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'slug',
        'excerpt',
        'meta_title',
        'meta_description',
        'og_image',
        'body',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
