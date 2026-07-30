<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Pembersih HTML untuk isi halaman kustom (mode "Kode HTML" dan blok teks
 * kaya di mode "Visual Builder").
 *
 * Kenapa perlu: apa pun yang admin tempel di sana akhirnya dirender apa adanya
 * lewat `v-html` di halaman publik. Tanpa penyaring, satu `<script>` yang
 * ikut ter-copy dari template asing — atau sekadar `onerror=` pada sebuah
 * `<img>` — akan berjalan di peramban setiap pengunjung (stored XSS), dengan
 * hak yang sama seperti kode situs sendiri: mencuri sesi admin yang sedang
 * login, mengubah tampilan, atau meneruskan pengunjung ke tempat lain.
 *
 * Cara kerjanya: HTML-nya diurai jadi DOM, lalu ditelusuri simpul demi simpul
 * dengan pendekatan DAFTAR IZIN — hanya tag & atribut yang tercantum di bawah
 * yang lolos, sisanya dibuang. Kebalikannya (daftar larangan) tidak dipakai
 * karena selalu ketinggalan dari cara-cara baru menyelipkan skrip.
 *
 * Yang TETAP boleh, karena memang itu gunanya fitur ini:
 *  - seluruh tag tata letak & teks biasa beserta `class`/`style`-nya;
 *  - blok `<style>` — CSS tidak bisa menjalankan JavaScript di peramban modern
 *    (`expression()` milik IE lama tetap dibuang);
 *  - `<iframe>`, tapi hanya menuju layanan sematan yang dikenal (lihat Embed).
 *
 * Yang dibuang beserta isinya: `<script>`, `<object>`, `<embed>`, `<applet>`,
 * `<noscript>`, `<template>`, dan seluruh elemen formulir (tidak ada endpoint
 * yang menerimanya, sementara `action` ke luar situs adalah bahan phishing).
 * Tag yang tidak dikenal tidak dibuang mentah-mentah melainkan "dilepas
 * bungkusnya": isinya tetap tampil, hanya tag pembungkusnya yang hilang.
 */
class HtmlSanitizer
{
    /** Tag yang boleh muncul apa adanya. */
    public const TAGS = [
        'p', 'br', 'hr', 'span', 'div', 'section', 'article', 'aside', 'header',
        'footer', 'main', 'nav', 'figure', 'figcaption', 'blockquote', 'cite',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'mark', 'small',
        'sub', 'sup', 'abbr', 'code', 'pre', 'kbd', 'samp', 'var', 'q', 'time',
        'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'colgroup', 'col',
        'a', 'img', 'picture', 'source', 'video', 'audio', 'track', 'iframe',
        'details', 'summary', 'button', 'label', 'style',
        // Ikon SVG sebaris. `use`, `script`, `foreignObject`, dan `animate*`
        // sengaja di luar daftar: ketiganya bisa memuat/mengubah rujukan luar.
        'svg', 'g', 'defs', 'path', 'circle', 'ellipse', 'rect', 'line',
        'polyline', 'polygon', 'text', 'tspan', 'linearGradient',
        'radialGradient', 'stop', 'clipPath', 'mask', 'symbol',
    ];

    /**
     * Tag yang dibuang BESERTA isinya (bukan cuma dilepas bungkusnya).
     *
     * `title` ikut di sini supaya judul berkas HTML yang diunggah admin tidak
     * bocor sebagai teks di tengah halaman.
     */
    public const DROP_TAGS = [
        'script', 'object', 'embed', 'applet', 'noscript', 'template', 'base',
        'meta', 'link', 'title', 'form', 'input', 'select', 'textarea',
        'option', 'optgroup', 'fieldset', 'legend', 'frame', 'frameset',
        'marquee', 'blink', 'math',
    ];

    /** Atribut yang boleh ada di tag mana pun. */
    public const GLOBAL_ATTRIBUTES = [
        'class', 'id', 'style', 'title', 'dir', 'lang', 'role', 'hidden',
        'colspan', 'rowspan', 'span', 'datetime', 'open', 'for', 'type',
        'align', 'valign', 'width', 'height', 'start', 'reversed', 'value',
    ];

