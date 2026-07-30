{{-- Judul halaman & kartu pratinjau tautan (Open Graph + Twitter Card).

     Seluruhnya dicetak di server dan diletakkan sedini mungkin di <head>.
     Dua alasan, keduanya soal perayap pratinjau (WhatsApp, Telegram,
     Facebook, LinkedIn, X):

      1. Perayap itu tidak menjalankan JavaScript, jadi tag yang dipasang
         komponen <Head> milik Inertia tidak pernah ada baginya;
      2. Sebagian perayap — WhatsApp yang paling ketat — berhenti membaca
         setelah beberapa ratus kilobita pertama dokumen. Tag ini karena itu
         mendahului skrip tema, @vite, dan seluruh preload aset.

     Datanya berasal dari App\Support\PageMeta lewat `->withViewData()` di
     controller halaman yang bersangkutan. --}}

<title inertia>{{ $meta['documentTitle'] }}</title>

@if ($meta['description'])
    <meta name="description" content="{{ $meta['description'] }}">
@endif

<link rel="canonical" href="{{ $meta['url'] }}">

@if ($meta['noindex'])
    <meta name="robots" content="noindex, nofollow">
@endif

{{-- Open Graph memakai atribut `property`. --}}
<meta property="og:type" content="{{ $meta['type'] }}">
<meta property="og:site_name" content="{{ $meta['siteName'] }}">
<meta property="og:locale" content="id_ID">
<meta property="og:url" content="{{ $meta['url'] }}">
<meta property="og:title" content="{{ $meta['title'] }}">

@if ($meta['description'])
    <meta property="og:description" content="{{ $meta['description'] }}">
@endif

@if ($meta['image'])
    <meta property="og:image" content="{{ $meta['image'] }}">
    <meta property="og:image:alt" content="{{ $meta['imageAlt'] }}">
    @if ($meta['imageWidth'] && $meta['imageHeight'])
        {{-- Lihat PageMeta::dimensions(): tanpa ini pembagian pertama sebuah
             tautan baru sering keluar tanpa gambar. --}}
        <meta property="og:image:width" content="{{ $meta['imageWidth'] }}">
        <meta property="og:image:height" content="{{ $meta['imageHeight'] }}">
    @endif
@endif

@if ($meta['type'] === 'article')
    @if ($meta['publishedTime'])
        <meta property="article:published_time" content="{{ $meta['publishedTime'] }}">
    @endif
    @if ($meta['modifiedTime'])
        <meta property="article:modified_time" content="{{ $meta['modifiedTime'] }}">
    @endif
@endif

{{-- Twitter/X memakai `name`, bukan `property` — satu-satunya beda bentuk
     yang penting di antara kedua standar ini. --}}
<meta name="twitter:card" content="{{ $meta['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $meta['title'] }}">

@if ($meta['description'])
    <meta name="twitter:description" content="{{ $meta['description'] }}">
@endif

@if ($meta['image'])
    <meta name="twitter:image" content="{{ $meta['image'] }}">
@endif
