<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\Opsi3Controller;
use App\Http\Controllers\ShortlinkController;
use Illuminate\Support\Facades\Route;

// ============================ HALAMAN PUBLIK ============================

// Landing page utama — konsep "Neo Cyber Madrasah" (dark, neon, 3D).
Route::get('/', Opsi3Controller::class)->name('home');

// Berita: indeks seluruh berita + halaman detail per artikel.
Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{slug}', [NewsController::class, 'show'])->name('news.show');

// Next Event: agenda kegiatan yang akan dilaksanakan + detail per acara.
Route::get('/event', [EventController::class, 'index'])->name('events.index');
Route::get('/event/{slug}', [EventController::class, 'show'])->name('events.show');

// Galeri: seluruh foto profil sekolah — tujuan tombol "Lihat Semua" di beranda.
Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index');

// Alias lama untuk landing utama.
Route::get('/opsi3', Opsi3Controller::class)->name('opsi3');

// ============================== OTENTIKASI ==============================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ============================= PANEL ADMIN ==============================
// Semua rute di bawah butuh login + status administrator.

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', DashboardController::class)->name('dashboard');

    // Teks & gambar tunggal tiap section landing page.
    Route::get('/konten', [SettingController::class, 'edit'])->name('content.edit');
    Route::put('/konten', [SettingController::class, 'update'])->name('content.update');

    // Warna tema (aksen neon) untuk mode gelap & terang.
    Route::get('/tema', [ThemeController::class, 'edit'])->name('theme.edit');
    Route::put('/tema', [ThemeController::class, 'update'])->name('theme.update');

    // Berita.
    Route::get('/berita', [AdminNewsController::class, 'index'])->name('news.index');
    Route::get('/berita/tambah', [AdminNewsController::class, 'create'])->name('news.create');
    Route::post('/berita', [AdminNewsController::class, 'store'])->name('news.store');
    Route::get('/berita/{news}/ubah', [AdminNewsController::class, 'edit'])->name('news.edit');
    Route::put('/berita/{news}', [AdminNewsController::class, 'update'])->name('news.update');
    Route::delete('/berita/{news}', [AdminNewsController::class, 'destroy'])->name('news.destroy');
    Route::patch('/berita/{news}/terbit', [AdminNewsController::class, 'togglePublish'])->name('news.toggle');

    // Agenda "Next Event".
    Route::get('/event', [AdminEventController::class, 'index'])->name('events.index');
    Route::get('/event/tambah', [AdminEventController::class, 'create'])->name('events.create');
    Route::post('/event', [AdminEventController::class, 'store'])->name('events.store');
    Route::get('/event/{event}/ubah', [AdminEventController::class, 'edit'])->name('events.edit');
    Route::put('/event/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/event/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
    Route::patch('/event/{event}/terbit', [AdminEventController::class, 'togglePublish'])->name('events.toggle');

    // Pustaka media (unggah gambar).
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::get('/media/daftar', [MediaController::class, 'list'])->name('media.list');
    Route::delete('/media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Profil admin.
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Koleksi konten generik: statistik, pilar, kegiatan, prestasi,
    // kontak, media sosial, dan menu navigasi.
    Route::prefix('koleksi/{resource}')->name('resources.')->group(function () {
        Route::get('/', [ResourceController::class, 'index'])->name('index');
        Route::post('/', [ResourceController::class, 'store'])->name('store');
        Route::put('/{id}', [ResourceController::class, 'update'])->name('update');
        Route::delete('/{id}', [ResourceController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/urutan', [ResourceController::class, 'move'])->name('move');
        Route::patch('/{id}/tampil', [ResourceController::class, 'toggle'])->name('toggle');
    });
});

// ============================= TAUTAN PENDEK ============================
// WAJIB terdaftar paling akhir: rute ini menangkap sisa alamat satu ruas
// yang tidak cocok dengan rute mana pun, mis. /sanggar → Google Form.
//
// Daftar tautannya dikelola admin lewat menu "Tautan Pendek". Slug yang
// bentrok dengan rute di atas ditolak saat disimpan (lihat aturan `not_in`
// pada config/admin_resources.php) karena rute di atas selalu menang.

Route::get('/{slug}', ShortlinkController::class)
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9._-]*')
    ->name('shortlink');
