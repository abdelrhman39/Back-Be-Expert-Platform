<?php

namespace App\Support;

use App\Models\CrmSource;
use App\Models\CrmStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrmOptions
{
    private const CACHE_TTL = 300;

    /** @var Collection<int, CrmStatus>|null */
    private static ?Collection $statusMemo = null;

    /** @var Collection<int, CrmSource>|null */
    private static ?Collection $sourceMemo = null;

    /** @return array<string, string> */
    public static function statuses(bool $activeOnly = true): array
    {
        return static::statusModels($activeOnly)
            ->mapWithKeys(fn (CrmStatus $status) => [$status->key => $status->name_ar])
            ->all();
    }

    /** @return array<string, string> */
    public static function sources(bool $activeOnly = true): array
    {
        return static::sourceModels($activeOnly)
            ->mapWithKeys(fn (CrmSource $source) => [$source->key => $source->name_ar])
            ->all();
    }

    /** @return array<string, string> */
    public static function priorities(): array
    {
        return ['low' => 'منخفضة', 'medium' => 'متوسطة', 'high' => 'مرتفعة', 'urgent' => 'عاجلة'];
    }

    /** @return array<string, string> */
    public static function activityTypes(): array
    {
        return [
            'call' => 'مكالمة',
            'whatsapp' => 'واتساب',
            'email' => 'بريد إلكتروني',
            'meeting' => 'اجتماع',
            'note' => 'ملاحظة',
            'status_change' => 'تغيير الحالة',
            'assignment' => 'توزيع العميل',
            'system' => 'تحديث آلي',
        ];
    }

    /** @return array<string, string> */
    public static function outcomes(): array
    {
        return [
            'answered' => 'تم الرد',
            'no_answer' => 'لم يرد',
            'busy' => 'مشغول',
            'callback' => 'طلب معاودة الاتصال',
            'interested' => 'مهتم',
            'not_interested' => 'غير مهتم',
            'completed' => 'مكتمل',
        ];
    }

    public static function label(array $options, ?string $value): string
    {
        return $value ? ($options[$value] ?? $value) : '—';
    }

    public static function statusLabel(?string $key): string
    {
        if (! $key) {
            return '—';
        }

        return static::statuses(false)[$key] ?? $key;
    }

    public static function sourceLabel(?string $key): string
    {
        if (! $key) {
            return '—';
        }

        return static::sources(false)[$key] ?? $key;
    }

    public static function defaultStatusKey(): string
    {
        $default = static::statusModels(true)->firstWhere('is_default', true)
            ?? static::statusModels(true)->first();

        return $default?->key ?: 'new';
    }

    public static function contactedStatusKey(): string
    {
        $contacted = static::statusModels(true)->firstWhere('key', 'contacted')
            ?? static::statusModels(true)->first(fn (CrmStatus $status) => ! $status->is_closed && ! $status->is_default);

        return $contacted?->key ?: static::defaultStatusKey();
    }

    public static function defaultSourceKey(): string
    {
        $manual = static::sourceModels(true)->firstWhere('key', 'manual')
            ?? static::sourceModels(true)->first();

        return $manual?->key ?: 'manual';
    }

    /** @return array<int, string> */
    public static function closedStatusKeys(): array
    {
        return static::statusModels(false)
            ->filter(fn (CrmStatus $status) => $status->is_closed || $status->is_won || $status->is_lost)
            ->pluck('key')
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    public static function wonStatusKeys(): array
    {
        return static::statusModels(false)->where('is_won', true)->pluck('key')->values()->all();
    }

    /** @return array<int, string> */
    public static function lostStatusKeys(): array
    {
        return static::statusModels(false)->where('is_lost', true)->pluck('key')->values()->all();
    }

    public static function isWon(?string $key): bool
    {
        return $key && in_array($key, static::wonStatusKeys(), true);
    }

    public static function isLost(?string $key): bool
    {
        return $key && in_array($key, static::lostStatusKeys(), true);
    }

    public static function isClosed(?string $key): bool
    {
        return $key && in_array($key, static::closedStatusKeys(), true);
    }

    public static function isDefault(?string $key): bool
    {
        return $key === static::defaultStatusKey();
    }

    public static function isPaymentStatus(?string $key): bool
    {
        return in_array((string) $key, ['awaiting_payment', 'paid'], true);
    }

    public static function requiresPaymentReceipt(?string $key): bool
    {
        return $key === 'paid';
    }

    public static function statusColor(?string $key): string
    {
        return static::statusModels(false)->firstWhere('key', $key)?->color ?: '#55706f';
    }

    public static function resolveSourceKey(?string $value, ?string $fallback = null): string
    {
        $fallback ??= static::defaultSourceKey();
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        $sources = static::sourceModels(false);
        $byKey = $sources->firstWhere('key', Str::slug($value, '_') ?: $value);
        if ($byKey) {
            return $byKey->key;
        }

        $byName = $sources->first(fn (CrmSource $source) => mb_strtolower($source->name_ar) === mb_strtolower($value));

        return $byName?->key ?: $fallback;
    }

    public static function resolveStatusKey(?string $value, ?string $fallback = null): string
    {
        $fallback ??= static::defaultStatusKey();
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        $statuses = static::statusModels(false);
        $byKey = $statuses->firstWhere('key', Str::slug($value, '_') ?: $value);
        if ($byKey) {
            return $byKey->key;
        }

        $byName = $statuses->first(fn (CrmStatus $status) => mb_strtolower($status->name_ar) === mb_strtolower($value));

        return $byName?->key ?: $fallback;
    }

    public static function makeKey(string $name): string
    {
        $slug = Str::slug($name, '_');
        if ($slug !== '') {
            return mb_substr($slug, 0, 40);
        }

        return 'item_'.substr(md5($name), 0, 8);
    }

    public static function forgetCache(): void
    {
        self::$statusMemo = null;
        self::$sourceMemo = null;
        Cache::forget('crm.options.statuses.v2');
        Cache::forget('crm.options.sources.v2');
        // Clear legacy keys that stored Eloquent collections and can break unserialize().
        Cache::forget('crm.options.statuses');
        Cache::forget('crm.options.sources');
    }

    /** @return Collection<int, CrmStatus> */
    public static function statusModels(bool $activeOnly = true)
    {
        if (! static::tablesReady()) {
            return collect();
        }

        self::$statusMemo ??= static::hydrateModels(
            CrmStatus::class,
            Cache::remember('crm.options.statuses.v2', self::CACHE_TTL, function () {
                return CrmStatus::query()->ordered()->get()->map->getAttributes()->values()->all();
            })
        );

        return $activeOnly
            ? self::$statusMemo->where('is_active', true)->values()
            : self::$statusMemo->values();
    }

    /** @return Collection<int, CrmSource> */
    public static function sourceModels(bool $activeOnly = true)
    {
        if (! static::tablesReady()) {
            return collect();
        }

        self::$sourceMemo ??= static::hydrateModels(
            CrmSource::class,
            Cache::remember('crm.options.sources.v2', self::CACHE_TTL, function () {
                return CrmSource::query()->ordered()->get()->map->getAttributes()->values()->all();
            })
        );

        return $activeOnly
            ? self::$sourceMemo->where('is_active', true)->values()
            : self::$sourceMemo->values();
    }

    /**
     * @param  class-string<CrmStatus|CrmSource>  $modelClass
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, CrmStatus|CrmSource>
     */
    private static function hydrateModels(string $modelClass, array $rows): Collection
    {
        return collect($rows)->map(function (array $attributes) use ($modelClass) {
            /** @var CrmStatus|CrmSource $model */
            $model = (new $modelClass)->newFromBuilder($attributes);

            return $model;
        });
    }

    private static function tablesReady(): bool
    {
        return Schema::hasTable('crm_statuses') && Schema::hasTable('crm_sources');
    }
}
