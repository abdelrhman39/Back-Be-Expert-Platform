<?php

namespace App\Support;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RuntimeSettings
{
    public const KEY_PREFIX = 'env.';

    /** @return array<string, array<string, mixed>> */
    public static function sections(): array
    {
        return config('runtime-settings.sections', []);
    }

    /** @return array<string, array<string, mixed>> */
    public static function fields(): array
    {
        return config('runtime-settings.fields', []);
    }

    /** @return array<int, array<string, string>> */
    public static function integrationPages(): array
    {
        return config('runtime-settings.integration_pages', []);
    }

    public static function storageKey(string $envKey): string
    {
        return self::KEY_PREFIX.$envKey;
    }

    public static function field(string $envKey): ?array
    {
        return static::fields()[$envKey] ?? null;
    }

    /** @return array<int, array{key: string, meta: array<string, mixed>}> */
    public static function fieldsForSection(string $section): array
    {
        $items = [];

        foreach (static::fields() as $key => $meta) {
            if (($meta['section'] ?? '') === $section) {
                $items[] = ['key' => $key, 'meta' => $meta];
            }
        }

        return $items;
    }

    public static function canManageSection(?User $user, string $section): bool
    {
        if (! $user) {
            return false;
        }

        $permission = static::sections()[$section]['permission'] ?? 'system-settings.manage';

        return AdminPermissions::can($user, $permission)
            || AdminPermissions::can($user, 'system-settings.manage');
    }

