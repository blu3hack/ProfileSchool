<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;

/**
 * Menemukan berkas hasil build milik satu halaman Inertia.
 *
 * Masalah yang diselesaikan: halaman dimuat lewat dynamic import, sehingga
 * browser baru TAHU berkasnya ada setelah bundel utama selesai diunduh dan
 * dijalankan. Rantainya jadi tiga perjalanan bolak-balik sebelum piksel pertama
 * muncul:
 *
 *     HTML  →  app.js  →  Opsi3.js + Opsi3.css  →  render
 *
 * Dengan menyebut berkas halaman di dalam <head>, unduhannya berjalan
 * bersamaan dengan bundel utama, bukan mengantre di belakangnya. Satu
 * perjalanan bolak-balik penuh hilang — dan itulah bagian yang paling terasa
 * saat pengunjung datang dengan cache kosong.
 */
class PageAssets
{
    /** Manifest cukup dibaca & diurai sekali per request. */
    protected static ?array $manifest = null;

    /**
     * Berkas yang perlu didahulukan untuk sebuah komponen halaman Inertia,
     * mis. 'Opsi3' atau 'Berita/Show'.
     *
     * @return array{js: list<string>, css: list<string>}
     */
    public static function preloads(?string $component): array
    {
        $empty = ['js' => [], 'css' => []];

        // Saat `npm run dev`, Vite menyajikan modul apa adanya tanpa manifest.
        if (! $component || Vite::isRunningHot()) {
            return $empty;
        }

        $manifest = static::manifest();
        $entry = $manifest["resources/js/Pages/{$component}.vue"] ?? null;

        if (! $entry) {
            return $empty;
        }

        $js = [];
        $css = [];

        // Telusuri chunk bersama yang diimpor halaman (NeonFooter, EventCard, …)
        // supaya semuanya ikut diunduh berbarengan, bukan berantai satu per satu.
        $walk = function (array $chunk) use (&$walk, &$js, &$css, $manifest) {
            $js[] = $chunk['file'];

            foreach ($chunk['css'] ?? [] as $file) {
                $css[] = $file;
            }

            foreach ($chunk['imports'] ?? [] as $key) {
                // Bundel utama sudah dimuat oleh @vite — jangan disebut dua kali.
                if ($key === 'resources/js/app.js' || ! isset($manifest[$key])) {
                    continue;
                }

                $walk($manifest[$key]);
            }
        };

        $walk($entry);

        $url = fn (string $file) => asset('build/'.$file);

        return [
            'js' => array_map($url, array_values(array_unique($js))),
            'css' => array_map($url, array_values(array_unique($css))),
        ];
    }

    protected static function manifest(): array
    {
        if (static::$manifest !== null) {
            return static::$manifest;
        }

        $path = public_path('build/manifest.json');

        if (! is_file($path)) {
            return static::$manifest = [];
        }

        return static::$manifest = json_decode(file_get_contents($path), true) ?: [];
    }
}
