<?php

/**
 * Definisi seluruh field teks/gambar tunggal yang bisa diedit admin.
 *
 * Satu file ini menjadi sumber kebenaran untuk tiga hal sekaligus:
 *  1. nilai default saat seeding pertama kali (`default`);
 *  2. form yang dirender di panel admin (`label`, `type`, `hint`);
 *  3. fallback ketika sebuah key belum ada isinya di database.
 *
 * Menambah field baru cukup dengan menambah satu baris di sini, lalu
 * memakainya di komponen Vue lewat prop `content.<key>`.
 *
 * type: text | textarea | image | url
 */
return [

    'groups' => [
        'identitas' => 'Identitas Sekolah',
        'hero' => 'Beranda (Hero)',
        'keunggulan' => 'Section Keunggulan',
        'berita' => 'Section Berita',
        'event' => 'Section Next Event',
        'kegiatan' => 'Section Kegiatan',
        'prestasi' => 'Section Prestasi',
        'galeri' => 'Section Galeri',
        'ppdb' => 'Section PPDB / Ajakan Daftar',
        'footer' => 'Footer & Kontak Cepat',
    ],

    'fields' => [

        // ------------------------------- Identitas
        ['key' => 'school_name', 'group' => 'identitas', 'type' => 'text', 'label' => 'Nama Sekolah', 'default' => 'Alazka Islamic School'],
        ['key' => 'school_subtitle', 'group' => 'identitas', 'type' => 'text', 'label' => 'Sub-judul Logo', 'hint' => 'Tampil kecil di bawah nama sekolah pada navbar & footer.', 'default' => 'SD & SMP Islam Terpadu'],
        ['key' => 'nav_logo', 'group' => 'identitas', 'type' => 'image', 'label' => 'Logo Sekolah', 'hint' => 'Tampil di navbar & footer. Kosongkan untuk memakai inisial otomatis. Disarankan PNG transparan rasio persegi, minimal 128px.', 'default' => null],
        ['key' => 'nav_cta_label', 'group' => 'identitas', 'type' => 'text', 'label' => 'Teks Tombol "Daftar" (Navbar)', 'hint' => 'Tombol menonjol di ujung kanan navbar. Kosongkan untuk menyembunyikannya.', 'default' => 'Daftar PPDB'],
        ['key' => 'nav_cta_href', 'group' => 'identitas', 'type' => 'text', 'label' => 'Target Tombol "Daftar"', 'hint' => 'Isi #ppdb untuk menggulir ke bagian PPDB, atau tempel URL formulir pendaftaran (mis. https://...) yang akan dibuka di tab baru.', 'default' => '#ppdb'],
        ['key' => 'meta_description', 'group' => 'identitas', 'type' => 'textarea', 'label' => 'Deskripsi Meta (SEO)', 'default' => 'Sekolah Islam terpadu jenjang SD & SMP dengan tahfidz terstruktur, sains modern, dan pembinaan akhlak.'],

        // ------------------------------- Hero
        ['key' => 'hero_badge', 'group' => 'hero', 'type' => 'text', 'label' => 'Badge Atas', 'default' => 'Terakreditasi A · PPDB 2026/2027 Dibuka'],
        ['key' => 'hero_title_1', 'group' => 'hero', 'type' => 'text', 'label' => 'Judul Baris 1', 'default' => "Generasi Qur'ani"],
        ['key' => 'hero_title_2', 'group' => 'hero', 'type' => 'text', 'label' => 'Judul Baris 2 (gradasi neon)', 'default' => 'Berpikir Masa Depan'],
        ['key' => 'hero_title_3', 'group' => 'hero', 'type' => 'text', 'label' => 'Judul Baris 3', 'default' => 'Berhati'],
        ['key' => 'hero_title_highlight', 'group' => 'hero', 'type' => 'text', 'label' => 'Kata Bergaris Sorot', 'hint' => 'Kata terakhir judul yang diberi sorotan warna.', 'default' => 'Tenang'],
        ['key' => 'hero_description', 'group' => 'hero', 'type' => 'textarea', 'label' => 'Paragraf Hero', 'default' => 'Sekolah Islam terpadu jenjang SD & SMP yang memadukan tahfidz terstruktur, sains modern, dan pembinaan akhlak — dalam lingkungan belajar yang teduh, aman, dan menyenangkan bagi ananda.'],
        ['key' => 'hero_cta_primary', 'group' => 'hero', 'type' => 'text', 'label' => 'Teks Tombol Utama', 'default' => 'Daftar Sekarang'],
        ['key' => 'hero_cta_secondary', 'group' => 'hero', 'type' => 'text', 'label' => 'Teks Tombol Kedua', 'default' => 'Jelajahi Sekolah'],
        ['key' => 'hero_image', 'group' => 'hero', 'type' => 'image', 'label' => 'Foto Latar Beranda (Cadangan)', 'hint' => 'Hanya dipakai bila belum ada satu pun slide di menu "Slide Beranda". Disarankan lanskap minimal 1920px.', 'default' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=2400&q=80'],
        ['key' => 'hero_image_alt', 'group' => 'hero', 'type' => 'text', 'label' => 'Teks Alternatif Foto', 'default' => 'Gedung dan area belajar Alazka Islamic School'],
        ['key' => 'hero_image_credit', 'group' => 'hero', 'type' => 'text', 'label' => 'Kredit Foto', 'hint' => 'Kosongkan bila memakai foto sendiri.', 'default' => 'Foto placeholder — Unsplash'],

        // ------------------------------- Keunggulan
        ['key' => 'pillars_eyebrow', 'group' => 'keunggulan', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Keunggulan Kami'],
        ['key' => 'pillars_title', 'group' => 'keunggulan', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Empat Pilar yang Menopang'],
        ['key' => 'pillars_title_highlight', 'group' => 'keunggulan', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Tumbuh Kembang Ananda'],
        ['key' => 'pillars_description', 'group' => 'keunggulan', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => 'Setiap pilar dirancang agar ananda tumbuh seimbang — kuat ilmunya, lembut akhlaknya, dan percaya diri menghadapi tantangan zaman.'],

        // ------------------------------- Berita
        ['key' => 'news_eyebrow', 'group' => 'berita', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Kabar Sekolah'],
        ['key' => 'news_title', 'group' => 'berita', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Berita Terbaru'],
        ['key' => 'news_description', 'group' => 'berita', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => 'Momen, capaian, dan pengumuman terkini dari lingkungan sekolah.'],
        ['key' => 'news_cta', 'group' => 'berita', 'type' => 'text', 'label' => 'Teks Tombol "Lihat Semua"', 'default' => 'Lihat Semua Berita'],

        // ------------------------------- Next Event
        ['key' => 'events_eyebrow', 'group' => 'event', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Agenda Terdekat'],
        ['key' => 'events_title', 'group' => 'event', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Next Event'],
        ['key' => 'events_title_highlight', 'group' => 'event', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Sekolah'],
        ['key' => 'events_description', 'group' => 'event', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => 'Kegiatan yang akan segera dilaksanakan. Catat tanggalnya dan bergabunglah bersama kami.'],
        ['key' => 'events_cta', 'group' => 'event', 'type' => 'text', 'label' => 'Teks Tombol "Lihat Semua"', 'default' => 'Lihat Semua Next Event'],
        ['key' => 'events_empty', 'group' => 'event', 'type' => 'textarea', 'label' => 'Teks Saat Belum Ada Agenda', 'default' => 'Belum ada agenda yang dijadwalkan. Pantau terus halaman ini untuk kegiatan berikutnya.'],

        // ------------------------------- Kegiatan
        ['key' => 'activities_eyebrow', 'group' => 'kegiatan', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Keseharian Siswa'],
        ['key' => 'activities_title', 'group' => 'kegiatan', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Kegiatan yang'],
        ['key' => 'activities_title_highlight', 'group' => 'kegiatan', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Menumbuhkan'],
        ['key' => 'activities_description', 'group' => 'kegiatan', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => 'Dari halaqah tahfidz pagi hingga klub robotik sore hari — setiap kegiatan dirancang menyeimbangkan ruhiyah, nalar, dan kebugaran ananda. Geser kartu untuk menjelajah.'],

        // ------------------------------- Prestasi
        ['key' => 'achievements_eyebrow', 'group' => 'prestasi', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Pencapaian'],
        ['key' => 'achievements_title', 'group' => 'prestasi', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Jejak Prestasi'],
        ['key' => 'achievements_title_highlight', 'group' => 'prestasi', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Siswa SD & SMP'],
        ['key' => 'achievements_description', 'group' => 'prestasi', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => "Buah dari proses belajar yang konsisten — akademik, olahraga, seni, hingga tahfidz Al-Qur'an."],

        // ------------------------------- Galeri
        ['key' => 'gallery_eyebrow', 'group' => 'galeri', 'type' => 'text', 'label' => 'Label Kecil', 'default' => 'Galeri Sekolah'],
        ['key' => 'gallery_title', 'group' => 'galeri', 'type' => 'text', 'label' => 'Judul Section', 'default' => 'Potret Keseharian'],
        ['key' => 'gallery_title_highlight', 'group' => 'galeri', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Profil Sekolah'],
        ['key' => 'gallery_description', 'group' => 'galeri', 'type' => 'textarea', 'label' => 'Deskripsi Section', 'default' => 'Sekilas suasana gedung, kegiatan, dan momen berharga di lingkungan sekolah. Klik foto untuk melihatnya lebih besar.'],
        ['key' => 'gallery_cta', 'group' => 'galeri', 'type' => 'text', 'label' => 'Teks Tombol "Lihat Semua"', 'default' => 'Lihat Semua'],

        // ------------------------------- PPDB
        ['key' => 'ppdb_badge', 'group' => 'ppdb', 'type' => 'text', 'label' => 'Badge', 'default' => 'PPDB Tahun Ajaran 2026/2027'],
        ['key' => 'ppdb_title', 'group' => 'ppdb', 'type' => 'text', 'label' => 'Judul', 'default' => 'Mari Wujudkan Masa Depan'],
        ['key' => 'ppdb_title_highlight', 'group' => 'ppdb', 'type' => 'text', 'label' => 'Judul (gradasi neon)', 'default' => 'Terbaik untuk Ananda'],
        ['key' => 'ppdb_description', 'group' => 'ppdb', 'type' => 'textarea', 'label' => 'Deskripsi', 'default' => 'Pendaftaran gelombang II telah dibuka dengan kuota terbatas. Tersedia beasiswa prestasi akademik dan tahfidz bagi calon siswa terpilih.'],
        ['key' => 'ppdb_primary_label', 'group' => 'ppdb', 'type' => 'text', 'label' => 'Teks Tombol Utama', 'default' => 'Daftar via WhatsApp'],
        ['key' => 'ppdb_primary_href', 'group' => 'ppdb', 'type' => 'url', 'label' => 'Tautan Tombol Utama', 'default' => 'https://wa.me/622287654321'],
        ['key' => 'ppdb_secondary_label', 'group' => 'ppdb', 'type' => 'text', 'label' => 'Teks Tombol Kedua', 'default' => 'Konsultasi Dulu'],

        // ------------------------------- Footer
        ['key' => 'footer_description', 'group' => 'footer', 'type' => 'textarea', 'label' => 'Paragraf Footer', 'default' => "Mendidik generasi Qur'ani yang cerdas, berakhlak mulia, dan siap berkontribusi bagi umat serta bangsa — dengan pendekatan belajar yang hangat dan relevan bagi anak masa kini."],
        ['key' => 'footer_note', 'group' => 'footer', 'type' => 'text', 'label' => 'Catatan Bawah', 'default' => 'Dibangun dengan penuh amanah untuk pendidikan Indonesia.'],
        ['key' => 'quick_whatsapp', 'group' => 'footer', 'type' => 'url', 'label' => 'Tautan WhatsApp', 'default' => 'https://wa.me/622287654321'],
        ['key' => 'quick_phone', 'group' => 'footer', 'type' => 'text', 'label' => 'Nomor Telepon', 'hint' => 'Format tel:, mis. tel:+622287654321', 'default' => 'tel:+622287654321'],
        ['key' => 'quick_email', 'group' => 'footer', 'type' => 'text', 'label' => 'Alamat Email', 'hint' => 'Format mailto:, mis. mailto:info@alazka.sch.id', 'default' => 'mailto:info@alazka.sch.id'],
        ['key' => 'map_embed', 'group' => 'footer', 'type' => 'url', 'label' => 'URL Peta (embed)', 'hint' => 'Di Google Maps: Bagikan → "Sematkan peta" → salin isi src (atau tempel seluruh kode <iframe>). JANGAN tempel link biasa dari address bar / tombol Bagikan → Salin tautan, karena akan ditolak (X-Frame-Options).', 'default' => 'https://www.openstreetmap.org/export/embed.html?bbox=107.5300%2C-6.8800%2C107.5600%2C-6.8600&layer=mapnik'],
    ],
];
