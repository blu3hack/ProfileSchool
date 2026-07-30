<!DOCTYPE html>
{{-- Mode tampilan dipasang di <html>, bukan di div halaman: dengan begitu latar
     <body>, warna scrollbar, dan elemen yang di-teleport ke body (lightbox
     galeri) ikut berganti. Halaman admin sengaja tidak mendapat atribut ini —
     lihat App\Support\ThemeMode::boot(). --}}
@php($themeBoot = \App\Support\ThemeMode::boot($page['component'] ?? null))
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      @if ($themeBoot) data-theme="{{ $themeBoot['mode'] }}" @endif>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Judul + kartu pratinjau tautan, dicetak server. Sengaja paling atas
             di <head>: perayap WhatsApp/Telegram/Facebook tidak menjalankan
             JavaScript dan hanya membaca potongan awal dokumen. Halaman yang
             tidak menitipkan `meta` (panel admin, login) cukup memakai judul
             bawaan — tidak ada yang membagikan alamatnya. --}}
        @isset($meta)
            @include('partials.meta')
        @else
            <title inertia>{{ config('app.name', 'SMPI Alazka Surabaya') }}</title>
        @endisset

        @if ($themeBoot)
            {{-- Skrip sinkron & sedini mungkin: menetapkan mode final SEBELUM
                 cat pertama, jadi tidak ada kedipan gelap→terang. Pilihan
                 pengunjung menang, kecuali admin mengubah bawaan situs setelah
                 pilihan itu dibuat (stamp server lebih baru). --}}
            <script>
                (function (boot) {
                    var mode = boot.default;

                    try {
                        var saved = JSON.parse(window.localStorage.getItem(boot.storageKey) || 'null');

                        if (saved && (saved.mode === 'dark' || saved.mode === 'light')
                            && Number(saved.stamp) >= boot.stamp) {
                            mode = saved.mode;
                        }
                    } catch (e) {
                        // localStorage diblokir, atau berisi format lama ("dark"
                        // polos tanpa stamp) — pakai bawaan dari panel admin.
                    }

                    if (mode === 'system') {
                        mode = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                    }

                    document.documentElement.dataset.theme = mode;
                    window.__alazkaTheme = { mode: mode, default: boot.default, stamp: boot.stamp, storageKey: boot.storageKey };
                })({!! Illuminate\Support\Js::from($themeBoot) !!});
            </script>
        @endif

        {{-- Ikon tab browser: logo sekolah dari panel admin, dipersegikan
             sekali oleh App\Support\Favicon. --}}
        @php($favicon = \App\Support\Favicon::url())
        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
        @endif

        {{-- Foto hero = elemen LCP beranda. Diletakkan sebelum @vite supaya
             unduhannya berjalan bersamaan dengan bundel JS, bukan menunggu
             Vue selesai merender <img>-nya. --}}
        @isset($heroPreload)
            {{-- `imagesrcset`/`imagesizes` harus sama persis dengan yang dipakai
                 <img> hero, kalau tidak preload-nya memilih kandidat berbeda dan
                 fotonya terunduh dua kali. Lihat Opsi3Controller. --}}
            <link rel="preload" as="image" href="{{ $heroPreload }}" fetchpriority="high"
                @if (! empty($heroPreloadSrcset)) imagesrcset="{{ $heroPreloadSrcset }}" imagesizes="100vw" @endif>
        @endisset

        @vite(['resources/js/app.js'])

        {{-- Berkas halaman yang sedang dibuka. Tanpa ini, browser baru
             menemukannya setelah bundel utama selesai dijalankan — satu
             perjalanan bolak-balik penuh sebelum apa pun bisa dirender.
             Lihat App\Support\PageAssets. --}}
        @php($pageAssets = \App\Support\PageAssets::preloads($page['component'] ?? null))
        @foreach ($pageAssets['css'] as $href)
            <link rel="stylesheet" href="{{ $href }}">
        @endforeach
        @foreach ($pageAssets['js'] as $src)
            <link rel="modulepreload" href="{{ $src }}">
        @endforeach

        @inertiaHead

        {{-- Warna aksen tema pilihan admin (menu "Tema Website").
             Ditaruh setelah @vite agar menimpa nilai bawaan di app.css. --}}
        <style id="admin-theme">{!! \App\Support\ThemePalette::css() !!}</style>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
