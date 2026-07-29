<?php

namespace App\Support;

use GdImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Varian kecil dari gambar yang sudah diunggah admin.
 *
 * `ImageOptimizer` mengurus batas atas — tak ada berkas tersimpan melebihi
 * 1920 px. Yang tidak diurusnya: gambar yang DITAMPILKAN jauh lebih kecil dari
 * itu tetap dikirim pada ukuran penuh. Contoh paling mencolok adalah logo
 * sekolah: berkasnya 528 KB, tapi navbar merendernya pada 40 px dan footer
 * pada 48 px. Pengunjung mengunduh setengah megabita untuk sebuah gambar
 * seukuran kuku jari, di setiap halaman.
 *
 * PNG-nya sendiri tidak bisa dikecilkan di tempat: encoder PNG milik GD justru
 * membuatnya membengkak (528 KB → 599 KB), sehingga `shrinkInPlace()` menolak
 * menulis hasilnya — lihat catatan MIN_SAVING di sana. Jalan keluarnya adalah
 * varian terpisah dalam format WebP, yang jauh lebih efisien untuk gambar
 * bertransparansi.
 *
 * Berkas asli tidak pernah disentuh: path-nya sudah tersimpan sebagai string di
 * banyak tabel, dan admin tetap berhak mengunduh versi penuhnya.
 */
class ImageVariant
{
    private const DIRECTORY = 'variants';

    private const QUALITY = 82;

    /**
     * Lebar untuk `srcset` foto konten (berita, event, galeri, kegiatan).
     * 1920 tidak ikut — itu berkas aslinya sendiri, yang tetap dipakai `src`.
     */
    public const WIDTHS = [480, 960, 1440];

    /** Lebar varian yang sudah diukur, per request. */
    private static array $measured = [];

    /**
     * URL varian WebP dengan sisi terpanjang maksimal `$size` piksel.
     *
     * Selalu mengembalikan sesuatu yang bisa dipasang di `<img src>`: bila
     * variannya gagal dibuat — GD tidak ada, berkasnya rusak, disk penuh —
     * hasilnya jatuh ke URL gambar asli, bukan null.
     */
    public static function url(?string $path, int $size): ?string
    {
        $original = MediaUrl::resolve($path);

        if ($original === null) {
            return null;
        }

        $disk = self::disk();

        // Sumber luar (URL penuh, data URI) tidak bisa diolah di sini.
        if (! $disk->exists($path)) {
            return $original;
        }

        $target = self::target($path, $size);

        if ($disk->exists($target)) {
            return $disk->url($target);
        }

        return self::generate($disk, $path, $target, $size)
            ? $disk->url($target)
            : $original;
    }

    /**
     * Nilai untuk atribut `srcset`, mis. "…-480.webp 480w, …-960.webp 960w".
     *
     * SENGAJA TIDAK MEMBUAT APA PUN. Hanya varian yang sudah ada di disk yang
     * disebut; kalau belum ada, hasilnya null dan browser memakai `src` biasa.
     *
     * Alasannya penting: beranda memuat ~30 gambar. Kalau varian dibuat saat
     * halaman dirender, request pertama setelah deploy harus menjalankan ~90
     * operasi GD secara sinkron — cukup untuk menembus batas waktu PHP dan
     * membuat situs tampak mati. Pembuatannya karena itu terjadi di dua tempat
     * lain: saat admin mengunggah (`warm()` dari MediaController) dan lewat
     * `php artisan images:variants` untuk gambar lama.
     *
     * Lebar yang ditulis adalah lebar SEBENARNYA berkas varian, bukan lebar yang
     * diminta. Varian tidak pernah diperbesar, jadi foto sumber 800 px akan
     * menghasilkan berkas 800 px pada slot 960 — menyebutnya "960w" akan
     * membuat browser memilihnya untuk ruang yang tak sanggup ia isi.
     */
    public static function srcset(?string $path, array $widths = self::WIDTHS): ?string
    {
        if (blank($path) || ! self::disk()->exists($path)) {
            return null;
        }

        $entries = [];

        foreach ($widths as $width) {
            $target = self::target($path, $width);
            $actual = self::widthOf($target);

            // Lebar yang sama bisa muncul dua kali bila sumbernya kecil.
            if ($actual !== null) {
                $entries[$actual] = self::disk()->url($target).' '.$actual.'w';
            }
        }

        return $entries === [] ? null : implode(', ', $entries);
    }

