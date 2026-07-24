<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berita / artikel. `body` menyimpan blok konten terstruktur
 * (paragraph | heading | list | quote) sehingga editor admin bisa
 * menyusun artikel tanpa HTML mentah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category')->default('Berita');
            $table->string('icon')->default('📰');
            $table->string('accent')->default('mint');
            $table->text('excerpt')->nullable();
            $table->string('author')->nullable();
            $table->string('read_time')->nullable();
            $table->string('image')->nullable();
            $table->string('image_caption')->nullable();
            $table->json('tags')->nullable();
            $table->json('body')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
