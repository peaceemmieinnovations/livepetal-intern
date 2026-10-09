<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-managed platform settings, grouped so the settings screen can be laid
 * out by area. Reads are cached because they happen on every page render.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $group
 * @property string $type
 */
#[Fillable(['key', 'value', 'group', 'type'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    public const CACHE_KEY = 'livepetal.settings';

    /**
     * All settings as a key => value map.
     *
     * @return array<string, string|null>
     */
    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->pluck('value', 'key')
            ->all());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all_settings()[$key] ?? $default;
    }

    public static function put(string $key, ?string $value, string $group = 'general', string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'type' => $type],
        );

        static::flushCache();
    }

    public static function putMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => is_scalar($value) ? (string) $value : json_encode($value), 'group' => $group],
            );
        }

        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string|null>
     */
    public static function group(string $group): array
    {
        return array_filter(
            self::all_settings(),
            fn (string $key): bool => str_starts_with($key, "{$group}."),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
