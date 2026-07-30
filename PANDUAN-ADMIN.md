# Panduan Panel Admin — Alazka Profile

Seluruh isi halaman publik kini tersimpan di database dan bisa diubah lewat
panel admin di `/admin`, tanpa menyentuh kode.

## 1. Menyiapkan (sekali saja)

```bash
php artisan migrate        # membuat tabel konten
php artisan db:seed        # mengisi konten awal + akun admin
php artisan storage:link   # agar gambar hasil unggahan bisa diakses publik
npm run build              # membangun aset frontend
```

Akun bawaan hasil seeding:

| Email                   | Kata sandi |
|-------------------------|------------|
| `admin@alazka.sch.id`   | `password` |

> **Ganti kata sandi ini setelah login pertama** lewat menu **Profil Saya**.

Menambah admin lain:

```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Nama', 'email' => 'email@sekolah.id', 'password' => bcrypt('rahasia'), 'is_admin' => true]);
```

## 2. Masuk

Buka `/login`. User tanpa penanda `is_admin` akan ditolak (403). Setelah lima
kali percobaan gagal, login dikunci sementara.

## 3. Isi Panel

| Menu | Yang bisa diatur |
|------|------------------|
| **Dasbor** | Ringkasan jumlah konten + pintasan |
| **Konten Halaman** | Semua teks halaman utama: badge & judul hero, paragraf, label tombol, judul tiap section, teks PPDB, teks footer, **foto latar beranda**, tautan WhatsApp/telepon/email, dan URL peta |
| **Tema Website** | Warna aksen neon situs (Aqua, Ungu, Magenta, Emas) **dan warna latar utama** untuk **mode gelap dan terang** secara terpisah; permukaan kartu & garis diturunkan otomatis dari warna latar, lengkap dengan pratinjau langsung dan tombol kembalikan ke bawaan |
| **Berita** | Tambah/ubah/hapus artikel, banner, galeri foto, tag, blok isi, sakelar terbit/draf |
| **Halaman Kustom** | Halaman baru dengan alamatnya sendiri (mis. `/datasiswa`) — disusun per blok atau lewat kode HTML, lengkap dengan SEO & sakelar terbit/draf |
| **Pustaka Media** | Semua gambar yang pernah diunggah; bisa dipakai ulang atau dihapus |
| **Statistik Beranda** | Angka "1.200+ Siswa Aktif" dsb. |
| **Keunggulan (Pilar)** | Kartu keunggulan: ikon, judul, deskripsi, poin, warna, lebar kartu |
| **Kegiatan** | Kegiatan & ekstrakurikuler beserta jadwal dan foto |
| **Prestasi** | Capaian siswa: tingkat, tahun, nama siswa, dan foto pendukung |
| **Kontak** | Baris kontak di footer |
| **Media Sosial** | Tautan Instagram, YouTube, dst. |
| **Menu Navigasi** | Item menu navbar & footer |
| **Tautan Pendek** | Alamat singkat `smpialazka.com/<slug>` yang meneruskan ke Google Form, Drive, Zoom, dsb. |
| **Profil Saya** | Nama, email, dan kata sandi akun admin |

Setiap koleksi mendukung **urutan tampil** (tombol ↑ ↓) dan **sakelar
tampil/sembunyi** — menyembunyikan item tidak menghapus datanya.

### Halaman Kustom

Untuk halaman yang tidak muat di struktur tetap situs — data siswa, profil
sekolah, jadwal, syarat pendaftaran. Alamatnya ditentukan sendiri: mengisi slug
`datasiswa` berarti halaman terbuka di `smpialazka.sch.id/datasiswa`, memakai
navbar, footer, dan tema situs seperti halaman lain.

Dua cara mengisinya, bisa ditukar kapan saja (isi keduanya disimpan terpisah,
jadi tidak ada yang hilang saat berpindah):

1. **Visual Builder** — susun halaman dari blok: teks kaya (tebal, miring,
   daftar, tautan, sub judul), gambar, galeri foto, kutipan, sematan
   video/URL, tombol ajakan, grid kartu, dan blok kode HTML. Tiap blok bisa
   dilipat, dipindah (↑ ↓), diduplikasi, atau dihapus.
