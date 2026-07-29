<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Mode tampilan situs publik: gelap, terang, atau ikut perangkat pengunjung.
 *
 * Sebelumnya mode hanya hidup di `localStorage` browser masing-masing
 * pengunjung, sehingga tidak ada satu pun tombol di panel admin yang bisa
 * membuat situs tampil terang — tab "Mode Terang" di menu Tema Website cuma
 * mengganti kelompok warna yang sedang disunting. Di sini modenya jadi milik
 * situs: tersimpan di database, dikirim ke browser saat halaman dimuat, dan
 * dipakai sebagai bawaan bagi pengunjung yang belum pernah menekan sakelar.
 *
 * `stamp` adalah detik Unix saat admin terakhir mengganti mode. Pilihan
 * pengunjung di localStorage disimpan bersama stamp yang berlaku ketika ia
 * memilih; begitu admin mengganti bawaan, stamp server jadi lebih baru dan
 * pilihan lama otomatis kedaluwarsa. Tanpa ini, admin yang pernah menekan
 * sakelar ke gelap akan terus melihat situsnya gelap walau sudah menyetel
 * "Mode Terang" — persis keluhan "tampilan kembali ke mode gelap".
 */
class ThemeMode
{
    public const KEY = 'theme_mode';

    public const CACHE_KEY = 'theme_mode_config';

    /** Harus sama dengan konstanta di resources/js/lib/theme.js. */
    public const STORAGE_KEY = 'alazka:opsi3-theme';

    /** Pilihan mode + labelnya untuk panel admin. */
    public const MODES = [
        'dark' => 'Mode Gelap',
        'light' => 'Mode Terang',
        'system' => 'Ikuti Perangkat',
    ];

    /** Dipakai bila belum ada setelan tersimpan, dan sebagai tebakan
     *  server untuk mode 'system' (dikoreksi skrip pra-render di browser). */
    public const FALLBACK = 'dark';

    /** @return array{mode: string, stamp: int} */
    public static function config(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $raw = SiteSetting::query()->where('key', self::KEY)->value('value');
            $decoded = is_string($raw) ? json_decode($raw, true) : null;
            $decoded = is_array($decoded) ? $decoded : [];

            $mode = $decoded['mode'] ?? null;

            return [
                'mode' => isset(self::MODES[$mode]) ? $mode : self::FALLBACK,
                'stamp' => (int) ($decoded['stamp'] ?? 0),
            ];
        });
    }

    public static function current(): string
    {
        return self::config()['mode'];
    }

    /** Simpan mode baru; stamp hanya naik bila modenya benar-benar berganti. */
    public static function save(string $mode): void
    {
        $mode = isset(self::MODES[$mode]) ? $mode : self::FALLBACK;
        $config = self::config();

        SiteSetting::updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => json_encode([
                    'mode' => $mode,
                    // Naik hanya saat modenya berganti — menyimpan warna saja
                    // tidak boleh membuang pilihan gelap/terang pengunjung.
                    // `+ 1` menjaga stamp tetap menaik walau dua pergantian
                    // terjadi di detik yang sama.
                    'stamp' => $mode === $config['mode']
                        ? $config['stamp']
                        : max(time(), $config['stamp'] + 1),
                ]),
                'group' => 'tema',
                'type' => 'json',
                'label' => 'Mode Tampilan Situs',
            ],
        );

        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Data yang disuntikkan ke <head> agar mode sudah benar sejak cat pertama.
     *
     * Mengembalikan null untuk panel admin & halaman masuk: keduanya punya
     * tampilan terang sendiri, dan aturan `[data-theme='light']` di app.css
     * membalik skala `--color-slate-*` — kalau ikut terpasang, teks panel admin
     * jadi kacau. Jadi atribut `data-theme` sengaja hanya dipakai halaman publik.
     *
     * @return array{mode: string, default: string, stamp: int, storageKey: string}|null
     */
    public static function boot(?string $component): ?array
    {
        if ($component === null
            || str_starts_with($component, 'Admin/')
            || str_starts_with($component, 'Auth/')) {
            return null;
        }

        $config = self::config();

        return [
            // Nilai untuk atribut data-theme di HTML server. Preferensi
            // perangkat belum diketahui di sini; skrip di <head> mengoreksinya.
            'mode' => $config['mode'] === 'system' ? self::FALLBACK : $config['mode'],
            'default' => $config['mode'],
            'stamp' => $config['stamp'],
            'storageKey' => self::STORAGE_KEY,
        ];
    }
}
