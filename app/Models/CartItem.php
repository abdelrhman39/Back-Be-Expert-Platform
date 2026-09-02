<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'course_id',
        'training_id',
        'delivery_type',
        'price_snapshot',
        'course_title',
        'course_image',
        'course_slug',
    ];

    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function deliveryLabel(): string
    {
        $locale = app()->getLocale();

        return match ($this->delivery_type) {
            'online' => \App\Support\PublicCopy::cart('online', $locale),
            'onsite' => \App\Support\PublicCopy::cart('onsite', $locale),
            default => $this->delivery_type,
        };
    }
}
