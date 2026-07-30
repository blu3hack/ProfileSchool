<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Warna aksen tema landing page (Opsi 3), dikelola admin lewat menu
 * "Tema Website".
 *
 * Situs memakai empat keluarga warna neon — aqua, volt, plasma, solar —
 * yang tampil berbeda pada mode gelap & terang. Admin cukup memilih satu
 * warna induk (shade "400") untuk tiap aksen di tiap mode; ramp shade
 * lainnya (200–600) diturunkan otomatis lewat `color-mix()` di CSS.
 *
 * Nilainya disimpan sebagai satu baris JSON di tabel `site_settings`
 * (key `theme_palette`) dan di-cache agar tidak memukul database tiap request.
 */
class ThemePalette
{
    public const KEY = 'theme_palette';

    public const CACHE_KEY = 'theme_palette_map';

    /** Aksen yang bisa diubah + labelnya untuk panel admin. */
    public const ACCENTS = [
        'aqua' => 'Aksen Utama · Aqua',
        'volt' => 'Aksen Kedua · Ungu',
        'plasma' => 'Aksen Ketiga · Magenta',
        'solar' => 'Aksen Keempat · Emas',
    ];

    /**
     * Warna latar utama halaman per mode (shade `--color-void-950`).
     * Permukaan & garis lain (void-900…600) diturunkan otomatis darinya.
     */
    public const BACKGROUND = 'background';

    /**
     * Warna bawaan — disalin dari nilai asli di `resources/css/app.css`
     * supaya "Kembalikan Bawaan" mengembalikan tampilan pabrik.
     *
     * @return array{dark: array<string,string>, light: array<string,string>}
     */
    public static function defaults(): array
    {
        return [
            'dark' => [
                'aqua' => '#34e2f5',
                'volt' => '#a97bff',
                'plasma' => '#ff5ecf',
                'solar' => '#ffc73d',
                'background' => '#03050e',
            ],
            'light' => [
                'aqua' => '#0aa2bd',
                'volt' => '#7c3aed',
                'plasma' => '#d3299d',
                'solar' => '#c98d0f',
                'background' => '#eef3fb',
            ],
        ];
    }

