<?php

namespace App\Support;

use App\Models\News;
use Illuminate\Support\Collection;

/**
 * Sumber tunggal seluruh konten berita.
 *
 * Sejak panel admin aktif, datanya diambil dari tabel `news` lewat model
 * Eloquent. Bentuk data yang dikembalikan (kontrak ke komponen Vue) tetap
 * sama seperti sebelumnya, jadi halaman publik tidak perlu diubah.
 *
 * Bentuk satu artikel:
 *  - slug        : identitas URL, dipakai /berita/{slug}
 *  - category    : Pengumuman | Berita | Kegiatan | Prestasi
 *  - accent      : mint | gold | sky | lilac — memetakan warna neon di UI
 *  - date        : tanggal siap tampil (Bahasa Indonesia)
 *  - publishedAt : ISO-8601, dipakai untuk mengurutkan & atribut <time>
 *  - body        : daftar blok konten (paragraph | heading | list | quote)
 */
class NewsRepository
{
    /** Kategori untuk filter di halaman indeks, lengkap dengan jumlahnya. */
    public static function categories(): array
    {
        return News::published()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['label' => $row->category, 'count' => (int) $row->total])
            ->all();
    }

    /** Seluruh artikel tayang, terbaru lebih dulu. */
    public static function all(): array
    {
        return self::query()->map(fn (News $news) => $news->toArticle())->all();
    }

    /** N artikel terbaru — dipakai slider berita di landing page. */
    public static function latest(int $limit = 6): array
    {
        return News::published()->latestFirst()->limit($limit)->get()
            ->map(fn (News $news) => $news->toArticle())
            ->all();
    }

    /** Satu artikel berdasarkan slug, atau null bila tidak ada. */
    public static function find(string $slug): ?array
    {
        $news = News::published()->where('slug', $slug)->first();

        return $news?->toArticle();
    }

    /**
     * Berita terkait: prioritaskan kategori yang sama, sisanya diisi
     * artikel terbaru lain supaya jumlahnya selalu penuh.
     */
    public static function related(string $slug, int $limit = 3): array
    {
        $current = News::published()->where('slug', $slug)->first();

        if (! $current) {
            return self::latest($limit);
        }

        $others = News::published()->latestFirst()->where('slug', '!=', $slug)->get();

        $sameCategory = $others->where('category', $current->category);
        $rest = $others->where('category', '!=', $current->category);

        return $sameCategory->concat($rest)
            ->take($limit)
            ->map(fn (News $news) => $news->toArticle())
            ->values()
            ->all();
    }

    /**
     * Ringkasan artikel untuk kartu/slider — tanpa `body` agar payload
     * halaman tidak membawa seluruh isi berita yang tidak ditampilkan.
     */
    public static function toCard(array $article): array
    {
        $card = $article;
        unset($card['body']);

        $card['href'] = '/berita/'.$article['slug'];

        return $card;
    }

    /** @return array<int, array<string, mixed>> */
    public static function cards(array $articles): array
    {
        return array_map(fn (array $a) => self::toCard($a), $articles);
    }

    /** @return Collection<int, News> */
    protected static function query(): Collection
    {
        return News::published()->latestFirst()->get();
    }
}
