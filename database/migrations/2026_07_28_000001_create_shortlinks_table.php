<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan pendek sekolah: smpialazka.com/<slug> yang meneruskan ke Google
 * Form, Drive, Zoom, dan sejenisnya.
 *
 * Menggantikan plugin WordPress "Redirection" yang ikut mati saat situs
 * pindah ke Laravel. Tautan lama diimpor lewat ShortlinkSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shortlinks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->text('target');
            $table->string('note')->nullable();

            // Dipakai admin untuk melihat tautan mana yang masih dipakai.
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_visited_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlinks');
    }
};
