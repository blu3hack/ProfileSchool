<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu tambahan yang dibuat admin sendiri — halaman/dokumen/formulir di luar
 * struktur tetap landing page (mis. "Jadwal KBM", "E-Rapor", "Brosur PPDB").
 *
 * Bedanya dengan `nav_links`: `nav_links` menunjuk ke section beranda yang
 * memang sudah ada dan jumlahnya tetap, sedangkan tabel ini bebas bertambah.
 * Karena itu isinya tidak ikut memenuhi navbar — semuanya ditampung satu
 * dropdown "Lainnya" plus satu section kartu di atas footer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_links', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('🔗');
            $table->string('label');

            // Bebas: #section beranda, /halaman-internal, https://…, mailto:, tel:
            $table->string('href')->default('#');
            $table->text('description')->nullable();
            $table->string('accent')->default('mint');
            $table->string('image')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_links');
    }
};
