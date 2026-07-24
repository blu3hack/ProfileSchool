<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Pola bersama semua konten berulang landing page:
 * urut manual lewat `sort_order` dan sakelar tampil `is_active`.
 */
trait Orderable
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Nomor urut berikutnya — dipakai saat admin menambah item baru. */
    public static function nextSortOrder(): int
    {
        return (int) static::query()->max('sort_order') + 1;
    }
}
