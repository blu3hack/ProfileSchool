<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyatukan dua sumber gambar yang dipakai situs:
 *  - URL eksternal penuh (mis. placeholder Unsplash) → dipakai apa adanya;
 *  - path hasil unggahan admin (mis. `uploads/2026/foto.jpg`) → dijadikan
 *    URL publik lewat disk `public`.
 */
class MediaUrl
{
    public static function resolve(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/storage/', 'data:'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