2. **Kode HTML** — tempel kode, atau unggah berkas `.html` yang sudah
   disiapkan. Berkasnya dibaca langsung di peramban lalu masuk ke editor
   sehingga masih bisa disunting sebelum disimpan.

**Kode HTML selalu disaring lebih dulu.** Yang dibuang: `<script>`, atribut
penangan event (`onclick`, `onerror`, …), tautan `javascript:`, `<iframe>` ke
situs di luar daftar layanan sematan, dan seluruh elemen formulir. Yang tetap
berjalan: semua tag tata letak & teks beserta `class`/`style`-nya, blok
`<style>`, tabel, gambar, ikon SVG, dan sematan dari YouTube, Vimeo, Google
Drive/Docs/Form/Maps, Spotify, dan sejenisnya. Karena itu, kode yang tersimpan
bisa berbeda sedikit dari yang ditempel — yang tampil di editor setelah
disimpan adalah versi final yang dipakai halaman.

Untuk formulir pendaftaran, sematkan **Google Form** lewat blok "Sematan
Video/URL" (atau `<iframe>` di mode HTML), bukan `<form>` sendiri.

Selama masih **draf**, alamatnya menghasilkan 404 bagi pengunjung; admin yang
sedang login tetap bisa membukanya sebagai pratinjau (ada bilah penanda di atas
halaman). Alamat yang sudah dipakai halaman asli situs (`berita`, `admin`, …)
atau tautan pendek akan ditolak, dan sebaliknya — keduanya berbagi ruang
alamat yang sama.

Isi kolom **SEO** bila judul/deskripsi untuk Google & pratinjau WhatsApp perlu
berbeda dari judul halaman; bila dikosongkan, judul dan ringkasan halaman yang
dipakai.

### Tautan Pendek

Menggantikan plugin **Redirection** dari situs WordPress lama; 48 tautan lama
sudah diimpor dan hidup kembali. Slug ditulis **tanpa garis miring dan huruf
kecil** (`sanggar`, bukan `/Sanggar/`) — kolom **Dibuka** menunjukkan berapa
kali tautan itu diklik, berguna untuk membersihkan tautan yang sudah mati.

Slug yang sama dengan halaman asli situs (`berita`, `event`, `galeri`,
`login`, `admin`, …) ditolak, karena halaman aslinya selalu menang. Begitu pula
slug yang sudah dipakai **Halaman Kustom**: keduanya berbagi ruang alamat satu
ruas, dan halaman kustom didahulukan.

## 4. Mengunggah Gambar

Tombol **Unggah Gambar** tersedia di setiap field gambar (banner berita, foto
prestasi, foto kegiatan, foto beranda). Berkas disimpan di
`storage/app/public/uploads/TAHUN/BULAN` dan tercatat di Pustaka Media.

- Format: JPG, PNG, WEBP, GIF, AVIF
- Ukuran maksimal: 5 MB
- Alternatif: pilih dari pustaka, atau tempel URL gambar dari luar

## 5. Pratinjau Tautan di WhatsApp & Media Sosial

Setiap halaman publik mengirim kartu pratinjau (judul, ringkasan, gambar) yang
dibaca WhatsApp, Telegram, Facebook, dan X. Isinya diambil otomatis:

| Halaman | Judul | Deskripsi | Gambar |
|---------|-------|-----------|--------|
| Detail berita | Judul berita | Ringkasan (`excerpt`) | Banner berita |
| Detail event | Judul acara | Ringkasan acara | Banner acara |
| Halaman kustom | Meta Title, atau judul halaman | Meta Description → Ringkasan → kalimat pertama isi | Gambar OG → Gambar Hero |
| Beranda & indeks | Nama sekolah / nama halaman | Deskripsi Meta (SEO) | Slide beranda / foto pertama |

Bila sebuah halaman tidak punya gambar sendiri, dipakai **Gambar Bagikan**
di *Konten Halaman → Identitas*. Isi field itu sekali supaya tidak ada tautan
yang tampil polos. Ukuran ideal **1200×630 px, di bawah 300 KB** — WhatsApp
melewatkan gambar yang terlalu berat dan menampilkan kartu tanpa foto.

