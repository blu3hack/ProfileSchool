<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Kartu pratinjau sebuah halaman: judul, ringkasan, dan gambar yang muncul
 * ketika alamatnya dibagikan ke WhatsApp, Telegram, Facebook, atau X.
 *
 * KENAPA INI HARUS DICETAK SERVER. Situs ini memakai Inertia tanpa SSR: isi
 * halaman baru terbentuk setelah Vue berjalan di peramban. Perayap pratinjau
 * TIDAK menjalankan JavaScript — ia mengunduh HTML mentah satu kali, membaca
 * <head>-nya, lalu pergi. Tag Open Graph yang dipasang lewat komponen <Head>
 * milik Inertia karena itu tidak pernah terlihat olehnya, dan tautan yang
 * dibagikan tetap tampil sebagai teks polos. Satu-satunya tempat yang dibaca
 * perayap adalah root view — lihat resources/views/partials/meta.blade.php,
 * yang menerima hasil kelas ini lewat `->withViewData(['meta' => ...])`.
 *
 * Tiap nilai punya cadangan berlapis sampai ke identitas situs, sehingga
 * halaman tanpa gambar atau tanpa ringkasan tetap menghasilkan kartu utuh dan
 * bukan pratinjau setengah jadi.
 */
class PageMeta
{
    /**
     * Batas deskripsi. WhatsApp memotong pratinjaunya sekitar dua baris dan
     * Google sekitar 160 karakter; 200 memberi ruang tanpa jadi paragraf.
     */
    public const DESCRIPTION_LIMIT = 200;

    /** Ukuran gambar yang sudah dibaca, per request. */
    private static array $sizes = [];

    /**
     * @param  array{
     *     title?: ?string,
     *     description?: ?string,
     *     image?: ?string,
     *     imageAlt?: ?string,
     *     url?: ?string,
     *     type?: string,
     *     publishedTime?: ?string,
     *     modifiedTime?: ?string,
     *     noindex?: bool,
     * }  $meta
     */
    public static function make(array $meta = []): array
    {
        $content = PageContent::all();
        $siteName = SiteInfo::name();

        $title = trim((string) ($meta['title'] ?? '')) ?: $siteName;
        $image = self::image($meta['image'] ?? null, $content);

        return [
            // og:title — judul halamannya saja. Nama situs tidak ikut ditempel
            // di sini karena sudah punya tempat sendiri di og:site_name.
            'title' => $title,

            // <title> tab. Susunannya sengaja sama persis dengan callback
            // `title` di resources/js/app.js, supaya judul tidak berkedip
            // berubah begitu Inertia mengambil alih setelah halaman hidup.
            'documentTitle' => $title.' - '.config('app.name', $siteName),

            'description' => self::description($meta['description'] ?? null, $content),

            // Alamat kanonik. Wajib absolut: pratinjau menolak alamat relatif,
            // dan og:url adalah kunci cache di sisi WhatsApp/Facebook.
            'url' => $meta['url'] ?? url()->current(),

            'image' => $image['url'],
            'imageWidth' => $image['width'],
            'imageHeight' => $image['height'],
            'imageAlt' => ($meta['imageAlt'] ?? null) ?: $title,

            'siteName' => $siteName,
            'type' => $meta['type'] ?? 'website',
            'publishedTime' => self::timestamp($meta['publishedTime'] ?? null),
            'modifiedTime' => self::timestamp($meta['modifiedTime'] ?? null),
            'noindex' => (bool) ($meta['noindex'] ?? false),
        ];
    }

    /**
     * Ringkasan halaman, dijatuhkan ke deskripsi situs bila kosong.
     *
     * Dibersihkan dari HTML lebih dulu: sumbernya bisa berupa ringkasan yang
     * diketik admin di editor teks kaya, dan tag mentah di dalam atribut
     * `content` akan tampil apa adanya di kartu pratinjau.
     */
    protected static function description(?string $value, array $content): ?string
    {
        $text = HtmlSanitizer::plain($value) ?: HtmlSanitizer::plain($content['meta_description'] ?? '');

        return $text === '' ? null : Str::limit($text, self::DESCRIPTION_LIMIT);
    }

