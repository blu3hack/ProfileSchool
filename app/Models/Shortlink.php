<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Tautan pendek smpialazka.com/<slug> → URL tujuan di luar situs.
 *
 * `is_active` mematikan tautan tanpa menghapusnya, `hits` mencatat berapa
 * kali tautan dibuka.
 */
class Shortlink extends Model
{
    use Orderable;

    protected $fillable = ['slug', 'target', 'note', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_visited_at' => 'datetime',
        ];
    }

    /**
     * Bentuk baku slug: huruf kecil, tanpa garis miring pembuka/penutup —
     * supaya "/Sanggar/" dan "sanggar" tidak tersimpan sebagai dua baris.
     */
    public static function normalizeSlug(?string $slug): string
    {
        return strtolower(trim((string) $slug, "/ \t\n\r\0\x0B"));
    }

    protected function slug(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => static::normalizeSlug($value));
    }
}
