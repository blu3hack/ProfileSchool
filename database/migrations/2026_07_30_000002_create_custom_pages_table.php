<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Halaman kustom buatan admin — alamatnya sendiri di akar situs, mis.
 * smpialazka.sch.id/datasiswa.
 *
 * Dua cara mengisinya, dipilih lewat `mode`:
 *  - `builder` → `blocks`, daftar blok terstruktur (teks kaya, gambar, galeri,
 *    kutipan, sematan video, tombol ajakan, kartu/grid);
 *  - `html`    → `html`, kode HTML+CSS yang ditempel/diunggah admin.
 *
 * Isi kolom `html` dan blok `richtext` di dalam `blocks` SELALU sudah melewati
 * App\Support\HtmlSanitizer sebelum tersimpan — lihat catatan di sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('mode')->default('builder');

            // Kepala halaman: label kecil, ringkasan, dan foto latar. Ketiganya
            // opsional — halaman tanpa foto tetap dapat kepala bergradasi.
            $table->string('eyebrow')->nullable();
            $table->text('summary')->nullable();
            $table->string('hero_image')->nullable();

            $table->json('blocks')->nullable();
            $table->longText('html')->nullable();

            // SEO. Dikosongkan → judul & ringkasan halaman yang dipakai.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();

            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