### Kalau pratinjaunya masih yang lama

WhatsApp, Telegram, dan Facebook **menyimpan hasil bacaan pertama** sebuah
alamat selama berhari-hari. Mengubah judul atau gambar tidak otomatis mengubah
pratinjau yang sudah tersimpan. Cara menyegarkannya:

1. **Facebook & WhatsApp** — buka [Sharing Debugger](https://developers.facebook.com/tools/debug/),
   tempel alamatnya, tekan **Scrape Again**. WhatsApp mengambil ulang dari sana.
2. **Telegram** — kirim alamatnya ke bot [@WebpageBot](https://t.me/WebpageBot).
3. **Butuh cepat** — bagikan alamat dengan tambahan `?v=2` di belakangnya
   (`…/berita/english-zone?v=2`). Bagi perayap itu alamat baru, jadi dibaca
   dari nol. Tag `canonical` menjaga mesin pencari tetap menganggapnya satu
   halaman yang sama.

Sebelum menuduh cache, pastikan dulu **alamat gambarnya bisa dibuka sendiri**:
salin isi `og:image` (klik kanan → Lihat Sumber Halaman) lalu tempel di jendela
penyamaran. Kalau di sana gambarnya tidak muncul, perayap juga tidak bisa
mengambilnya.

## 6. Struktur Teknis

| Berkas | Peran |
|--------|-------|
| `config/site_content.php` | Definisi + nilai default semua teks halaman. **Menambah teks baru cukup menambah satu baris di sini**, lalu pakai `content.<key>` di komponen Vue |
| `config/admin_resources.php` | Definisi koleksi konten (field, validasi, kolom tabel). Menambah jenis konten baru tidak perlu controller atau halaman baru |
| `app/Support/PageContent.php` | Menggabungkan default config dengan nilai database |
| `app/Support/ThemePalette.php` | Warna aksen tema pilihan admin → CSS yang disuntikkan ke `<head>` (menimpa variabel warna di `app.css`). Ramp shade 200–600 diturunkan otomatis via `color-mix()` |
| `app/Support/SiteInfo.php` | Identitas, menu, kontak, sosial media |
| `app/Support/NewsRepository.php` | Sumber tunggal data berita untuk halaman publik |
| `app/Support/HtmlSanitizer.php` | Penyaring HTML halaman kustom (daftar izin tag & atribut). **Satu-satunya jalan masuk HTML ke database** |
| `app/Support/PageBlocks.php` | Definisi, validasi, dan pembakuan blok halaman kustom — sepadan dengan `PageBlockEditor.vue` di sisi admin |
| `app/Support/Embed.php` | Tautan biasa → alamat yang bisa disematkan `<iframe>`; daftar host-nya sekaligus batas keamanan |
| `app/Http/Controllers/SlugController.php` | Penangkap `/{slug}`: halaman kustom dulu, lalu tautan pendek, lalu 404 |
| `app/Http/Controllers/Admin/` | Controller panel admin |
| `resources/js/Pages/Admin/` | Halaman panel admin (Inertia + Vue) |

Cache: nilai teks halaman di-cache selamanya dan otomatis dibersihkan setiap
kali admin menyimpan perubahan.

## 7. Menjalankan Pengujian

```bash
php artisan test --filter=AdminPanelTest
php artisan test --filter=CustomPageTest
```

`AdminPanelTest` mencakup: proteksi rute, login, edit teks, CRUD prestasi,
pembuatan berita (termasuk slug otomatis & pembersihan blok kosong), draf yang
tidak tampil di publik, serta unggah gambar dan penolakan berkas non-gambar.

`CustomPageTest` mencakup: halaman terbit & draf di alamatnya sendiri, pratinjau
draf untuk admin, hidup berdampingan dengan tautan pendek, penolakan alamat
ganda/terlarang, serta penyaringan HTML (skrip, `onerror`, `javascript:`,
`<iframe>` asing) dan penerjemahan tautan sematan.
