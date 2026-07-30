<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Mengubah tautan yang ditempel admin menjadi alamat yang benar-benar bisa
 * disematkan lewat `<iframe>`.
 *
 * Masalahnya sama seperti peta di footer: tautan yang biasa dibagikan bukan
 * tautan sematan. `youtube.com/watch?v=…` menolak di-frame (`X-Frame-Options`),
 * begitu pula halaman Google Drive/Forms versi biasa. Jadi bentuk umum
 * ditebak dan diterjemahkan di sini, sekali, untuk dipakai bersama oleh blok
 * "Sematan Video/URL" dan penyaring `<iframe>` di HtmlSanitizer.
 *
 * Daftar host-nya juga berfungsi sebagai pembatas keamanan: `<iframe>` di HTML
 * kustom hanya boleh menuju layanan di bawah ini. Tanpa batas itu, satu iframe
 * layar penuh ke situs mana pun cukup untuk membajak tampilan halaman.
 */
class Embed
{
    /**
     * Host yang boleh disematkan. Sub-domain ikut diterima (mis.
     * `www.youtube.com`, `drive.google.com`).
     */
    public const HOSTS = [
        'youtube.com', 'youtube-nocookie.com', 'youtu.be',
        'vimeo.com', 'player.vimeo.com',
        'google.com', 'drive.google.com', 'docs.google.com',
        'calendar.google.com', 'forms.gle', 'maps.google.com',
        'openstreetmap.org', 'facebook.com', 'instagram.com',
        'spotify.com', 'open.spotify.com', 'soundcloud.com',
        'w.soundcloud.com', 'anchor.fm', 'archive.org',
        'canva.com', 'issuu.com', 'slideshare.net', 'dailymotion.com',
    ];

    /** Apakah alamat ini menuju layanan sematan yang dikenal. */
    public static function isAllowed(?string $url): bool
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));

        if ($host === '') {
            return false;
        }

        foreach (static::HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alamat siap pakai untuk `<iframe src>`, atau null bila tautannya tidak
     * bisa disematkan — pemanggilnya lalu menampilkannya sebagai tautan biasa.
     */
    public static function src(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        // Admin sering menempel seluruh kode sematan, bukan alamatnya saja.
        if (Str::contains($url, '<iframe', ignoreCase: true)
            && preg_match('/src\s*=\s*["\']([^"\']+)["\']/i', $url, $match)) {
            $url = trim($match[1]);
        }

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($id = static::youtubeId($url, $host, $path)) {
            // `-nocookie` tidak memasang cookie pelacak sebelum videonya
            // benar-benar diputar.
            return 'https://www.youtube-nocookie.com/embed/'.$id;
        }

        if (Str::endsWith($host, 'vimeo.com') && ! Str::startsWith($host, 'player.')
            && preg_match('#^/(\d+)#', $path, $match)) {
            return 'https://player.vimeo.com/video/'.$match[1];
        }

        // Berkas Google Drive & dokumen: `/view` dan `/edit` menolak di-frame,
        // `/preview` boleh. Tautan Google Form (`/viewform`) sudah bisa
        // disematkan apa adanya, jadi sengaja tidak tersentuh pola di bawah.
        if (Str::endsWith($host, ['drive.google.com', 'docs.google.com'])) {
            return preg_replace('#/(?:view|edit)(\?.*)?$#', '/preview', $url) ?? $url;
        }

        return static::isAllowed($url) ? $url : null;
    }

    /** ID video dari semua bentuk tautan YouTube yang beredar. */
    protected static function youtubeId(string $url, string $host, string $path): ?string
    {
        if (! Str::endsWith($host, ['youtube.com', 'youtu.be', 'youtube-nocookie.com'])) {
            return null;
        }

        // Tautan pendek menaruh ID langsung di akar (youtu.be/ID); domain
        // penuh selalu lewat salah satu awalan (/embed, /shorts, /live, /v).
        $pattern = Str::endsWith($host, 'youtu.be')
            ? '#^/([A-Za-z0-9_-]{6,})#'
            : '#^/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{6,})#';

        if (preg_match($pattern, $path, $match)) {
            return $match[1];
        }

        // youtube.com/watch?v=ID
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $id = $query['v'] ?? null;

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{6,}$/', $id) ? $id : null;
    }
}
