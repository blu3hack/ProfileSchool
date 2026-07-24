<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel konten berulang landing page: statistik, pilar keunggulan,
 * kegiatan, prestasi, kontak, sosial media, dan menu navigasi.
 *
 * Semua tabel punya pola sama: `sort_order` untuk urutan tampil dan
 * `is_active` sebagai sakelar tampil/sembunyi tanpa perlu menghapus data.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Statistik hero: "1.200+ Siswa Aktif".
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('label');
            $table->string('hint')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Empat pilar keunggulan.
        Schema::create('pillars', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('📖');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('points')->nullable();
            $table->string('accent')->default('mint');
            $table->string('span')->default('sm');
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kegiatan / ekstrakurikuler.
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('🕌');
            $table->string('title');
            $table->string('schedule')->nullable();
            $table->text('description')->nullable();
            $table->string('accent')->default('mint');
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Prestasi siswa.
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('🥇');
            $table->string('year')->nullable();
            $table->string('level')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('student')->nullable();
            $table->string('grade')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kontak di footer.
        Schema::create('contact_infos', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('📍');
            $table->string('label');
            $table->text('value');
            $table->string('href')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tautan media sosial.
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('href')->default('#');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Menu navigasi navbar & footer.
        Schema::create('nav_links', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('hash')->default('#beranda');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_links');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('contact_infos');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('pillars');
        Schema::dropIfExists('stats');
    }
};
