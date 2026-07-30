<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur "Menu Tambahan" dihapus: tidak ada lagi dropdown "Lainnya" di navbar
 * maupun section kartu di atas footer, jadi tabelnya tak lagi dibaca siapa pun.
 *
 * Ikut dibersihkan: baris `site_settings` milik teks pembungkus section itu
 * (`extras_*`). Definisinya sudah hilang dari config/site_content.php, jadi
 * baris-baris itu tidak pernah tampil di form admin maupun terkirim ke halaman
 * — sisa yang cuma menumpuk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('custom_links');

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->whereIn('key', [
                'extras_menu_label',
                'extras_eyebrow',
                'extras_title',
                'extras_title_highlight',
                'extras_description',
            ])->delete();
        }
    }

    /**
     * Skemanya dipulihkan apa adanya (lihat migrasi pembuatnya), tapi ISINYA
     * tidak — baris menu yang sudah dibuat admin hilang bersama tabelnya.
     */
    public function down(): void
    {
        Schema::create('custom_links', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('🔗');
            $table->string('label');
            $table->string('href')->default('#');
            $table->text('description')->nullable();
            $table->string('accent')->default('mint');
            $table->string('image')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
