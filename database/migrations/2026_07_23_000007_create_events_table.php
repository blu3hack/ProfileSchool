<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agenda / "Next Event": kegiatan yang akan dilaksanakan.
 *
 * Bentuknya menyerupai tabel `news` (blok isi terstruktur + galeri), dengan
 * tambahan kolom waktu pelaksanaan yang dipakai hitung mundur di halaman
 * publik: `starts_at` (wajib) dan `ends_at` (opsional, untuk acara multi-hari).
 * `rundown` menyimpan susunan acara: [['time' => '08.00', 'title' => '...']].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category')->default('Kegiatan');
            $table->string('icon')->default('📅');
            $table->string('accent')->default('mint');
            $table->text('excerpt')->nullable();
            $table->string('location')->nullable();
            $table->string('organizer')->nullable();
            $table->string('audience')->nullable();
            $table->string('registration_url')->nullable();
            $table->string('registration_label')->nullable();
            $table->string('image')->nullable();
            $table->string('image_caption')->nullable();
            $table->json('tags')->nullable();
            $table->json('body')->nullable();
            $table->json('rundown')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
