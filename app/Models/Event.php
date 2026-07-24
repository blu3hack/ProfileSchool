<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Agenda kegiatan yang akan dilaksanakan ("Next Event").
 *
 * `body` memakai format blok yang sama dengan berita sehingga admin
 * menyusun deskripsi acara lewat form, bukan HTML mentah:
 *  - ['type' => 'paragraph', 'text' => '...']
 *  - ['type' => 'heading',   'text' => '...']
 *  - ['type' => 'list',      'items' => ['...', '...']]
 *  - ['type' => 'quote',     'text' => '...', 'cite' => '...']
 *
 * `rundown` menyimpan susunan acara: [['time' => '08.00', 'title' => '...']].
 */
class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'slug', 'title', 'category', 'icon', 'accent', 'excerpt', 'location',
        'organizer', 'audience', 'registration_url', 'registration_label',
        'image', 'image_caption', 'tags', 'body', 'rundown', 'gallery',
        'is_published', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'body' => 'array',
            'rundown' => 'array',
            'gallery' => 'array',
            'is_published' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Acara yang belum selesai. Acara berdurasi satu hari dianggap masih
     * "akan datang" sampai hari itu berakhir, supaya tidak hilang dari
     * beranda hanya karena jam mulainya sudah lewat beberapa menit.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('ends_at', '>=', now())
            ->orWhere(fn (Builder $sub) => $sub
                ->whereNull('ends_at')
                ->where('starts_at', '>=', now()->startOfDay())));
    }

    /** Acara yang sudah lewat — dipakai sebagai arsip di halaman indeks. */
    public function scopePast(Builder $query): Builder
    {
        return $query->whereNot(fn (Builder $q) => $q
            ->where('ends_at', '>=', now())
            ->orWhere(fn (Builder $sub) => $sub
                ->whereNull('ends_at')
                ->where('starts_at', '>=', now()->startOfDay())));
    }

    /** Terdekat lebih dulu — urutan alami untuk agenda mendatang. */
    public function scopeSoonest(Builder $query): Builder
    {
        return $query->orderBy('starts_at')->orderBy('id');
    }

    /** Terbaru lebih dulu — urutan alami untuk arsip acara yang sudah lewat. */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('starts_at')->orderByDesc('id');
    }

    public function isUpcoming(): bool
    {
        $end = $this->ends_at ?? $this->starts_at?->copy()->endOfDay();

        return $end ? $end->greaterThanOrEqualTo(now()) : false;
    }

    /** Tanggal siap tampil, mis. "Sabtu, 15 Agustus 2026". */
    public function formattedDate(): string
    {
        $start = $this->starts_at ?? Carbon::now();
        $end = $this->ends_at;

        // Acara lintas hari ditulis sebagai rentang: "15 – 17 Agustus 2026".
        if ($end && ! $end->isSameDay($start)) {
            return $end->isSameMonth($start)
                ? $start->translatedFormat('j').' – '.$end->locale('id')->translatedFormat('j F Y')
                : $start->locale('id')->translatedFormat('j F').' – '.$end->locale('id')->translatedFormat('j F Y');
        }

        return $start->locale('id')->translatedFormat('l, j F Y');
    }

    /** Jam pelaksanaan, mis. "08.00 – 12.00 WIB". */
    public function formattedTime(): string
    {
        $start = $this->starts_at;

        if (! $start) {
            return '';
        }

        $time = $start->format('H.i');

        if ($this->ends_at && $this->ends_at->isSameDay($start)) {
            $time .= ' – '.$this->ends_at->format('H.i');
        }

        return $time.' WIB';
    }

    /** Payload lengkap untuk halaman detail acara. */
    public function toArticle(): array
    {
        return [
            'slug' => $this->slug,
            'category' => $this->category,
            'icon' => $this->icon,
            'accent' => $this->accent,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'date' => $this->formattedDate(),
            'time' => $this->formattedTime(),
            // ISO-8601 lengkap dengan offset — dipakai hitung mundur di klien.
            'startsAt' => optional($this->starts_at)->toIso8601String(),
            'endsAt' => optional($this->ends_at)->toIso8601String(),
            'isUpcoming' => $this->isUpcoming(),
            'location' => $this->location,
            'organizer' => $this->organizer,
            'audience' => $this->audience,
            'registrationUrl' => $this->registration_url,
            'registrationLabel' => $this->registration_label ?: 'Daftar Sekarang',
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
            'rundown' => collect($this->rundown ?? [])
                ->map(fn ($item) => [
                    'time' => $item['time'] ?? '',
                    'title' => $item['title'] ?? '',
                    'description' => $item['description'] ?? null,
                ])
                ->filter(fn ($item) => filled($item['title']))
                ->values()
                ->all(),
            'tags' => $this->tags ?? [],
            'body' => $this->body ?? [],
        ];
    }

    /** Ringkasan untuk kartu — tanpa `body` agar payload ringan. */
    public function toCard(): array
    {
        $card = $this->toArticle();
        unset($card['body'], $card['gallery']);

        $card['href'] = '/event/'.$this->slug;

        return $card;
    }
}
