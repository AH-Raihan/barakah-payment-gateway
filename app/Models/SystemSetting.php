<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting by key with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            return Cache::remember("system_setting_{$key}", 300, function () use ($key, $default) {
                $record = self::where('key', $key)->first();
                return $record !== null ? $record->value : $default;
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set a setting by key.
     */
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        Cache::forget("system_setting_{$key}");
    }

    /**
     * Check if public user registration is enabled.
     */
    public static function isRegistrationEnabled(): bool
    {
        try {
            $val = self::get('registration_enabled', '1');
            return $val === '1' || $val === 'true' || $val === true || $val === 1;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
