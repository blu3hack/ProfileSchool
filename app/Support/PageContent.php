<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Jembatan antara `config/site_content.php` (definisi + default) dan
 * tabel `site_settings` (nilai hasil editan admin).
 *
 * Halaman publik memanggil `PageContent::all()` dan menerima satu array
 * key => value yang selalu lengkap: nilai dari database bila ada,
 * jatuh ke default config bila belum pernah diisi.
 */
class PageContent
{
    /** @return array<string, string|null> */
    public static function all(): array
    {
        $values = self::stored();

        $content = [];

        foreach (config('site_content.fields', []) as $field) {
            $key = $field['key'];

            // Bedakan "belum pernah diisi" (pakai default) dari "sengaja
            // dikosongkan admin" (hormati nilai kosong). Baris yang sudah ada
            // di database — termasuk string kosong — menang atas default,
            // supaya sebuah field benar-benar bisa dikosongkan/disembunyikan.
            $content[$key] = array_key_exists($key, $values)
                ? $values[$key]
                : ($field['default'] ?? null);
        }

        // Logo dirender 40 px di navbar dan 48 px di footer — tidak pernah
        // lebih besar. Mengirim berkas aslinya (PNG 528 KB) berarti setengah
        // megabita per kunjungan untuk gambar seukuran kuku jari. 192 px sudah
        // tajam sampai layar 4×; lihat App\Support\ImageVariant.
        $logo = $content['nav_logo'] ?? null;

        // Field gambar dikirim sebagai URL siap pakai di <img src>.
        foreach (self::imageKeys() as $key) {
            $content[$key] = MediaUrl::resolve($content[$key] ?? null);
        }

        if (filled($logo)) {
            $content['nav_logo'] = ImageVariant::url($logo, 192);
        }

        return $content;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    /** Definisi field dikelompokkan per group — dipakai form admin. */
    public static function schema(): array
    {
        $values = self::stored();
        $groups = config('site_content.groups', []);
        $result = [];

        foreach (config('site_content.fields', []) as $field) {
            $group = $field['group'] ?? 'umum';

            $result[$group] ??= [
                'key' => $group,
                'label' => $groups[$group] ?? ucfirst($group),
                'fields' => [],
            ];

            // Sama seperti `all()`: baris yang sudah ada di database menang
            // atas default, termasuk bila nilainya null/kosong. Tanpa ini
            // field yang sengaja dikosongkan admin akan terisi default lagi
            // setiap form dibuka — dan ikut tersimpan pada penyimpanan berikutnya.
            $raw = array_key_exists($field['key'], $values)
                ? $values[$field['key']]
                : ($field['default'] ?? null);

            $result[$group]['fields'][] = [
                'key' => $field['key'],
                'label' => $field['label'],
                'type' => $field['type'] ?? 'text',
                'hint' => $field['hint'] ?? null,
                'value' => $raw,
                'preview' => ($field['type'] ?? null) === 'image' ? MediaUrl::resolve($raw) : null,
            ];
        }

        return array_values($result);
    }

    /** @return array<int, string> */
    public static function imageKeys(): array
    {
        return array_column(
            array_filter(config('site_content.fields', []), fn ($f) => ($f['type'] ?? 'text') === 'image'),
            'key'
        );
    }

    /**
     * Nilai mentah dari database. Dibungkus pengecekan tabel supaya
     * aplikasi tetap jalan sebelum `php artisan migrate` dijalankan.
     *
     * @return array<string, string|null>
     */
    protected static function stored(): array
    {
        if (! Schema::hasTable('site_settings')) {
            return [];
        }

        return SiteSetting::map();
    }
}
