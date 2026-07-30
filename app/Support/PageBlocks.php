<?php

namespace App\Support;

/**
 * Blok penyusun halaman kustom pada mode "Visual Builder".
 *
 * Satu tempat untuk tiga hal yang harus selalu sepakat:
 *  - TYPES      → jenis blok yang sah (dipakai aturan validasi & form admin);
 *  - clean()    → membakukan blok sebelum disimpan (buang yang kosong,
 *                 bersihkan HTML, batasi pilihan kolom);
 *  - present()  → menyiapkan blok untuk halaman publik (path gambar jadi URL
 *                 siap pakai + srcset, tautan sematan jadi alamat iframe).
 *
 * Bentuk tiap blok — semua kunci opsional kecuali `type`:
 *  ['type' => 'richtext', 'html' => '<p>…</p>']
 *  ['type' => 'heading',  'text' => '…', 'level' => 'h2'|'h3']
 *  ['type' => 'image',    'src' => '…', 'alt' => '…', 'caption' => '…', 'width' => 'normal'|'wide'|'full']
 *  ['type' => 'gallery',  'columns' => 2|3|4, 'items' => [['src' => '…', 'caption' => '…'], …]]
 *  ['type' => 'quote',    'text' => '…', 'cite' => '…']
 *  ['type' => 'embed',    'url' => '…', 'title' => '…', 'caption' => '…']
 *  ['type' => 'cta',      'title' => '…', 'description' => '…', 'label' => '…', 'href' => '…', 'style' => 'primary'|'ghost']
 *  ['type' => 'cards',    'columns' => 2|3|4, 'items' => [['icon' => '…', 'title' => '…', 'description' => '…', 'image' => '…', 'href' => '…'], …]]
 *  ['type' => 'html',     'html' => '<div>…</div>']
 */
class PageBlocks
{
    /** Jenis blok + label & ikonnya untuk tombol "tambah blok" di admin. */
    public const TYPES = [
        'richtext' => ['label' => 'Teks / Paragraf', 'icon' => '📝'],
        'heading' => ['label' => 'Sub Judul', 'icon' => '🔤'],
        'image' => ['label' => 'Gambar', 'icon' => '🖼️'],
        'gallery' => ['label' => 'Galeri Foto', 'icon' => '📸'],
        'quote' => ['label' => 'Kutipan', 'icon' => '❝'],
        'embed' => ['label' => 'Sematan Video / URL', 'icon' => '🎬'],
        'cta' => ['label' => 'Tombol Ajakan', 'icon' => '🔘'],
        'cards' => ['label' => 'Grid / Kartu', 'icon' => '🧩'],
        'html' => ['label' => 'Kode HTML', 'icon' => '🧬'],
    ];

    public const LEVELS = ['h2', 'h3'];

    public const WIDTHS = ['normal', 'wide', 'full'];

    public const COLUMNS = [2, 3, 4];

    public const CTA_STYLES = ['primary', 'ghost'];