    public static function canViewHub(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (AdminPermissions::can($user, 'system-settings.view')
            || AdminPermissions::can($user, 'system-settings.manage')) {
            return true;
        }

        foreach (static::sections() as $section) {
            if (AdminPermissions::can($user, $section['permission'] ?? '')) {
                return true;
            }
        }

        foreach (static::integrationPages() as $page) {
            if (AdminPermissions::can($user, $page['permission'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    public static function get(string $envKey, mixed $default = null): mixed
    {
        $stored = PlatformSetting::get(self::storageKey($envKey));

        if ($stored !== null && $stored !== '') {
            $field = static::field($envKey);

            if ($field['secret'] ?? false) {
                try {
                    return Crypt::decryptString($stored);
                } catch (\Throwable) {
                    return $stored;
                }
            }

            return static::castValue($stored, $field['type'] ?? 'string');
        }

        $envValue = env($envKey);

        return $envValue !== null && $envValue !== '' ? $envValue : $default;
    }

    public static function hasStored(string $envKey): bool
    {
        $stored = PlatformSetting::get(self::storageKey($envKey));

        return filled($stored);
    }

    public static function source(string $envKey): string
    {
        return static::hasStored($envKey) ? 'database' : 'env';
    }

    public static function set(string $envKey, mixed $value, ?User $updatedBy = null, bool $audit = true): void
    {
        $change = static::persist($envKey, $value, $updatedBy);

        if (! $change || ! $updatedBy || ! $audit) {
            return;
        }

        app(AuditLogService::class)->log(
            action: 'runtime_settings.updated',
            descriptionAr: 'تحديث متغير النظام '.$envKey,
            group: 'settings',
            actor: $updatedBy,
            subjectLabel: $envKey,
            oldValues: [$envKey => $change['old']],
            newValues: [$envKey => $change['new']],
        );
    }

    /**
     * @return array{old: string, new: string}|null
     */
    protected static function persist(string $envKey, mixed $value, ?User $updatedBy = null): ?array
    {
        $field = static::field($envKey);

        if (! $field) {
            return null;
        }

        $group = $field['section'] ?? 'system';
        $label = $field['label_ar'] ?? $envKey;
        $isSecret = (bool) ($field['secret'] ?? false);

        $normalized = static::normalizeForStorage($value, $field['type'] ?? 'string');
        $storedValue = $isSecret && filled($normalized)
            ? Crypt::encryptString((string) $normalized)
            : (string) $normalized;

        $previousValue = static::hasStored($envKey)
            ? static::get($envKey)
            : env($envKey);

        $oldDisplay = $isSecret ? '●●●●' : static::displayValue($envKey, $previousValue);
        $newDisplay = $isSecret ? '●●●●' : static::displayValue($envKey, $normalized);

        if ($oldDisplay === $newDisplay) {
            return null;
        }

        PlatformSetting::set(
            key: self::storageKey($envKey),
            value: $storedValue,
            group: $group,
            labelAr: $label,
            type: $isSecret ? 'encrypted' : ($field['type'] ?? 'string'),
            isSecret: $isSecret,
            descriptionAr: $field['hint_ar'] ?? null,
            updatedBy: $updatedBy?->id,
        );

        return ['old' => $oldDisplay, 'new' => $newDisplay];
    }

    /** @param  array<string, mixed>  $values */
    public static function setMany(array $values, ?User $updatedBy = null, ?string $section = null): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($values as $envKey => $value) {
            if (! static::field($envKey)) {
                continue;
            }

            if (($value === '' || $value === null) && static::field($envKey)['secret'] ?? false) {
                if (static::hasStored($envKey)) {
                    continue;
                }
            }

            $change = static::persist($envKey, $value, $updatedBy);

            if ($change) {
                $oldValues[$envKey] = $change['old'];
                $newValues[$envKey] = $change['new'];
            }
        }

        if ($updatedBy && $oldValues !== []) {
            $sectionLabel = $section
                ? (static::sections()[$section]['label'] ?? $section)
                : 'إعدادات النظام';
            $count = count($oldValues);

            app(AuditLogService::class)->log(
                action: 'runtime_settings.batch_updated',
                descriptionAr: 'تحديث '.$count.' متغيراً في «'.$sectionLabel.'»',
                group: 'settings',
                actor: $updatedBy,
                subjectLabel: $section ?? 'runtime-settings',
                oldValues: $oldValues,
                newValues: $newValues,
            );
        }
    }

    public static function clear(string $envKey): void
    {
        $field = static::field($envKey);
        $isSecret = (bool) ($field['secret'] ?? false);
        $previousValue = static::hasStored($envKey) ? static::get($envKey) : null;

        PlatformSetting::query()->where('key', self::storageKey($envKey))->delete();
        PlatformSetting::forgetCache(self::storageKey($envKey));

        if (auth()->check()) {
            app(AuditLogService::class)->log(
                action: 'runtime_settings.cleared',
                descriptionAr: 'إعادة '.$envKey.' لقيمة .env',
                group: 'settings',
                subjectLabel: $envKey,
                oldValues: [$envKey => $isSecret ? '●●●●' : (string) ($previousValue ?? '')],
                newValues: [$envKey => (string) (env($envKey) ?? '')],
            );
        }
    }

    public static function applyRuntimeConfig(): void
    {
        if (! Schema::hasTable('platform_settings')) {
            return;
        }

        foreach (static::fields() as $envKey => $field) {
            $value = static::get($envKey);

            if ($value === null || $value === '') {
                continue;
            }

            foreach ($field['config'] ?? [] as $configPath) {
                Config::set($configPath, static::castValue($value, $field['type'] ?? 'string'));
            }
        }

        $appName = static::get('APP_NAME', config('app.name'));
        if (filled($appName)) {
            Config::set('mail.from.name', static::get('MAIL_FROM_NAME', $appName));
        }
    }

    public static function testMail(string $toEmail): void
    {
        static::applyRuntimeConfig();

        Mail::raw(
            'هذه رسالة اختبار من منصة مركز التعلم المستمر. إذا وصلتك، فإعدادات البريد صحيحة.',
            fn ($message) => $message->to($toEmail)->subject('اختبار البريد — '.config('app.name'))
        );
    }

    protected static function displayValue(string $envKey, mixed $value): string
    {
        $type = static::field($envKey)['type'] ?? 'string';

        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        return (string) ($value ?? '');
    }

    protected static function normalizeForStorage(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
            'integer' => (string) (int) $value,
            default => (string) $value,
        };
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            default => $value,
        };
    }

    /** @return array<string, mixed> */
    public static function snapshotForSection(string $section): array
    {
        $snapshot = [];

        foreach (static::fieldsForSection($section) as $item) {
            $key = $item['key'];
            $snapshot[$key] = [
                'value' => static::get($key),
                'source' => static::source($key),
                'has_stored_secret' => ($item['meta']['secret'] ?? false) && static::hasStored($key),
                'meta' => $item['meta'],
            ];
        }

        return $snapshot;
    }

    public static function envFallbackLabel(string $envKey): string
    {
        $value = env($envKey);

        if ($value === null || $value === '') {
            return 'غير مضبوط في .env';
        }

        $field = static::field($envKey);

        if ($field['secret'] ?? false) {
            return 'مضبوط في .env (●●●●)';
        }

        return Str::limit((string) $value, 40);
    }
}
