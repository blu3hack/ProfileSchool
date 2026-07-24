<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seluruh teks bebas di landing page (judul hero, paragraf section,
 * label tombol, dsb) disimpan sebagai pasangan key => value di sini.
 *
 * `group` dipakai admin panel untuk mengelompokkan field per section,
 * `type` menentukan widget yang dirender (text | textarea | image).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('umum');
            $table->string('type')->default('text');
            $table->string('label');
            $table->string('hint')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