    /**
     * Gambar kartu: milik halaman itu sendiri, lalu gambar bagikan bawaan
     * situs, foto beranda, dan terakhir logo sekolah.
     *
     * @return array{url: ?string, width: ?int, height: ?int}
     */
    protected static function image(?string $value, array $content): array
    {
        $candidates = [
            $value,
            $content['og_image'] ?? null,
            $content['hero_image'] ?? null,
            $content['nav_logo'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $url = self::absolute(MediaUrl::resolve($candidate));

            if ($url !== null) {
                return ['url' => $url, ...self::dimensions($url)];
            }
        }

        return ['url' => null, 'width' => null, 'height' => null];
    }

    /**
     * Alamat lengkap berikut skema & host.
     *
     * `/storage/foto.jpg` tidak berarti apa-apa bagi perayap yang membaca HTML
     * di luar konteks peramban — ia butuh alamat yang bisa langsung diunduh.
     * Gambar data-URI ditolak karena alasan yang sama: tidak ada yang bisa
     * diambil dari sana.
     */
    protected static function absolute(?string $url): ?string
    {
        if (blank($url) || Str::startsWith($url, 'data:')) {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : url($url);
    }

    /**
     * Ukuran gambar, dibaca dari berkasnya sendiri.
     *
     * Facebook & WhatsApp menampilkan kartu bergambar pada pembagian PERTAMA
     * hanya bila ukurannya sudah tercantum di HTML. Tanpa itu gambar baru
     * diambil setelah kartunya terlanjur dirender, sehingga pengirim pertama
     * melihat pratinjau tanpa gambar dan baru penerima berikutnya kebagian
     * versi lengkapnya.
     *
     * Hanya berkas milik situs sendiri yang bisa diukur; gambar dari alamat
     * luar dilewati.
     *
     * @return array{width: ?int, height: ?int}
     */
    protected static function dimensions(string $url): array
    {
        if (array_key_exists($url, self::$sizes)) {
            return self::$sizes[$url];
        }

        $blank = ['width' => null, 'height' => null];

        // Dibandingkan pada ruas path-nya saja, bukan alamat penuh: disk
        // `public` bisa menghasilkan alamat absolut (produksi, memakai APP_URL)
        // atau relatif (`/storage/...`), sementara di sini alamatnya sudah
        // terlanjur diabsolutkan. Path-nya sama pada kedua bentuk itu.
        $base = self::path(Storage::disk('public')->url(''));

        if ($base === '' || ! Str::startsWith(self::path($url), $base)) {
            return self::$sizes[$url] = $blank;
        }

        $path = rawurldecode(Str::after(self::path($url), $base));

        // Field gambar juga menerima alamat yang diketik admin sendiri, jadi
        // path-nya tidak boleh dipercaya keluar dari akar disk.
        if (Str::contains($path, '..')) {
            return self::$sizes[$url] = $blank;
        }

        $file = Storage::disk('public')->path($path);

        // Berkas bisa saja sudah dihapus sementara path-nya masih tersimpan di
        // baris berita/halaman — itu tidak boleh menggagalkan render halaman.
        $size = is_file($file) ? @getimagesize($file) : false;

        return self::$sizes[$url] = $size === false
            ? $blank
            : ['width' => (int) $size[0], 'height' => (int) $size[1]];
    }

    /** Ruas path sebuah alamat, tanpa skema & host. */
    protected static function path(string $url): string
    {
        return (string) (parse_url($url, PHP_URL_PATH) ?: '');
    }

    /** Waktu terbit/ubah dalam ISO-8601, satu-satunya bentuk yang dibaca Facebook. */
    protected static function timestamp(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }
}
