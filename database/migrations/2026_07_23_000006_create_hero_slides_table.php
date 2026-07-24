<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slide hero beranda: beberapa foto yang bergantian otomatis, masing-masing
 * membawa "profil" singkat (judul, label, deskripsi) yang ikut berganti.
 *
 * Menggantikan field tunggal `hero_image` di site_settings — field lama tetap
 * dipakai sebagai cadangan bila belum ada satu pun slide yang aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('alt')->nullable();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('credit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
