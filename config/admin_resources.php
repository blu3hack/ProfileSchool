<?php

use App\Models\Achievement;
use App\Models\Activity;
use App\Models\ContactInfo;
use App\Models\HeroSlide;
use App\Models\NavLink;
use App\Models\Pillar;
use App\Models\SocialLink;
use App\Models\Stat;

/**
 * Definisi koleksi konten yang dikelola panel admin lewat satu
 * controller & satu halaman Vue generik (Admin/Resource.vue).
 *
 * Setiap koleksi cukup dideklarasikan di sini: label, model, kolom tabel,
 * dan daftar field beserta aturan validasinya. Menambah jenis konten baru
 * tidak butuh controller atau halaman baru.
 *
 * type field: text | textarea | number | select | tags | image | boolean
 */
$accents = [
    ['value' => 'mint', 'label' => 'Mint / Aqua'],
    ['value' => 'gold', 'label' => 'Emas'],
    ['value' => 'sky', 'label' => 'Ungu / Volt'],
    ['value' => 'lilac', 'label' => 'Magenta / Plasma'],
];

return [

    'heroslides' => [
        'label' => 'Slide Beranda',
        'singular' => 'Slide Beranda',
        'icon' => '🖼️',
        'description' => 'Foto latar hero yang bergantian otomatis dengan efek fade, masing-masing dengan profil singkatnya.',
        'model' => HeroSlide::class,
        'columns' => ['image', 'title', 'eyebrow'],
        'fields' => [
            ['name' => 'image', 'label' => 'Foto', 'type' => 'image', 'rules' => ['required', 'string', 'max:2048'], 'hint' => 'Lanskap, disarankan minimal 1920px.'],
            ['name' => 'eyebrow', 'label' => 'Label Kecil', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'hint' => 'Contoh: Gedung Utama, Laboratorium.'],
            ['name' => 'title', 'label' => 'Judul Profil', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
            ['name' => 'description', 'label' => 'Deskripsi Profil', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:500']],
            ['name' => 'alt', 'label' => 'Teks Alternatif Foto', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:200'], 'hint' => 'Kosongkan untuk memakai judul.'],
            ['name' => 'credit', 'label' => 'Kredit Foto', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120'], 'hint' => 'Kosongkan bila memakai foto sendiri.'],
        ],
    ],

    'stats' => [
        'label' => 'Statistik Beranda',
        'singular' => 'Statistik',
        'icon' => '📊',
        'description' => 'Angka ringkas yang tampil di bawah judul hero, mis. "1.200+ Siswa Aktif".',
        'model' => Stat::class,
        'columns' => ['value', 'label', 'hint'],
        'fields' => [
            ['name' => 'value', 'label' => 'Angka', 'type' => 'text', 'rules' => ['required', 'string', 'max:50'], 'hint' => 'Contoh: 1.200+'],
            ['name' => 'label', 'label' => 'Keterangan', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
            ['name' => 'hint', 'label' => 'Catatan Kecil', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'hint' => 'Baris kecil di bawah keterangan.'],
        ],
    ],

    'pillars' => [
        'label' => 'Keunggulan (Pilar)',
        'singular' => 'Pilar Keunggulan',
        'icon' => '🏛️',
        'description' => 'Kartu keunggulan sekolah pada section "Keunggulan Kami".',
        'model' => Pillar::class,
        'columns' => ['icon', 'title', 'accent', 'span'],
        'fields' => [
            ['name' => 'icon', 'label' => 'Ikon (emoji)', 'type' => 'text', 'rules' => ['required', 'string', 'max:16'], 'default' => '📖'],
            ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            ['name' => 'points', 'label' => 'Poin Singkat', 'type' => 'tags', 'rules' => ['nullable', 'array'], 'hint' => 'Tekan Enter untuk menambah poin.'],
            ['name' => 'accent', 'label' => 'Warna Aksen', 'type' => 'select', 'options' => $accents, 'rules' => ['required', 'in:mint,gold,sky,lilac'], 'default' => 'mint'],
            ['name' => 'span', 'label' => 'Lebar Kartu', 'type' => 'select', 'options' => [
                ['value' => 'sm', 'label' => 'Kecil (2 kolom)'],
                ['value' => 'lg', 'label' => 'Besar (3 kolom)'],
            ], 'rules' => ['required', 'in:sm,lg'], 'default' => 'sm'],
            ['name' => 'image', 'label' => 'Gambar Pendukung', 'type' => 'image', 'rules' => ['nullable', 'string', 'max:2048'], 'hint' => 'Opsional.'],
        ],
    ],

    'activities' => [
        'label' => 'Kegiatan',
        'singular' => 'Kegiatan',
        'icon' => '🎯',
        'description' => 'Kegiatan harian & ekstrakurikuler pada section "Kegiatan".',
        'model' => Activity::class,
        'columns' => ['icon', 'title', 'schedule', 'accent'],
        'fields' => [
            ['name' => 'icon', 'label' => 'Ikon (emoji)', 'type' => 'text', 'rules' => ['required', 'string', 'max:16'], 'default' => '🕌'],
            ['name' => 'title', 'label' => 'Nama Kegiatan', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
            ['name' => 'schedule', 'label' => 'Jadwal', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120'], 'hint' => 'Contoh: Selasa & Kamis · 15.00'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            ['name' => 'accent', 'label' => 'Warna Aksen', 'type' => 'select', 'options' => $accents, 'rules' => ['required', 'in:mint,gold,sky,lilac'], 'default' => 'mint'],
            ['name' => 'image', 'label' => 'Foto Kegiatan', 'type' => 'image', 'rules' => ['nullable', 'string', 'max:2048'], 'hint' => 'Opsional.'],
        ],
    ],

    'achievements' => [
        'label' => 'Prestasi',
        'singular' => 'Prestasi',
        'icon' => '🏆',
        'description' => 'Capaian siswa pada section "Jejak Prestasi".',
        'model' => Achievement::class,
        'columns' => ['icon', 'title', 'level', 'year', 'student'],
        'fields' => [
            ['name' => 'icon', 'label' => 'Ikon (emoji)', 'type' => 'text', 'rules' => ['required', 'string', 'max:16'], 'default' => '🥇'],
            ['name' => 'title', 'label' => 'Judul Prestasi', 'type' => 'text', 'rules' => ['required', 'string', 'max:200']],
            ['name' => 'year', 'label' => 'Tahun', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10'], 'default' => '2026'],
            ['name' => 'level', 'label' => 'Tingkat', 'type' => 'select', 'options' => [
                ['value' => 'Sekolah', 'label' => 'Sekolah'],
                ['value' => 'Kabupaten/Kota', 'label' => 'Kabupaten/Kota'],
                ['value' => 'Provinsi', 'label' => 'Provinsi'],
                ['value' => 'Nasional', 'label' => 'Nasional'],
                ['value' => 'Internasional', 'label' => 'Internasional'],
            ], 'rules' => ['nullable', 'string', 'max:50'], 'default' => 'Nasional'],
            ['name' => 'description', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:1000']],
            ['name' => 'student', 'label' => 'Nama Siswa / Tim', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:120']],
            ['name' => 'grade', 'label' => 'Kelas / Jenjang', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'hint' => 'Contoh: SMP Kelas 8'],
            ['name' => 'image', 'label' => 'Foto Pendukung', 'type' => 'image', 'rules' => ['nullable', 'string', 'max:2048'], 'hint' => 'Foto penyerahan piala / sertifikat.'],
        ],
    ],

    'contacts' => [
        'label' => 'Kontak',
        'singular' => 'Kontak',
        'icon' => '📞',
        'description' => 'Daftar kontak yang tampil di footer semua halaman.',
        'model' => ContactInfo::class,
        'columns' => ['icon', 'label', 'value'],
        'fields' => [
            ['name' => 'icon', 'label' => 'Ikon (emoji)', 'type' => 'text', 'rules' => ['required', 'string', 'max:16'], 'default' => '📍'],
            ['name' => 'label', 'label' => 'Label', 'type' => 'text', 'rules' => ['required', 'string', 'max:60'], 'hint' => 'Contoh: Alamat, Telepon, Email'],
            ['name' => 'value', 'label' => 'Isi', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            ['name' => 'href', 'label' => 'Tautan', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'hint' => 'Opsional. Contoh: tel:+622287654321 atau mailto:info@alazka.sch.id'],
        ],
    ],

    'socials' => [
        'label' => 'Media Sosial',
        'singular' => 'Media Sosial',
        'icon' => '🌐',
        'description' => 'Tautan media sosial sekolah di footer.',
        'model' => SocialLink::class,
        'columns' => ['label', 'href'],
        'fields' => [
            ['name' => 'label', 'label' => 'Nama Platform', 'type' => 'text', 'rules' => ['required', 'string', 'max:60']],
            ['name' => 'href', 'label' => 'URL', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'default' => 'https://'],
        ],
    ],

    'navlinks' => [
        'label' => 'Menu Navigasi',
        'singular' => 'Menu',
        'icon' => '🧭',
        'description' => 'Item menu di navbar dan footer. Hash menunjuk ke section landing page.',
        'model' => NavLink::class,
        'columns' => ['label', 'hash'],
        'fields' => [
            ['name' => 'label', 'label' => 'Teks Menu', 'type' => 'text', 'rules' => ['required', 'string', 'max:60']],
            ['name' => 'hash', 'label' => 'Target', 'type' => 'text', 'rules' => ['required', 'string', 'max:60'], 'default' => '#beranda', 'hint' => 'Contoh: #beranda, #berita, #prestasi'],
        ],
    ],
];
