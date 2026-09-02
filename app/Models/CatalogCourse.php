<?php

namespace App\Models;

use App\Services\CatalogCourseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CatalogCourse extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'academic_program_id',
        'title_ar',
        'title_en',
        'slug',
        'image',
        'price_online',
        'price_onsite',
        'delivery_type',
        'duration_hours',
        'duration_days',
        'duration_label',
        'city',
        'is_self_learning',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price_online' => 'decimal:2',
            'price_onsite' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_self_learning' => 'boolean',
            'duration_hours' => 'integer',
            'duration_days' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        if ($field === 'id' || is_numeric($value)) {
            $query = static::query()->whereKey((int) $value);

            if (! request()->is('admin', 'admin/*')) {
                $query->where('status', 'published');
            }

            return $query->first();
        }

        $course = app(CatalogCourseService::class)->findPublishedBySlug((string) $value);

        if ($course !== null) {
            return $course;
        }

        if (request()->is('admin', 'admin/*')) {
            $slug = trim((string) $value);
            $candidates = array_unique([
                $slug,
                str_ends_with($slug, '.html') ? $slug : $slug.'.html',
                str_replace('.html', '', $slug),
            ]);

            return static::query()->whereIn('slug', $candidates)->first();
        }

        return null;
    }

    public function details(): HasOne
    {
        return $this->hasOne(CatalogCourseDetail::class, 'course_id');
    }

    public function academicProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'academic_program_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CatalogCourseModule::class, 'course_id')->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(CatalogCategory::class, 'catalog_category_course', 'course_id', 'category_id');
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(CatalogField::class, 'catalog_field_course', 'course_id', 'field_id');
    }

    public function primaryCategory(): ?CatalogCategory
    {
        return $this->relationLoaded('categories')
            ? $this->categories->first()
            : $this->categories()->orderBy('sort_order')->first();
    }

    public function displayTitle(): string
    {
        $locale = app()->getLocale();

        return $locale === 'en' && filled($this->title_en)
            ? $this->title_en
            : $this->title_ar;
    }

    public function displayPrice(): ?string
    {
        $price = $this->displayPriceValue();

        return $price !== null ? number_format($price, 0).' ر.س' : null;
    }

    public function displayPriceValue(): ?float
    {
        if ($this->allowsOnline() && $this->price_online !== null) {
            return (float) $this->price_online;
        }

        if ($this->allowsOnsite() && $this->price_onsite !== null) {
            return (float) $this->price_onsite;
        }

        $price = $this->price_online ?? $this->price_onsite;

        return $price !== null ? (float) $price : null;
    }

    public function allowsOnline(): bool
    {
        $mode = $this->normalizedDeliveryType();

        return in_array($mode, ['online', 'both'], true);
    }

    public function allowsOnsite(): bool
    {
        $mode = $this->normalizedDeliveryType();

        return in_array($mode, ['onsite', 'both'], true);
    }

    /** @return list<string> */
    public function availableDeliveryTypes(): array
    {
        $types = [];

        if ($this->allowsOnline() && $this->price_online !== null) {
            $types[] = 'online';
        }

        if ($this->allowsOnsite() && $this->price_onsite !== null) {
            $types[] = 'onsite';
        }

        // Fallback when prices aren't filled for the configured mode.
        if ($types === []) {
            if ($this->allowsOnline()) {
                $types[] = 'online';
            }
            if ($this->allowsOnsite()) {
                $types[] = 'onsite';
            }
        }

        return $types;
    }

    public function offersDeliveryChoice(): bool
    {
        return count($this->availableDeliveryTypes()) > 1;
    }

    public function deliveryModesLabel(): string
    {
        $isEn = app()->getLocale() === 'en';
        $both = $isEn ? 'Remote and in-person' : 'عن بعد وحضوري';
        $online = $isEn ? 'Remote' : 'عن بعد';
        $onsite = $isEn ? 'In-person' : 'حضوري';
        $types = $this->availableDeliveryTypes();

        if ($types === ['online', 'onsite'] || $types === ['onsite', 'online']) {
            return $both;
        }

        if ($types === ['online'] || ($types === [] && $this->allowsOnline() && ! $this->allowsOnsite())) {
            return $online;
        }

        if ($types === ['onsite'] || ($types === [] && $this->allowsOnsite() && ! $this->allowsOnline())) {
            return $onsite;
        }

        return match ($this->normalizedDeliveryType()) {
            'online' => $online,
            'both' => $both,
            default => $onsite,
        };
    }

    public function installmentOffered(): bool
    {
        if (\App\Support\InstallmentSettings::checkoutEnabled()) {
            $plans = app(\App\Services\InstallmentCheckoutService::class)
                ->availablePlans(collect([(object) ['course_id' => $this->id]]));

            if ($plans->isNotEmpty()) {
                return true;
            }
        }

        if (\App\Support\PaymentGatewaySettings::isEnabled('tabby')
            || \App\Support\PaymentGatewaySettings::isEnabled('tamara')) {
            return true;
        }

        if (! $this->relationLoaded('academicProgram')) {
            $this->load(['academicProgram.batches']);
        }

        return (bool) $this->academicProgram?->batches
            ?->contains(fn ($batch) => $batch->enrollment_open && $batch->installment_allowed);
    }

    public function installmentLabel(): string
    {
        if (! $this->installmentOffered()) {
            return 'سداد كامل فقط';
        }

        $parts = ['تقسيط متاح'];

        if (\App\Support\InstallmentSettings::checkoutEnabled()) {
            $plans = app(\App\Services\InstallmentCheckoutService::class)
                ->availablePlans(collect([(object) ['course_id' => $this->id]]));

            if ($plans->isNotEmpty()) {
                $parts[] = 'وفق خطط السداد المعتمدة';
            }
        }

        if (\App\Support\PaymentGatewaySettings::isEnabled('tabby')
            || \App\Support\PaymentGatewaySettings::isEnabled('tamara')) {
            $parts[] = 'Tabby / Tamara';
        }

        return implode(' · ', array_unique($parts));
    }

    public function normalizedDeliveryType(): string
    {
        $type = (string) ($this->delivery_type ?: 'online');

        return match ($type) {
            'online', 'onsite', 'both' => $type,
            'offline' => 'onsite',
            default => 'online',
        };
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'http')
            ? $this->image
            : static_asset(ltrim($this->image, './'));
    }

    public function posterUrl(): string
    {
        return \App\Support\PosterSettings::resolve($this->image);
    }

    public function hasCustomPoster(): bool
    {
        return filled($this->image) && ! \App\Support\PosterSettings::isLegacyPoster($this->image);
    }

    public function showSlug(): string
    {
        return str_replace('.html', '', (string) ($this->slug ?: 'course-'.$this->id));
    }
}