    /** Aturan validasi untuk field `blocks` beserta seluruh isinya. */
    public static function rules(): array
    {
        return [
            'blocks' => ['nullable', 'array', 'max:120'],
            'blocks.*.type' => ['required', 'in:'.implode(',', array_keys(static::TYPES))],
            // Batas 200 KB per blok teks: jauh di atas kebutuhan wajar, tapi
            // menahan tempelan raksasa yang bikin halaman admin macet.
            'blocks.*.html' => ['nullable', 'string', 'max:200000'],
            'blocks.*.text' => ['nullable', 'string', 'max:5000'],
            'blocks.*.level' => ['nullable', 'in:'.implode(',', static::LEVELS)],
            'blocks.*.src' => ['nullable', 'string', 'max:2048'],
            'blocks.*.alt' => ['nullable', 'string', 'max:255'],
            'blocks.*.caption' => ['nullable', 'string', 'max:500'],
            'blocks.*.width' => ['nullable', 'in:'.implode(',', static::WIDTHS)],
            'blocks.*.cite' => ['nullable', 'string', 'max:200'],
            'blocks.*.url' => ['nullable', 'string', 'max:2048'],
            'blocks.*.title' => ['nullable', 'string', 'max:200'],
            'blocks.*.description' => ['nullable', 'string', 'max:1000'],
            'blocks.*.label' => ['nullable', 'string', 'max:100'],
            'blocks.*.href' => ['nullable', 'string', 'max:2048'],
            'blocks.*.style' => ['nullable', 'in:'.implode(',', static::CTA_STYLES)],
            'blocks.*.columns' => ['nullable', 'integer', 'in:'.implode(',', static::COLUMNS)],
            'blocks.*.items' => ['nullable', 'array', 'max:60'],
            'blocks.*.items.*.src' => ['nullable', 'string', 'max:2048'],
            'blocks.*.items.*.image' => ['nullable', 'string', 'max:2048'],
            'blocks.*.items.*.caption' => ['nullable', 'string', 'max:500'],
            'blocks.*.items.*.icon' => ['nullable', 'string', 'max:16'],
            'blocks.*.items.*.title' => ['nullable', 'string', 'max:200'],
            'blocks.*.items.*.description' => ['nullable', 'string', 'max:1000'],
            'blocks.*.items.*.href' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * Membakukan daftar blok sebelum disimpan.
     *
     * Blok yang benar-benar kosong dibuang di sini, bukan disembunyikan saat
     * render: admin yang menekan "+ Gambar" lalu berpindah pikiran tidak
     * meninggalkan lubang di halaman.
     */
    public static function clean(?array $blocks): array
    {
        return collect($blocks ?? [])
            ->filter(fn ($block) => is_array($block) && isset(static::TYPES[$block['type'] ?? '']))
            ->map(fn (array $block) => static::normalize($block))
            ->filter(fn (array $block) => static::isFilled($block))
            ->values()
            ->all();
    }

    /** Menyiapkan daftar blok untuk halaman publik. */
    public static function present(?array $blocks): array
    {
        return collect($blocks ?? [])
            ->map(fn (array $block) => match ($block['type']) {
                'image' => [
                    ...$block,
                    'src' => MediaUrl::resolve($block['src'] ?? null),
                    'srcset' => ImageVariant::srcset($block['src'] ?? null),
                ],
                'gallery' => [
                    ...$block,
                    'items' => collect($block['items'] ?? [])
                        ->map(fn (array $item) => [
                            ...$item,
                            'src' => MediaUrl::resolve($item['src'] ?? null),
                            'srcset' => ImageVariant::srcset($item['src'] ?? null),
                        ])
                        ->filter(fn (array $item) => filled($item['src']))
                        ->values()
                        ->all(),
                ],
                'cards' => [
                    ...$block,
                    'items' => collect($block['items'] ?? [])
                        ->map(fn (array $item) => [
                            ...$item,
                            'image' => MediaUrl::resolve($item['image'] ?? null),
                        ])
                        ->values()
                        ->all(),
                ],
                // `src` null → halaman menampilkannya sebagai tautan biasa.
                'embed' => [...$block, 'src' => Embed::src($block['url'] ?? null)],
                default => $block,
            })
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    protected static function normalize(array $block): array
    {
        $type = $block['type'];
        $text = fn (string $key) => trim((string) ($block[$key] ?? ''));
        $columns = fn () => in_array((int) ($block['columns'] ?? 3), static::COLUMNS, true)
            ? (int) $block['columns']
            : 3;

        return match ($type) {
            'richtext', 'html' => [
                'type' => $type,
                // Inilah satu-satunya jalan masuk HTML ke database — lihat
                // App\Support\HtmlSanitizer.
                'html' => HtmlSanitizer::clean($block['html'] ?? null),
            ],
            'heading' => [
                'type' => $type,
                'text' => $text('text'),
                'level' => in_array($block['level'] ?? '', static::LEVELS, true) ? $block['level'] : 'h2',
            ],
            'image' => [
                'type' => $type,
                'src' => $text('src'),
                'alt' => $text('alt'),
                'caption' => $text('caption'),
                'width' => in_array($block['width'] ?? '', static::WIDTHS, true) ? $block['width'] : 'normal',
            ],
            'gallery' => [
                'type' => $type,
                'columns' => $columns(),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($item) => [
                        'src' => trim((string) ($item['src'] ?? '')),
                        'caption' => trim((string) ($item['caption'] ?? '')),
                    ])
                    ->filter(fn (array $item) => $item['src'] !== '')
                    ->values()
                    ->all(),
            ],
            'quote' => [
                'type' => $type,
                'text' => $text('text'),
                'cite' => $text('cite'),
            ],
            'embed' => [
                'type' => $type,
                'url' => $text('url'),
                'title' => $text('title'),
                'caption' => $text('caption'),
            ],
            'cta' => [
                'type' => $type,
                'title' => $text('title'),
                'description' => $text('description'),
                'label' => $text('label'),
                // Tautan tombol tidak melewati penyaring HTML (bukan HTML),
                // jadi skemanya diperiksa sendiri di sini.
                'href' => HtmlSanitizer::link($text('href')),
                'style' => in_array($block['style'] ?? '', static::CTA_STYLES, true) ? $block['style'] : 'primary',
            ],
            'cards' => [
                'type' => $type,
                'columns' => $columns(),
                'items' => collect($block['items'] ?? [])
                    ->map(fn ($item) => [
                        'icon' => trim((string) ($item['icon'] ?? '')),
                        'title' => trim((string) ($item['title'] ?? '')),
                        'description' => trim((string) ($item['description'] ?? '')),
                        'image' => trim((string) ($item['image'] ?? '')),
                        'href' => HtmlSanitizer::link($item['href'] ?? null),
                    ])
                    ->filter(fn (array $item) => filled($item['title']) || filled($item['description']))
                    ->values()
                    ->all(),
            ],
        };
    }

    /** Blok dianggap terisi bila ada satu saja bagian yang berguna. */
    protected static function isFilled(array $block): bool
    {
        return match ($block['type']) {
            // Selain teks, blok HTML boleh berisi elemen yang memang tak
            // bertulisan sama sekali — gambar, sematan, garis pemisah.
            'richtext', 'html' => filled(HtmlSanitizer::plain($block['html']))
                || preg_match('/<(img|iframe|svg|video|audio|hr)\b/i', $block['html']) === 1,
            'heading', 'quote' => filled($block['text']),
            'image' => filled($block['src']),
            'gallery', 'cards' => count($block['items']) > 0,
            'embed' => filled($block['url']),
            'cta' => filled($block['label']) || filled($block['title']),
        };
    }
}
