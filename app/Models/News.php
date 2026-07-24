<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Artikel berita. `body` berisi daftar blok terstruktur sehingga admin
 * menyusun isi artikel lewat form, bukan menulis HTML mentah.
 *
 * Bentuk satu blok:
 *  - ['type' => 'paragraph', 'text' => '...']
 *  - ['type' => 'heading',   'text' => '...']
 *  - ['type' => 'list',      'items' => ['...', '...']]
 *  - ['type' => 'quote',     'text' => '...', 'cite' => '...']
 */
class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'slug', 'title', 'category', 'icon', 'accent', 'excerpt', 'author',
        'read_time', 'image', 'image_caption', 'tags', 'body', 'gallery',
        'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'body' => 'array',
            'gallery' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /** Tanggal siap tampil dalam Bahasa Indonesia, mis. "20 Juli 2026". */
    public function formattedDate(): string
    {
        $date = $this->published_at ?? $this->created_at ?? Carbon::now();

        return $date->locale('id')->translatedFormat('j F Y');
    }

    /** Payload lengkap untuk halaman detail berita. */
    public function toArticle(): array
    {
        return [
            'slug' => $this->slug,
            'category' => $this->category,
            'icon' => $this->icon,
            'accent' => $this->accent,
            'date' => $this->formattedDate(),
            'publishedAt' => optional($this->published_at ?? $this->created_at)->toDateString(),
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'author' => $this->author,
            'readTime' => $this->read_time,
            'image' => MediaUrl::resolve($this->image),
            'imageCaption' => $this->image_caption,
            'gallery' => collect($this->gallery ?? [])
                ->map(fn ($item) => [
                    'src' => MediaUrl::resolve(is_array($item) ? ($item['src'] ?? null) : $item),
                    'caption' => is_array($item) ? ($item['caption'] ?? null) : null,
                ])
                ->filter(fn ($item) => filled($item['src']))
                ->values()
                ->all(),
            'tags' => $this->tags ?? [],
            'body' => $this->body ?? [],
        ];
    }

    /** Ringkasan untuk kartu/slider — tanpa `body` agar payload ringan. */
    public function toCard(): array
    {
        $card = $this->toArticle();
        unset($card['body']);

        $card['href'] = '/berita/'.$this->slug;

        return $card;
    }
}
