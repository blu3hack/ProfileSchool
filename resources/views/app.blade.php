<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SMPI Alazka Surabaya') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">

        @vite(['resources/js/app.js'])
        @inertiaHead

        {{-- Warna aksen tema pilihan admin (menu "Tema Website").
             Ditaruh setelah @vite agar menimpa nilai bawaan di app.css. --}}
        <style id="admin-theme">{!! \App\Support\ThemePalette::css() !!}</style>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
