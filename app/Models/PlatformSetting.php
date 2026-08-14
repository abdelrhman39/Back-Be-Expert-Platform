<?php

namespace App\Models;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label_ar',
        'label_en',
        'description_ar',
        'is_public',
        'is_secret',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_secret' => 'boolean',
        ];
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return $default;
        }

        return Cache::rememberForever('platform_setting.'.$key, function () use ($key, $default) {
            $value = static::query()->where('key', $key)->value('value');

            return $value !== null && $value !== '' ? $value : $default;
        });
    }

    public static function set(
        string $key,
        ?string $value,
        string $group = 'general',
        ?string $labelAr = null,
        ?string $type = 'string',
        bool $isSecret = false,
        ?string $descriptionAr = null,
        ?int $updatedBy = null,
    ): void {
        $oldValue = static::get($key);

        static::query()->updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'group' => $group,
                'label_ar' => $labelAr,
                'type' => $type,
                'is_secret' => $isSecret,
                'description_ar' => $descriptionAr,
                'updated_by' => $updatedBy,
            ], fn ($v) => $v !== null),
        );

        static::forgetCache($key);

        if ($oldValue !== $value && auth()->check() && ! str_starts_with($key, 'env.')) {
            app(AuditLogService::class)->log(
                action: 'settings.updated',
                descriptionAr: 'تحديث إعداد: '.($labelAr ?? $key),
                group: 'settings',
                subjectLabel: $key,
                oldValues: [$key => $isSecret ? '●●●●' : $oldValue],
                newValues: [$key => $isSecret ? '●●●●' : $value],
            );
        }
    }

    public static function forgetCache(string $key): void
    {
        Cache::forget('platform_setting.'.$key);
    }
}
