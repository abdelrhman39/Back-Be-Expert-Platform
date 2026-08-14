<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'order_item_id',
        'delivery_type',
        'status',
        'progress_percent',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
            'enrolled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(CatalogCourse::class, 'course_id');
    }

    public function contentProgress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CatalogContentProgress::class, 'enrollment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function displayTitle(): string
    {
        return $this->course?->title_ar
            ?: $this->course?->title_en
            ?: $this->orderItem?->course_title
            ?: 'دورة #'.$this->course_id;
    }

    public function displayImage(): ?string
    {
        return $this->course?->image ?: $this->orderItem?->course_image;
    }
}
