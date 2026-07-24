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
| **Pustaka Media** | Semua gambar yang pernah diunggah; bisa dipakai ulang atau dihapus |
| **Statistik Beranda** | Angka "1.200+ Siswa Aktif" dsb. |
| **Keunggulan (Pilar)** | Kartu keunggulan: ikon, judul, deskripsi, poin, warna, lebar kartu |
| **Kegiatan** | Kegiatan & ekstrakurikuler beserta jadwal dan foto |
| **Prestasi** | Capaian siswa: tingkat, tahun, nama siswa, dan foto pendukung |
| **Kontak** | Baris kontak di footer |
| **Media Sosial** | Tautan Instagram, YouTube, dst. |
| **Menu Navigasi** | Item menu navbar & footer |
| **Profil Saya** | Nama, email, dan kata sandi akun admin |

Setiap koleksi mendukung **urutan tampil** (tombol ↑ ↓) dan **sakelar
tampil/sembunyi** — menyembunyikan item tidak menghapus datanya.

## 4. Mengunggah Gambar

Tombol **Unggah Gambar** tersedia di setiap field gambar (banner berita, foto
prestasi, foto kegiatan, foto beranda). Berkas disimpan di
`storage/app/public/uploads/TAHUN/BULAN` dan tercatat di Pustaka Media.

- Format: JPG, PNG, WEBP, GIF, AVIF
- Ukuran maksimal: 5 MB
- Alternatif: pilih dari pustaka, atau tempel URL gambar dari luar

## 5. Struktur Teknis

| Berkas | Peran |
|--------|-------|
| `config/site_content.php` | Definisi + nilai default semua teks halaman. **Menambah teks baru cukup menambah satu baris di sini**, lalu pakai `content.<key>` di komponen Vue |
| `config/admin_resources.php` | Definisi koleksi konten (field, validasi, kolom tabel). Menambah jenis konten baru tidak perlu controller atau halaman baru |
| `app/Support/PageContent.php` | Menggabungkan default config dengan nilai database |
| `app/Support/ThemePalette.php` | Warna aksen tema pilihan admin → CSS yang disuntikkan ke `<head>` (menimpa variabel warna di `app.css`). Ramp shade 200–600 diturunkan otomatis via `color-mix()` |
| `app/Support/SiteInfo.php` | Identitas, menu, kontak, sosial media |
| `app/Support/NewsRepository.php` | Sumber tunggal data berita untuk halaman publik |
| `app/Http/Controllers/Admin/` | Controller panel admin |
| `resources/js/Pages/Admin/` | Halaman panel admin (Inertia + Vue) |

Cache: nilai teks halaman di-cache selamanya dan otomatis dibersihkan setiap
kali admin menyimpan perubahan.

## 6. Menjalankan Pengujian

```bash
php artisan test --filter=AdminPanelTest
```

Mencakup: proteksi rute, login, edit teks, CRUD prestasi, pembuatan berita
(termasuk slug otomatis & pembersihan blok kosong), draf yang tidak tampil di
publik, serta unggah gambar dan penolakan berkas non-gambar.
