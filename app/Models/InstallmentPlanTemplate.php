<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class InstallmentPlanTemplate extends Model
{
    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'program_type',
        'max_installments',
        'min_down_payment_percent',
        'is_active',
        'description_ar',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'min_down_payment_percent' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InstallmentPlanTemplateItem::class, 'template_id')->orderBy('sequence');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(InstallmentContract::class, 'template_id');
    }

    public function academicPrograms(): BelongsToMany
    {
        return $this->belongsToMany(
            AcademicProgram::class,
            'installment_plan_template_programs',
            'template_id',
            'academic_program_id',
        )->withTimestamps();
    }

    public function catalogCourses(): BelongsToMany
    {
        return $this->belongsToMany(
            CatalogCourse::class,
            'installment_plan_template_courses',
            'template_id',
            'catalog_course_id',
        )->withTimestamps();
    }

    public function totalPercent(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum('percent');
        }

        return (float) $this->items()->sum('percent');
    }

    /** Empty links = plan is global (available for any cart item). */
    public function isGlobalScope(): bool
    {
        $programCount = $this->relationLoaded('academicPrograms')
            ? $this->academicPrograms->count()
            : $this->academicPrograms()->count();

        $courseCount = $this->relationLoaded('catalogCourses')
            ? $this->catalogCourses->count()
            : $this->catalogCourses()->count();

        return $programCount === 0 && $courseCount === 0;
    }

    /**
     * @param  Collection<int, int>  $courseIds
     * @param  Collection<int, int>  $programIds
     */
    public function appliesToCart(Collection $courseIds, Collection $programIds): bool
    {
        if ($this->isGlobalScope()) {
            return true;
        }

        $linkedCourseIds = $this->relationLoaded('catalogCourses')
            ? $this->catalogCourses->pluck('id')
            : $this->catalogCourses()->pluck('catalog_courses.id');

        if ($linkedCourseIds->intersect($courseIds)->isNotEmpty()) {
            return true;
        }

        $linkedProgramIds = $this->relationLoaded('academicPrograms')
            ? $this->academicPrograms->pluck('id')
            : $this->academicPrograms()->pluck('academic_programs.id');

        return $linkedProgramIds->intersect($programIds)->isNotEmpty();
    }

    /** @return array<int, array{sequence: int, label: string, percent: float, amount: float, is_first: bool}> */
    public function schedulePreview(float $totalAmount): array
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        $rows = [];
        $allocated = 0.0;
        $count = $items->count();

        foreach ($items->values() as $index => $item) {
            $isLast = $index === $count - 1;
            $amount = $isLast
                ? round($totalAmount - $allocated, 2)
                : round($totalAmount * ((float) $item->percent / 100), 2);
            $allocated += $amount;

            $rows[] = [
                'sequence' => (int) $item->sequence,
                'label' => $item->displayLabel(),
                'percent' => (float) $item->percent,
                'amount' => $amount,
                'is_first' => $index === 0,
            ];
        }

        return $rows;
    }
}
