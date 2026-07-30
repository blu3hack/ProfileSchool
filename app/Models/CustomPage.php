<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use App\Support\ImageVariant;
use App\Support\MediaUrl;
use App\Support\PageBlocks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Halaman kustom buatan admin, beralamat di akar situs — mis.
 * smpialazka.sch.id/datasiswa.
 *
 * `mode` menentukan dari mana isinya dibaca:
 *  - 'builder' → `blocks` (daftar blok, lihat App\Support\PageBlocks);
 *  - 'html'    → `html` (kode HTML+CSS yang ditempel/diunggah admin).
 *
 * Keduanya sudah bersih saat tersimpan; jalur render tetap membersihkannya
 * sekali lagi (lihat `safeHtml()`).
 */
class CustomPage extends Model
{
    public const MODE_BUILDER = 'builder';

    public const MODE_HTML = 'html';

    /** Mode + labelnya untuk pilihan di form admin. */
    public const MODES = [
        self::MODE_BUILDER => 'Visual Builder (susun per blok)',
        self::MODE_HTML => 'Kode HTML (tempel / unggah berkas)',
    ];

    /**
     * Slug satu ruas yang TIDAK boleh dipakai halaman kustom walau rutenya
     * tidak terdaftar di router: berkas & direktori yang dilayani langsung
     * oleh web server.
     *
     * @see reservedSlugs() — daftar lengkapnya termasuk rute asli situs.
     */
    public const RESERVED_PATHS = [
        'storage', 'build', 'vendor', 'up', 'favicon.ico', 'robots.txt',
        'sitemap.xml', 'index.php', 'opsi2', 'welcome',
    ];

    protected $fillable = [
        'slug', 'title', 'mode', 'eyebrow', 'summary', 'hero_image', 'blocks',
        'html', 'meta_title', 'meta_description', 'og_image', 'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /** Terbit & waktunya sudah lewat — satu-satunya yang boleh dilihat publik. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('updated_at')->orderByDesc('id');
    }

    /**
     * Bentuk baku slug: huruf kecil, tanpa garis miring pembuka/penutup —
     * sama seperti Shortlink, karena keduanya berbagi ruang alamat yang sama.
     */
    public static function normalizeSlug(?string $slug): string
    {
        return strtolower(trim((string) $slug, "/ \t\n\r\0\x0B"));
    }

    protected function slug(): Attribute
    {
        return Attribute::make(set: fn (?string $value) => static::normalizeSlug($value));
    }

    /**
     * Slug yang sudah dipegang situs sendiri.
     *
     * Diambil dari router, bukan daftar tetap: rute publik mana pun yang
     * ditambahkan nanti (`/ppdb`, `/kontak`, …) otomatis ikut terlindungi,
     * karena rute selalu menang atas penangkap `/{slug}` di akhir berkas rute.
     *
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        $fromRouter = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => Str::before(trim($route->uri(), '/'), '/'))
            // Ruas berparameter (`{slug}`) justru rute penangkapnya sendiri.
            ->reject(fn (string $segment) => $segment === '' || Str::contains($segment, '{'));

        return $fromRouter
            ->merge(static::RESERVED_PATHS)
            ->map(fn (string $segment) => strtolower($segment))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function isVisible(): bool
    {
        return $this->is_published
            && (! $this->published_at || $this->published_at->isPast());
    }

    /** Tanggal terbit dalam Bahasa Indonesia, mis. "30 Juli 2026". */
    public function formattedDate(): string
    {
        $date = $this->published_at ?? $this->updated_at ?? $this->created_at ?? Carbon::now();

        return $date->locale('id')->translatedFormat('j F Y');
    }

    /**
     * HTML mode "Kode HTML", dibersihkan ulang saat dirender.
     *
     * Isi kolomnya memang sudah disaring sebelum tersimpan. Pembersihan kedua
     * ini murni berlapis: baris yang masuk lewat jalur lain (seeder, impor,
     * tinker, atau daftar izin yang diperketat setelah halaman dibuat) tetap
     * tidak bisa menyelipkan skrip ke halaman publik.
     */
    public function safeHtml(): string
    {
        return HtmlSanitizer::clean($this->html);
    }

    /** Payload lengkap untuk halaman publik. */
    public function toPayload(): array
    {
        return [
            'slug' => $this->slug,
            // Alamat penuh, dipakai tag canonical & og:url — pratinjau
            // WhatsApp/Facebook menolak alamat relatif.
            'url' => url('/'.$this->slug),
            'title' => $this->title,
            'eyebrow' => $this->eyebrow,
            'summary' => $this->summary,
            'heroImage' => MediaUrl::resolve($this->hero_image),
            'heroSrcset' => ImageVariant::srcset($this->hero_image),
            'mode' => $this->mode,
            'blocks' => $this->mode === self::MODE_BUILDER
                ? PageBlocks::present($this->blocks)
                : [],
            'html' => $this->mode === self::MODE_HTML ? $this->safeHtml() : '',
            'date' => $this->formattedDate(),
            'updatedAt' => optional($this->updated_at)->toDateString(),
            'meta' => [
                'title' => $this->meta_title ?: $this->title,
                'description' => $this->metaDescription(),
                'image' => MediaUrl::resolve($this->og_image ?: $this->hero_image),
            ],
        ];
    }

    /**
     * Deskripsi untuk mesin pencari & pratinjau WhatsApp/Facebook. Bila admin
     * tidak mengisinya, diambil dari ringkasan halaman — atau, kalau itu juga
     * kosong, dari kalimat pertama isi halaman.
     */
    public function metaDescription(): ?string
    {
        $description = $this->meta_description ?: $this->summary ?: $this->firstWords();

        return filled($description) ? Str::limit(HtmlSanitizer::plain($description), 300) : null;
    }

    /** Teks pembuka halaman — sumber cadangan meta description. */
    protected function firstWords(): string
    {
        if ($this->mode === self::MODE_HTML) {
            return HtmlSanitizer::plain($this->html);
        }

        foreach ($this->blocks ?? [] as $block) {
            $text = match ($block['type'] ?? '') {
                'richtext', 'html' => HtmlSanitizer::plain($block['html'] ?? ''),
                'heading', 'quote' => (string) ($block['text'] ?? ''),
                'cta' => (string) ($block['description'] ?? ''),
                default => '',
            };

            if (filled($text)) {
                return $text;
            }
        }

        return '';
    }
}
