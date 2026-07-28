<?php

namespace Database\Seeders;

use App\Models\Shortlink;
use Illuminate\Database\Seeder;

/**
 * Memulihkan tautan pendek yang dulu ditangani plugin WordPress
 * "Redirection" — semuanya mati begitu situs pindah ke Laravel.
 *
 * Sengaja memakai `firstOrCreate`, bukan `updateOrCreate`: begitu sebuah
 * slug ada di database, isinya milik admin. Seeder yang ikut jalan tiap
 * deploy tidak boleh mengembalikannya ke tujuan lama hasil impor.
 */
class ShortlinkSeeder extends Seeder
{
    public function run(): void
    {
        $rows = require database_path('seeders/data/shortlinks.php');

        foreach ($rows as $i => $row) {
            Shortlink::firstOrCreate(
                ['slug' => $row['slug']],
                [...$row, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }
}
