<?php

namespace App\Support;

use Illuminate\Support\Str;

class AuditDiff
{
    /** @var array<string, string> */
    protected static array $fieldLabels = [
        'amount' => 'المبلغ',
        'order' => 'رقم الطلب',
        'reason' => 'السبب',
        'status' => 'الحالة',
        'is_enabled' => 'التفعيل',
        'offset_minutes' => 'التوقيت (دقيقة)',
        'channels' => 'قنوات الإرسال',
        'admin_notes' => 'ملاحظات الإدارة',
        'order_status' => 'حالة الطلب',
        'reference_no' => 'المرجع',
        'value' => 'القيمة',
        'key' => 'المفتاح',
        'label_ar' => 'التسمية',
        'MAIL_MAILER' => 'نوع البريد',
        'MAIL_HOST' => 'خادم SMTP',
        'MAIL_PORT' => 'منفذ SMTP',
        'MAIL_USERNAME' => 'اسم مستخدم SMTP',
        'MAIL_PASSWORD' => 'كلمة مرور SMTP',
        'MAIL_FROM_ADDRESS' => 'بريد المرسل',
        'MAIL_FROM_NAME' => 'اسم المرسل',
        'APP_URL' => 'رابط التطبيق',
        'APP_DEBUG' => 'وضع التصحيح',
    ];

    /**
     * @return array{
     *     rows: list<array{key: string, label: string, before: ?string, after: ?string, change: string}>,
     *     summary: array{modified: int, added: int, removed: int, unchanged: int, total: int},
     *     has_changes: bool,
     *     mode: string
     * }
     */
    public static function build(?array $oldValues, ?array $newValues, ?string $action = null): array
    {
        $oldFlat = static::flatten($oldValues ?? []);
        $newFlat = static::flatten($newValues ?? []);
        $allKeys = array_values(array_unique(array_merge(array_keys($oldFlat), array_keys($newFlat))));
        sort($allKeys);

        $rows = [];
        $summary = ['modified' => 0, 'added' => 0, 'removed' => 0, 'unchanged' => 0, 'total' => 0];

        foreach ($allKeys as $key) {
            $hasOld = array_key_exists($key, $oldFlat);
            $hasNew = array_key_exists($key, $newFlat);
            $oldRaw = $hasOld ? $oldFlat[$key] : null;
            $newRaw = $hasNew ? $newFlat[$key] : null;

            $change = static::detectChange($hasOld, $hasNew, $oldRaw, $newRaw);
            $summary[$change === 'unchanged' ? 'unchanged' : $change]++;
            $summary['total']++;

            $rows[] = [
                'key' => $key,
                'label' => static::labelFor($key),
                'before' => $hasOld ? static::formatValue($key, $oldRaw) : null,
                'after' => $hasNew ? static::formatValue($key, $newRaw) : null,
                'change' => $change,
            ];
        }

        $hasChanges = $summary['modified'] + $summary['added'] + $summary['removed'] > 0;

        return [
            'rows' => $rows,
            'summary' => $summary,
            'has_changes' => $hasChanges,
            'mode' => static::resolveMode($action, $oldValues, $newValues, $summary),
        ];
    }

    public static function changeLabel(string $change): string
    {
        return match ($change) {
            'added' => 'جديد',
            'removed' => 'محذوف',
            'modified' => 'معدّل',
            default => 'بدون تغيير',
        };
    }

    public static function modeLabel(string $mode): string
    {
        return match ($mode) {
            'create' => 'إنشاء',
            'delete' => 'حذف',
            'update' => 'تعديل',
            default => 'تغيير',
        };
    }

    /** @param  array<string, mixed>|null  $values */
    protected static function flatten(?array $values, string $prefix = ''): array
    {
        if ($values === null) {
            return [];
        }

        $flat = [];

        foreach ($values as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value) && static::isAssocArray($value)) {
                $flat = array_merge($flat, static::flatten($value, $fullKey));
            } else {
                $flat[$fullKey] = $value;
            }
        }

        return $flat;
    }

    protected static function isAssocArray(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected static function detectChange(bool $hasOld, bool $hasNew, mixed $old, mixed $new): string
    {
        if (! $hasOld && $hasNew) {
            return 'added';
        }

        if ($hasOld && ! $hasNew) {
            return 'removed';
        }

        if (static::valuesEqual($old, $new)) {
            return 'unchanged';
        }

        return 'modified';
    }

    protected static function valuesEqual(mixed $a, mixed $b): bool
    {
        if (is_array($a) && is_array($b)) {
            return json_encode($a) === json_encode($b);
        }

        return (string) $a === (string) $b;
    }

    protected static function labelFor(string $key): string
    {
        if (isset(static::$fieldLabels[$key])) {
            return static::$fieldLabels[$key];
        }

        $leaf = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;

        $runtimeField = RuntimeSettings::field($leaf);

        if ($runtimeField && isset($runtimeField['label_ar'])) {
            return $runtimeField['label_ar'].' ('.$leaf.')';
        }

        if (isset(static::$fieldLabels[$leaf])) {
            return static::$fieldLabels[$leaf];
        }

        return Str::replace(['_', '.'], ' ', $leaf);
    }

    protected static function formatValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $leaf = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;

        $runtimeField = RuntimeSettings::field($leaf);

        if (($runtimeField['type'] ?? '') === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'نعم' : 'لا';
        }

        if ($leaf === 'is_enabled' && (is_bool($value) || in_array($value, [0, 1, '0', '1', true, false], true))) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'مفعّل' : 'معطّل';
        }

        if ($leaf === 'status') {
            return RefundOptions::statusLabel((string) $value);
        }

        if ($leaf === 'order_status') {
            return match ((string) $value) {
                'refunded' => 'مسترد',
                'paid' => 'مدفوع',
                'pending' => 'قيد الانتظار',
                'cancelled' => 'ملغى',
                default => (string) $value,
            };
        }

        if ($leaf === 'channels' || $key === 'channels') {
            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }

            if (is_array($value)) {
                return collect($value)
                    ->map(fn ($ch) => NotificationRuleCatalog::channels()[$ch]['label'] ?? $ch)
                    ->implode(' · ');
            }
        }

        if (is_array($value)) {
            return collect($value)->map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE))->implode(' · ');
        }

        if ($leaf === 'amount' && is_numeric($value)) {
            return number_format((float) $value, 2).' ر.س';
        }

        if ($leaf === 'offset_minutes' && is_numeric($value)) {
            return NotificationRuleCatalog::offsetLabel((int) $value).' ('.$value.' د)';
        }

        return (string) $value;
    }

    /** @param  array<string, mixed>|null  $old */
    /** @param  array<string, mixed>|null  $new */
    protected static function resolveMode(?string $action, ?array $old, ?array $new, array $summary): string
    {
        if (str_contains((string) $action, 'batch_updated')) {
            return 'update';
        }

        if (str_contains((string) $action, 'requested') || str_contains((string) $action, 'created')) {
            return 'create';
        }

        if (str_contains((string) $action, 'cleared') || str_contains((string) $action, 'deleted')) {
            return 'delete';
        }

        if (empty($old) && ! empty($new) && $summary['added'] > 0 && $summary['modified'] === 0) {
            return 'create';
        }

        if (! empty($old) && empty($new)) {
            return 'delete';
        }

        return 'update';
    }
}
