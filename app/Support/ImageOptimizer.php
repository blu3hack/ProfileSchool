<?php

namespace App\Support;

use GdImage;

/**
 * Mengecilkan gambar agar tidak dikirim mentah dari kamera.
 *
 * Foto ponsel/DSLR lazimnya 6000 px dan 1–2 MB, padahal layar terlebar pun
 * hanya butuh ~1920 px. Dibiarkan mentah, foto hero menjadi elemen LCP yang
 * memakan detik-detik pertama pemuatan halaman.
 *
 * Memakai GD; Imagick tidak tersedia di server produksi.
 */
class ImageOptimizer
{
    /** Sisi terpanjang setelah dikecilkan. */
    public const MAX_DIMENSION = 1920;

    public const QUALITY = 82;

    /**
     * Batas aman agar gambar raksasa tidak menghabiskan memori: GD memuat
     * gambar tanpa kompresi, 4 byte per piksel — 50 MP saja sudah ~200 MB.
     */
    private const MAX_PIXELS = 50_000_000;

    /** GIF sengaja tidak didukung: GD hanya menyimpan bingkai pertamanya. */
    private const SUPPORTED = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];

    /**
     * Kecilkan lalu tulis sebagai WebP. Dipakai untuk unggahan baru, yang
     * belum punya referensi di database sehingga namanya bebas berubah.
     */
    public static function toWebp(string $source, string $destination): bool
    {
        return self::process(
            $source,
            fn (GdImage $image) => imagewebp($image, $destination, self::QUALITY),
        );
    }

    /**
     * Kecilkan di tempat dengan format asli dipertahankan. Dipakai untuk
     * gambar lama: path-nya sudah tersimpan sebagai string di banyak tabel
     * (news, events, hero_slides, gallery_images, …), jadi mengganti nama
     * atau ekstensinya akan memutus referensi yang ada.
     */
    public static function shrinkInPlace(string $path): bool
    {
        $type = self::typeOf($path);

        if ($type === null) {
            return false;
        }

        return self::process($path, fn (GdImage $image) => match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, self::QUALITY),
            IMAGETYPE_PNG => imagepng($image, $path, 8),
            IMAGETYPE_WEBP => imagewebp($image, $path, self::QUALITY),
        });
    }

    /** Muat gambar, siapkan, serahkan ke penulis, lalu bebaskan memorinya. */
    private static function process(string $source, callable $write): bool
    {
        $type = self::typeOf($source);

        if ($type === null) {
            return false;
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        };

        if (! $image instanceof GdImage) {
            return false;
        }

        // prepare() mengambil alih kepemilikan $image dan membebaskan setiap
        // hasil antara, sehingga di sini cukup satu imagedestroy() saja.
        $prepared = self::prepare($image, $source, $type);
        $ok = (bool) $write($prepared);
        imagedestroy($prepared);

        return $ok;
    }

    private static function prepare(GdImage $image, string $source, int $type): GdImage
    {
        // GD membuang metadata EXIF saat menulis ulang. Tanpa penerapan
        // orientasinya lebih dulu, foto potret dari ponsel jadi miring.
        if ($type === IMAGETYPE_JPEG) {
            $image = self::replace($image, self::orient($image, $source));
        }

        $image = self::replace($image, self::scale($image));

        // Jaga transparansi PNG/WebP saat ditulis ulang.
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /** Bebaskan gambar lama bila langkah sebelumnya menghasilkan salinan baru. */
    private static function replace(GdImage $old, GdImage $new): GdImage
    {
        if ($new !== $old) {
            imagedestroy($old);
        }

        return $new;
    }

    private static function scale(GdImage $image): GdImage
    {
        $longest = max(imagesx($image), imagesy($image));

        if ($longest <= self::MAX_DIMENSION) {
            return $image;
        }

        $ratio = self::MAX_DIMENSION / $longest;

        $scaled = imagescale(
            $image,
            (int) round(imagesx($image) * $ratio),
            (int) round(imagesy($image) * $ratio),
        );

        return $scaled instanceof GdImage ? $scaled : $image;
    }

    private static function orient(GdImage $image, string $source): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($source);

        $rotated = match ($exif['Orientation'] ?? 1) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    /** Tipe gambar bila didukung dan ukurannya masuk akal, selain itu null. */
    private static function typeOf(string $path): ?int
    {
        $info = @getimagesize($path);

        if (! $info || ! in_array($info[2], self::SUPPORTED, true)) {
            return null;
        }

        return $info[0] * $info[1] <= self::MAX_PIXELS ? $info[2] : null;
    }
}
