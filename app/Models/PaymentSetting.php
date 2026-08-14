<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever('payment_setting.'.$key, function () use ($key, $default) {
            $value = static::query()->where('key', $key)->value('value');

            return $value !== null && $value !== '' ? $value : $default;
        });
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget('payment_setting.'.$key);
    }
}
