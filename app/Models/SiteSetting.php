<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Teks & gambar tunggal halaman publik, disimpan sebagai key => value.
 *
 * Dibaca lewat `SiteSetting::value('hero_title_1')` yang di-cache agar
 * landing page tidak memukul database untuk tiap field.
 */
class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'hint', 'sort_order'];

    public const CACHE_KEY = 'site_settings_map';

    /** Seluruh setting sebagai array key => value. */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
            ->orderBy('sort_order')
            ->pluck('value', 'key')
            ->all());
    }

    public static function value(string $key, ?string $default = null): ?string
    {
        $value = self::map()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /** Dipanggil setelah admin menyimpan perubahan. */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::flush());
        static::deleted(fn () => self::flush());
    }
}
