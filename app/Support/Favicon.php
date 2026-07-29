<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Ikon tab browser, diturunkan dari logo sekolah yang diunggah admin.
 *
 * Logo aslinya berukuran penuh (ratusan KB) dan tidak persegi, sedangkan
 * favicon dirender pada kotak ~16 px. Dipasang apa adanya ia jadi unduhan
 * sia-sia sekaligus gepeng, karena browser memaksa gambarnya menjadi bujur
 * sangkar. Di sini logo dikecilkan sekali ke kanvas persegi transparan,
 * lalu hasilnya dipakai ulang selama logonya tidak berganti.
 *
 * Memakai GD, sama seperti App\Support\ImageOptimizer — Imagick tidak
 * tersedia di server produksi.
 */
class Favicon
{
    /** Sisi kanvas hasil; cukup untuk tab, bookmark, dan layar Retina. */
    private const SIZE = 180;

    private const DIRECTORY = 'favicon';

    /** URL siap pakai untuk <link rel="icon">, atau null bila logo kosong. */
    public static function url(): ?string
    {
        $logo = SiteSetting::value('nav_logo');

        if (blank($logo)) {
            return null;
        }

        $disk = Storage::disk('public');

        // Logo dari sumber luar (URL penuh) tidak bisa diolah di sini.
        if (! $disk->exists($logo)) {
            return MediaUrl::resolve($logo);
        }

        // Nama berkas ikut isi logo, jadi mengganti logo lewat panel admin
        // otomatis menghasilkan URL baru — cache browser tidak menyimpan
        // ikon lama.
        $target = self::DIRECTORY.'/'.md5($logo).'.png';

        if (! $disk->exists($target) && ! self::generate($disk, $logo, $target)) {
            return MediaUrl::resolve($logo);
        }

        return $disk->url($target);
    }

    /** Menulis versi persegi dari logo; false bila GD gagal membacanya. */
    private static function generate(Filesystem $disk, string $logo, string $target): bool
    {
        $disk->makeDirectory(self::DIRECTORY);

        // Sisa ikon dari logo sebelumnya tidak akan dipakai lagi.
        $disk->delete($disk->files(self::DIRECTORY));

        return self::render($disk->path($logo), $disk->path($target));
    }

    private static function render(string $source, string $destination): bool
    {
        $data = @file_get_contents($source);
        $image = $data === false ? false : @imagecreatefromstring($data);

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = self::SIZE / max($width, $height);
        $scaledWidth = max(1, (int) round($width * $scale));
        $scaledHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor(self::SIZE, self::SIZE);

        // Sisi yang lebih pendek disisakan transparan agar logo tetap utuh
        // proporsinya, bukan ditarik memenuhi kotak.
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        imagecopyresampled(
            $canvas,
            $image,
            intdiv(self::SIZE - $scaledWidth, 2),
            intdiv(self::SIZE - $scaledHeight, 2),
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $width,
            $height,
        );

        $written = imagepng($canvas, $destination, 9);

        imagedestroy($image);
        imagedestroy($canvas);

        return $written;
    }
}