    /** Atribut khusus per tag — di luar daftar global di atas. */
    public const TAG_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel', 'download', 'hreflang'],
        'img' => ['src', 'srcset', 'sizes', 'alt', 'loading', 'decoding', 'fetchpriority'],
        'source' => ['src', 'srcset', 'sizes', 'media'],
        'iframe' => ['src', 'allow', 'allowfullscreen', 'loading', 'referrerpolicy', 'frameborder'],
        'video' => ['src', 'poster', 'controls', 'autoplay', 'muted', 'loop', 'playsinline', 'preload'],
        'audio' => ['src', 'controls', 'autoplay', 'muted', 'loop', 'preload'],
        'track' => ['src', 'kind', 'srclang', 'label', 'default'],
    ];

    /** Atribut yang isinya berupa alamat dan wajib diperiksa skemanya. */
    public const URL_ATTRIBUTES = ['href', 'src', 'poster', 'srcset', 'xlink:href'];

    /** Skema alamat yang aman untuk `href`. */
    public const URL_SCHEMES = ['http', 'https', 'mailto', 'tel', 'sms', 'whatsapp'];

    /**
     * Membersihkan sebongkah HTML dan mengembalikan versi yang aman dirender.
     *
     * Idempoten: menjalankannya pada hasil keluarannya sendiri tidak mengubah
     * apa pun. Itu penting karena isi tersimpan sudah bersih, tapi jalur render
     * membersihkannya sekali lagi sebagai pengaman berlapis.
     */
    public static function clean(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        // Tanpa penanda encoding, libxml menganggap masukan ISO-8859-1 dan
        // huruf beraksen/emoji jadi kacau. Simpul PI-nya sendiri tidak ikut
        // keluar karena hanya elemen & teks yang diserialisasi ulang.
        $previous = libxml_use_internal_errors(true);

        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">'.$html,
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return '';
        }

        $output = '';

        // `<head>` ikut ditelusuri karena berkas HTML yang diunggah admin
        // menaruh `<style>`-nya di sana; sisa isi head (meta/link/title)
        // memang tidak masuk daftar izin dan otomatis terbuang.
        foreach (['head', 'body'] as $container) {
            $node = $dom->getElementsByTagName($container)->item(0);

            if (! $node) {
                continue;
            }

            foreach (iterator_to_array($node->childNodes) as $child) {
                $output .= static::render($dom, $child);
            }
        }

        return trim($output);
    }

    /**
     * Satu alamat yang aman dipasang di `href`, atau string kosong bila tidak.
     *
     * Dipakai untuk field tautan yang TIDAK berupa HTML — tombol ajakan dan
     * kartu di penyusun blok. Tanpa ini `javascript:alert(1)` yang ditempel ke
     * kolom "URL tombol" akan berjalan saat pengunjung menekannya, sama
     * berbahayanya dengan `<script>` di badan halaman.
     */
    public static function link(?string $url): string
    {
        return static::url((string) $url, 'a') ?? '';
    }

    /** Teks polos dari HTML — dipakai untuk ringkasan & meta description. */
    public static function plain(?string $html): string
    {
        $text = html_entity_decode(
            strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', (string) $html) ?? ''),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    /**
     * Menyalin satu simpul (beserta anak-anaknya) ke HTML yang sudah bersih.
     *
     * Mengembalikan string kosong bila simpulnya harus dibuang. Elemen yang
     * tidak dikenal tetap menyumbang isinya — bungkusnya saja yang hilang.
     */
    protected static function render(DOMDocument $dom, DOMNode $node): string
    {
        if ($node instanceof DOMText) {
            return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        if (! $node instanceof DOMElement) {
            // Komentar, PI, CDATA — tak ada gunanya di halaman publik.
            return '';
        }

        $tag = strtolower($node->nodeName);

        if (in_array($tag, static::DROP_TAGS, true)) {
            return '';
        }

        // Nama tag dibandingkan tanpa memandang besar-kecil huruf, tapi yang
        // ditulis ulang adalah ejaan resminya (mis. `linearGradient`) —
        // penting untuk SVG, yang case-sensitive.
        $allowed = static::allowedTag($tag);

        if ($tag === 'style') {
            $css = static::css($node->textContent ?? '');

            return $css === '' ? '' : '<style>'.$css.'</style>';
        }

        $inner = '';

        foreach (iterator_to_array($node->childNodes) as $child) {
            $inner .= static::render($dom, $child);
        }

        if ($allowed === null) {
            return $inner;
        }

        $attributes = static::attributes($node, $tag);

        if ($attributes === null) {
            // Elemen yang atributnya justru jadi alasan dibuang, mis. iframe
            // ke layanan yang tidak dikenal.
            return '';
        }

        // Tag kosong (void element) tidak boleh punya penutup.
        if (in_array($tag, ['br', 'hr', 'img', 'source', 'track', 'col'], true)) {
            return '<'.$allowed.$attributes.'>';
        }

        return '<'.$allowed.$attributes.'>'.$inner.'</'.$allowed.'>';
    }

    /** Ejaan resmi tag bila diizinkan, null bila tidak dikenal. */
    protected static function allowedTag(string $tag): ?string
    {
        foreach (static::TAGS as $allowed) {
            if (strtolower($allowed) === $tag) {
                return $allowed;
            }
        }

        return null;
    }

    /**
     * Merangkai ulang atribut satu elemen. Null berarti elemennya sendiri
     * tidak layak dirender.
     */
    protected static function attributes(DOMElement $node, string $tag): ?string
    {
        $allowed = [...static::GLOBAL_ATTRIBUTES, ...(static::TAG_ATTRIBUTES[$tag] ?? [])];
        $pairs = [];

        foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = $attribute->nodeValue ?? '';

            // Penangan event (`onclick`, `onerror`, `onload`, …) — jalur XSS
            // paling umum setelah `<script>`.
            if (str_starts_with($name, 'on')) {
                continue;
            }

            // `data-*` & `aria-*` tidak berbahaya tanpa skrip dan sering
            // dipakai template HTML untuk penanda/aksesibilitas.
            $isPassthrough = str_starts_with($name, 'data-') || str_starts_with($name, 'aria-');

            if (! $isPassthrough && ! in_array($name, $allowed, true) && ! static::isSvgAttribute($name)) {
                continue;
            }

            if (in_array($name, static::URL_ATTRIBUTES, true)) {
                $value = $name === 'srcset'
                    ? static::srcset($value)
                    : (static::url($value, $tag) ?? '');

                if ($value === '') {
                    // `<iframe>`/`<img>` tanpa alamat sah tidak ada gunanya;
                    // pada tag lain cukup atributnya yang hilang.
                    if (in_array($tag, ['iframe', 'img', 'source'], true)) {
                        return null;
                    }

                    continue;
                }
            }

            if ($name === 'style') {
                $value = static::inlineStyle($value);

                if ($value === '') {
                    continue;
                }
            }

            $pairs[$name] = $value;
        }

        // Tab baru tanpa `rel` membocorkan halaman asal ke situs tujuan dan
        // memberinya akses `window.opener`.
        if ($tag === 'a' && ($pairs['target'] ?? '') === '_blank') {
            $pairs['rel'] = 'noopener noreferrer';
        }

        if ($tag === 'iframe') {
            if (($pairs['src'] ?? '') === '') {
                return null;
            }

            $pairs['loading'] ??= 'lazy';
            $pairs['referrerpolicy'] = 'strict-origin-when-cross-origin';
        }

        if ($tag === 'img') {
            $pairs['loading'] ??= 'lazy';
            $pairs['alt'] ??= '';
        }

        $html = '';

        foreach ($pairs as $name => $value) {
            $html .= ' '.$name.'="'.htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"';
        }

        return $html;
    }

    /** Atribut khas SVG (`d`, `viewBox`, `fill`, `stroke-width`, …). */
    protected static function isSvgAttribute(string $name): bool
    {
        static $svg = [
            'viewbox', 'xmlns', 'fill', 'fill-rule', 'fill-opacity', 'stroke',
            'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
            'stroke-opacity', 'clip-rule', 'clip-path', 'd', 'cx', 'cy', 'r',
            'rx', 'ry', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'points', 'transform',
            'opacity', 'offset', 'stop-color', 'stop-opacity', 'gradientunits',
            'gradienttransform', 'preserveaspectratio', 'text-anchor',
            'font-size', 'font-weight', 'font-family', 'letter-spacing',
            'vector-effect', 'mask', 'patternunits',
        ];

        return in_array($name, $svg, true);
    }

    /**
     * Alamat yang lolos: http/https, mailto/tel/sms/whatsapp, alamat relatif
     * (`/berita`, `foto.jpg`, `#bagian`), dan gambar data-URI selain SVG.
     * Sisanya — terutama `javascript:` — dibuang.
     */
    protected static function url(string $value, string $tag): ?string
    {
        $url = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? '');

        if ($url === '') {
            return null;
        }

        // Gambar tersemat. SVG dikecualikan: berkasnya bisa memuat skrip, dan
        // sebagai data-URI di `<img>` tetap lebih baik tidak diizinkan.
        if (str_starts_with(strtolower($url), 'data:')) {
            return preg_match('#^data:image/(png|jpe?g|gif|webp|avif);base64,[a-z0-9+/=\s]+$#i', $url) === 1
                ? $url
                : null;
        }

        // Relatif atau jangkar — tidak punya skema, jadi aman.
        if (! preg_match('#^([a-z][a-z0-9+.-]*):#i', $url, $match)) {
            return $url;
        }

        $scheme = strtolower($match[1]);

        if (! in_array($scheme, static::URL_SCHEMES, true)) {
            return null;
        }

        // Sematan hanya dari layanan yang dikenal — pembatas terpenting pada
        // `<iframe>`, yang kalau tidak akan bisa memuat halaman mana pun.
        if ($tag === 'iframe' && ! Embed::isAllowed($url)) {
            return null;
        }

        return $url;
    }

    /** `srcset` = daftar "url lebar", tiap alamatnya diperiksa sendiri. */
    protected static function srcset(string $value): string
    {
        $candidates = [];

        foreach (preg_split('/\s*,\s*/', trim($value)) ?: [] as $candidate) {
            $parts = preg_split('/\s+/', trim($candidate)) ?: [];
            $url = static::url($parts[0] ?? '', 'img');

            if ($url === null) {
                continue;
            }

            $descriptor = isset($parts[1]) && preg_match('/^\d+(\.\d+)?[wx]$/', $parts[1]) ? ' '.$parts[1] : '';
            $candidates[] = $url.$descriptor;
        }

        return implode(', ', $candidates);
    }

    /**
     * Isi blok `<style>`. CSS-nya dibiarkan utuh — memang itu yang diminta
     * fitur "HTML+CSS kustom" — kecuali tiga hal: `@import` (memuat berkas
     * dari luar), `expression()` (eksekusi skrip di IE lama), dan `url(...)`
     * ber-skema aneh seperti `javascript:`.
     */
    protected static function css(string $css): string
    {
        $clean = preg_replace(
            [
                '/@import\b[^;]*;?/i',
                '/expression\s*\(/i',
                '/behavior\s*:[^;}]*/i',
                '/-moz-binding\s*:[^;}]*/i',
                '#url\(\s*[\'"]?\s*(?!data:image/(?:png|jpe?g|gif|webp|avif)[;,])(?:javascript|vbscript|data|blob|file)\s*:[^)]*\)#i',
            ],
            '',
            $css,
        ) ?? '';

        // `</style` di dalam CSS akan menutup blok lebih awal saat diserialisasi
        // ulang dan sisanya bocor sebagai HTML.
        $clean = preg_replace('#</\s*style#i', '', $clean) ?? '';

        return trim($clean);
    }

    /** Aturan yang sama untuk `style="…"` sebaris. */
    protected static function inlineStyle(string $style): string
    {
        return static::css(str_replace(['<', '>'], '', $style));
    }
}
