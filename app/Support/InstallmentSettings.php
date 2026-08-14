<?php

namespace App\Support;

use App\Models\PlatformSetting;

class InstallmentSettings
{
    public static function graceDays(): int
    {
        return max(0, (int) PlatformSetting::get('installment_grace_days', 7));
    }

    public static function suspendAfterDays(): int
    {
        return max(1, (int) PlatformSetting::get('installment_suspend_after_days', 14));
    }

    /** @return array<int> */
    public static function reminderDaysBefore(): array
    {
        $raw = PlatformSetting::get('installment_reminder_days', '7,3,1');

        return collect(explode(',', (string) $raw))
            ->map(fn ($d) => (int) trim($d))
            ->filter(fn ($d) => $d > 0)
            ->unique()
            ->sortDesc()
            ->values()
            ->all() ?: [7, 3, 1];
    }

    public static function requiresSignature(): bool
    {
        return static::flag('installment_requires_signature', true);
    }

    public static function remindersEnabled(): bool
    {
        return static::flag('installment_reminders_enabled', true);
    }

    public static function suspensionEnabled(): bool
    {
        return static::flag('installment_suspension_enabled', true);
    }

    public static function dunningEnabled(): bool
    {
        return static::flag('installment_dunning_enabled', true);
    }

    public static function dunningProcessTime(): string
    {
        return static::normalizeTime(
            PlatformSetting::get('installment_dunning_time', static::overdueProcessTime()),
            '09:00',
        );
    }

    public static function checkoutEnabled(): bool
    {
        return static::flag('installment_checkout_enabled', true);
    }

    public static function academicRegistrationEnabled(): bool
    {
        return static::flag('installment_academic_registration_enabled', true);
    }

    public static function lateFeesEnabled(): bool
    {
        return static::flag('installment_late_fees_enabled', false);
    }

    public static function lateFeeMode(): string
    {
        $mode = (string) PlatformSetting::get('installment_late_fee_mode', 'percent');

        return in_array($mode, ['percent', 'fixed'], true) ? $mode : 'percent';
    }

    public static function lateFeePercent(): float
    {
        return max(0.0, min(100.0, (float) PlatformSetting::get('installment_late_fee_percent', 2)));
    }

    public static function lateFeeFixed(): float
    {
        return max(0.0, (float) PlatformSetting::get('installment_late_fee_fixed', 50));
    }

    public static function lateFeeMaxCap(): float
    {
        return max(0.0, (float) PlatformSetting::get('installment_late_fee_max_cap', 0));
    }

    public static function lateFeeApplyAfterDays(): int
    {
        return max(0, (int) PlatformSetting::get('installment_late_fee_apply_after_days', 3));
    }

    /**
     * Calculate the one-time late fee for an overdue installment amount.
     */
    public static function calculateLateFee(float $installmentAmount): float
    {
        if (! static::lateFeesEnabled()) {
            return 0.0;
        }

        $fee = static::lateFeeMode() === 'fixed'
            ? static::lateFeeFixed()
            : $installmentAmount * (static::lateFeePercent() / 100);

        $cap = static::lateFeeMaxCap();

        if ($cap > 0) {
            $fee = min($fee, $cap);
        }

        return max(0.0, round($fee, 2));
    }

    public static function reminderDispatchTime(): string
    {
        return static::normalizeTime(
            PlatformSetting::get('installment_reminder_time', '08:00'),
            '08:00',
        );
    }

    public static function overdueProcessTime(): string
    {
        return static::normalizeTime(
            PlatformSetting::get('installment_overdue_time', '09:00'),
            '09:00',
        );
    }

    public static function flag(string $key, bool $default = true): bool
    {
        return PlatformSetting::get($key, $default ? '1' : '0') !== '0';
    }

    protected static function normalizeTime(?string $value, string $fallback): string
    {
        if (! $value || ! preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $fallback;
        }

        [$hour, $minute] = array_map('intval', explode(':', $value));

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return $fallback;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }
}
