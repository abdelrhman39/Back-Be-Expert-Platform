<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPlanTemplateItem extends Model
{
    protected $fillable = [
        'template_id',
        'sequence',
        'percent',
        'due_rule',
        'month_offset',
        'label_ar',
        'label_en',
    ];

    protected function casts(): array
    {
        return [
            'percent' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlanTemplate::class, 'template_id');
    }

    public function displayLabel(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'en' && filled($this->label_en)) {
            return $this->label_en;
        }

        return $this->label_ar ?: (($locale === 'en' ? 'Installment ' : 'قسط ').$this->sequence);
    }
}
