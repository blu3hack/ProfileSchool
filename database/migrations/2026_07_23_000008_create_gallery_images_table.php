<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galeri profil sekolah: kumpulan foto & gambar yang tampil sebagai carousel
 * di landing page (section setelah "Prestasi") dan lengkap di halaman /galeri.
 *
 * Tiap gambar membawa judul & keterangan (caption) yang muncul di popup saat
 * fotonya diklik. Dikelola admin lewat menu "Galeri".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('alt')->nullable();
            $table->string('credit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};