    /**
     * Palet aktif: nilai tersimpan admin ditimpakan di atas bawaan,
     * jadi hasilnya selalu lengkap walau admin hanya mengubah sebagian.
     *
     * @return array{dark: array<string,string>, light: array<string,string>}
     */
    public static function current(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $defaults = self::defaults();
            $stored = self::stored();

            $palette = [];

            foreach ($defaults as $mode => $accents) {
                foreach ($accents as $accent => $fallback) {
                    $value = $stored[$mode][$accent] ?? null;

                    $palette[$mode][$accent] = self::normalizeHex($value) ?? $fallback;
                }
            }

            return $palette;
        });
    }

    /** Simpan palet baru (nilai sudah tervalidasi controller). */
    public static function save(array $palette): void
    {
        $clean = [];

        foreach (self::defaults() as $mode => $accents) {
            foreach ($accents as $accent => $fallback) {
                $value = $palette[$mode][$accent] ?? null;

                $clean[$mode][$accent] = self::normalizeHex($value) ?? $fallback;
            }
        }

        SiteSetting::updateOrCreate(
            ['key' => self::KEY],
            [
                'value' => json_encode($clean),
                'group' => 'tema',
                'type' => 'json',
                'label' => 'Palet Warna Tema',
            ],
        );

        self::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Blok CSS yang disuntikkan ke `<head>`: menimpa variabel warna
     * Tailwind (`--color-aqua-400`, dst.) untuk tiap mode.
     *
     * Selektor atribut sengaja digandakan (`[data-theme='dark'][data-theme='dark']`)
     * agar spesifisitasnya melampaui aturan di app.css, sehingga selalu menang
     * berapa pun urutan pemuatan stylesheet.
     *
     * HANYA warna yang benar-benar diubah admin yang dituliskan. Ramp turunan
     * dihitung dengan mencampur ke putih/hitam murni, yang mencuci rona biru
     * pada skala `--color-void-*`: menuliskannya untuk warna bawaan membuat
     * permukaan kartu jadi abu-abu netral (#12141c, #dbe0e7) alih-alih
     * biru-kehitaman/biru-lembut hasil penyetelan tangan di app.css — padahal
     * admin belum menyentuh apa pun. Dengan disaring begini, nilai bawaan
     * app.css dibiarkan utuh dan `<style>` inline-nya kosong sampai benar-benar
     * ada yang dikustomisasi.
     */
    public static function css(): string
    {
        $palette = self::current();
        $defaults = self::defaults();
        $blocks = [];

        foreach ($palette as $mode => $values) {
            $lines = [];

            // Aksen neon: aqua, volt, plasma, solar.
            foreach (array_keys(self::ACCENTS) as $accent) {
                if ($values[$accent] === $defaults[$mode][$accent]) {
                    continue;
                }

                foreach (self::ramp($mode, $values[$accent]) as $shade => $color) {
                    $lines[] = "  --color-{$accent}-{$shade}: {$color};";
                }
            }

            // Latar utama + permukaan/garis turunannya (--color-void-*).
            if ($values[self::BACKGROUND] !== $defaults[$mode][self::BACKGROUND]) {
                foreach (self::bgRamp($mode, $values[self::BACKGROUND]) as $shade => $color) {
                    $lines[] = "  --color-void-{$shade}: {$color};";
                }
            }

            // Tombol ajakan utama hero — lihat catatan pada self::cta().
            if ($values['aqua'] !== $defaults[$mode]['aqua']
                || $values['volt'] !== $defaults[$mode]['volt']) {
                foreach (self::cta($mode, $values['aqua'], $values['volt']) as $token => $color) {
                    $lines[] = "  --cta-{$token}: {$color};";
                }
            }

            if ($lines === []) {
                continue;
            }

            $selector = "[data-theme='{$mode}'][data-theme='{$mode}']";
            $blocks[] = $selector.' {'.PHP_EOL.implode(PHP_EOL, $lines).PHP_EOL.'}';
        }

        return implode(PHP_EOL, $blocks);
    }

    /**
     * Turunkan shade 200–600 dari warna induk (400).
     *
     * Arah ramp berbeda per mode: di mode gelap shade kecil lebih terang
     * (dipakai untuk teks & glow di atas latar gelap), di mode terang shade
     * kecil justru lebih gelap (agar kontras di atas latar putih).
     *
     * @return array<int,string>
     */
    protected static function ramp(string $mode, string $hex): array
    {
        $mix = fn (int $pct, string $with) => self::mixHex($hex, $pct, $with);

        if ($mode === 'light') {
            return [
                200 => $mix(55, '#000000'),
                300 => $mix(75, '#000000'),
                400 => $hex,
                500 => $mix(88, '#000000'),
                600 => $mix(64, '#000000'),
            ];
        }

        return [
            200 => $mix(30, '#ffffff'),
            300 => $mix(60, '#ffffff'),
            400 => $hex,
            500 => $mix(82, '#000000'),
            600 => $mix(64, '#000000'),
        ];
    }

    /**
     * Turunkan skala latar `--color-void-950…600` dari satu warna latar
     * utama (shade 950).
     *
     * Mode gelap: latar paling gelap, permukaan makin terang → campur ke putih.
     * Mode terang: latar putih-lembut; 800 dipakai sebagai permukaan kartu
     * (paling terang), sedangkan 700/600 justru menggelap sebagai garis batas —
     * meniru struktur asli di app.css.
     *
     * @return array<int,string>
     */
    protected static function bgRamp(string $mode, string $hex): array
    {
        $mix = fn (int $pct, string $with) => self::mixHex($hex, $pct, $with);

        if ($mode === 'light') {
            return [
                950 => $hex,
                900 => $mix(92, '#000000'),
                800 => $mix(52, '#ffffff'),
                700 => $mix(82, '#000000'),
                600 => $mix(65, '#000000'),
            ];
        }

        return [
            950 => $hex,
            900 => $mix(94, '#ffffff'),
            800 => $mix(88, '#ffffff'),
            700 => $mix(78, '#ffffff'),
            600 => $mix(66, '#ffffff'),
        ];
    }

    /**
     * Token tombol ajakan utama di hero ("Daftar Sekarang").
     *
     * Permukaannya gradasi aqua-400 → aqua-500 → volt-400, dan dulu tintanya
     * dipatok `text-void-950`. Pasangan itu hanya kebetulan terbaca pada palet
     * pabrik: di mode gelap aksennya neon terang jadi tinta gelap menang, di
     * mode terang aksennya sengaja digelapkan jadi tinta terang menang. Admin
     * bebas memilih warna apa pun, termasuk yang luminansinya searah dengan
     * tinta — dan begitu itu terjadi labelnya jadi tak terbaca sama sekali
     * (tombolnya sendiri tetap ada di DOM dan tetap bisa diklik).
     *
     * Jadi tintanya tidak lagi ditebak, tapi dihitung: dipilih hitam atau putih
     * yang kontras TERBURUK-nya terhadap seluruh titik gradasi paling baik.
     * Setelah itu titik gradasi yang masih di bawah ambang baca digeser
     * menjauhi tinta secukupnya — hanya yang gagal, sehingga warna pilihan
     * admin dibiarkan utuh selama ia memang sudah terbaca.
     *
     * @return array<string,string>
     */
    protected static function cta(string $mode, string $aqua, string $volt): array
    {
        $aquaRamp = self::ramp($mode, $aqua);
        $voltRamp = self::ramp($mode, $volt);

        $stops = [$aquaRamp[400], $aquaRamp[500], $voltRamp[400]];
        $ink = self::ink($stops);

        [$from, $via, $to] = array_map(
            fn (string $stop) => self::legible($stop, $ink),
            $stops,
        );

        return [
            'from' => $from,
            'via' => $via,
            'to' => $to,
            'ink' => $ink,
            // Pendarnya ikut warna tombol yang benar-benar tampil, bukan cyan
            // & ungu bawaan yang dulu ditulis mati di markup.
            'glow' => self::rgba($from, '0.9'),
            'glow-hover' => self::rgba($to, '0.95'),
        ];
    }

    /**
     * Pilih tinta hitam atau putih untuk sebuah permukaan bergradasi.
     *
     * Yang dinilai adalah kontras terburuk terhadap SEMUA titik gradasi —
     * memakai rata-rata akan meloloskan tombol yang salah satu ujungnya
     * menelan teks.
     *
     * @param  array<int,string>  $stops
     */
    protected static function ink(array $stops): string
    {
        $dark = '#03050e';
        $light = '#f8fbff';

        $worst = fn (string $ink) => min(array_map(
            fn (string $stop) => self::contrast($stop, $ink),
            $stops,
        ));

        return $worst($light) > $worst($dark) ? $light : $dark;
    }

    /**
     * Geser satu titik gradasi menjauhi tinta (ke hitam atau putih murni)
     * sampai kontrasnya mencapai ambang baca WCAG AA untuk teks normal.
     * Warna yang sudah lolos dikembalikan apa adanya — tanpa perubahan.
     */
    protected static function legible(string $hex, string $ink, float $target = 4.5): string
    {
        $away = self::luminance($ink) > 0.5 ? '#000000' : '#ffffff';

        // Langkah 4% cukup halus: pergeseran warnanya tak kentara, tapi
        // loopnya tetap pendek dan selalu berhenti (ujungnya warna murni).
        for ($pct = 100; $pct > 0; $pct -= 4) {
            $candidate = self::mixHex($hex, $pct, $away);

            if (self::contrast($candidate, $ink) >= $target) {
                return $candidate;
            }
        }

        return $away;
    }

    /** Rasio kontras WCAG antara dua warna (1–21). */
    protected static function contrast(string $a, string $b): float
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** Luminansi relatif WCAG: 0 = hitam, 1 = putih. */
    protected static function luminance(string $hex): float
    {
        $channel = function (int $value): float {
            $v = $value / 255;

            return $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        };

        [$r, $g, $b] = self::rgb($hex);

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    /** Hex → `rgba(r, g, b, a)`, untuk nilai yang butuh transparansi. */
    protected static function rgba(string $hex, string $alpha): string
    {
        [$r, $g, $b] = self::rgb($hex);

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    /**
     * Campur dua warna hex secara linear di ruang sRGB — setara
     * `color-mix(in srgb, $hex $pct%, $with)` tetapi menghasilkan hex solid.
     *
     * Sengaja dihitung di server: hex solid didukung semua browser, sedangkan
     * `color-mix()` bisa gagal di WebView/Safari lama sehingga gradasi yang
     * memakainya (mis. tombol hero) ikut hilang.
     */
    protected static function mixHex(string $hex, int $pct, string $with): string
    {
        [$r1, $g1, $b1] = self::rgb($hex);
        [$r2, $g2, $b2] = self::rgb($with);

        $w = max(0, min(100, $pct)) / 100;

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r1 * $w + $r2 * (1 - $w)),
            (int) round($g1 * $w + $g2 * (1 - $w)),
            (int) round($b1 * $w + $b2 * (1 - $w)),
        );
    }

    /** @return array{0:int,1:int,2:int} */
    protected static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /** @return array<string, array<string,string>> */
    protected static function stored(): array
    {
        $raw = SiteSetting::query()->where('key', self::KEY)->value('value');

        if (! $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** Terima "#abc"/"#aabbcc" → "#aabbcc" huruf kecil; selain itu null. */
    protected static function normalizeHex(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = ltrim(trim($value), '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $value)) {
            $value = $value[0].$value[0].$value[1].$value[1].$value[2].$value[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $value)) {
            return null;
        }

        return '#'.strtolower($value);
    }
}