    /**
     * Buat seluruh varian untuk sebuah gambar. Dipanggil saat unggah &
     * dari perintah backfill — tidak pernah saat halaman dirender.
     *
     * @return int jumlah varian yang benar-benar ditulis
     */
    public static function warm(?string $path, array $widths = self::WIDTHS): int
    {
        $disk = self::disk();

        if (blank($path) || ! $disk->exists($path)) {
            return 0;
        }

        $written = 0;

        foreach ($widths as $width) {
            $target = self::target($path, $width);

            if ($disk->exists($target)) {
                continue;
            }

            $written += self::generate($disk, $path, $target, $width) ? 1 : 0;
        }

        return $written;
    }

    /** Buang seluruh varian; dipanggil bila perlu dibangun ulang. */
    public static function flush(): void
    {
        $disk = self::disk();

        $disk->delete($disk->files(self::DIRECTORY));

        self::$measured = [];
    }

    /**
     * Nama berkas ikut path sumber, jadi mengganti gambar lewat panel admin
     * otomatis menghasilkan URL baru — cache browser tidak menyimpan yang lama.
     */
    private static function target(string $path, int $size): string
    {
        return self::DIRECTORY.'/'.md5($path).'-'.$size.'.webp';
    }

    /** Lebar piksel varian, atau null bila berkasnya belum ada. */
    private static function widthOf(string $target): ?int
    {
        if (array_key_exists($target, self::$measured)) {
            return self::$measured[$target];
        }

        $disk = self::disk();

        // `getimagesize` hanya membaca header berkas, bukan mendekode gambarnya.
        $info = $disk->exists($target) ? @getimagesize($disk->path($target)) : false;

        return self::$measured[$target] = $info ? $info[0] : null;
    }

    /**
     * Memo harus dilupakan begitu variannya benar-benar ditulis.
     *
     * Tanpa ini, "belum ada" yang sempat tercatat sebelum `warm()` berjalan
     * akan terus dipakai — `srcset()` mengembalikan null walau berkasnya sudah
     * ada. Terasa di proses yang hidup lama (antrean, Octane) dan langsung
     * ketahuan di test suite yang memakai satu proses untuk semua kasus.
     */
    private static function forget(string $target): void
    {
        unset(self::$measured[$target]);
    }

    private static function disk(): FilesystemAdapter
    {
        return Storage::disk('public');
    }

    private static function generate(FilesystemAdapter $disk, string $path, string $target, int $size): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $disk->makeDirectory(self::DIRECTORY);

        $source = $disk->path($path);
        $image = self::load($source);

        if (! $image instanceof GdImage) {
            return false;
        }

        $longest = max(imagesx($image), imagesy($image));

        // Jangan pernah memperbesar: kalau sumbernya sudah lebih kecil dari
        // target, dipakai apa adanya (tetap dikonversi ke WebP).
        if ($longest > $size) {
            $ratio = $size / $longest;

            $scaled = imagescale(
                $image,
                max(1, (int) round(imagesx($image) * $ratio)),
                max(1, (int) round(imagesy($image) * $ratio)),
            );

            if ($scaled instanceof GdImage) {
                $image = $scaled;
            }
        }

        // Logo lazimnya PNG transparan — tanpa ini latarnya jadi hitam pekat.
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $written = (bool) @imagewebp($image, $disk->path($target), self::QUALITY);

        if ($written) {
            self::forget($target);
        }

        return $written;
    }

    private static function load(string $source): ?GdImage
    {
        $info = @getimagesize($source);

        $image = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => false,
        };

        return $image instanceof GdImage ? $image : null;
    }
}
