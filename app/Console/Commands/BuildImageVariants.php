<?php

namespace App\Console\Commands;

use App\Support\ImageVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat varian ukuran (`srcset`) untuk gambar yang sudah tersimpan.
 *
 * Unggahan baru sudah membuat variannya sendiri lewat MediaController. Perintah
 * ini untuk berkas yang masuk sebelum fitur ini ada — dan wajib dijalankan
 * sekali setelah deploy, karena `ImageVariant::srcset()` sengaja hanya menyebut
 * varian yang SUDAH ada di disk. Selama belum dijalankan situs tetap normal:
 * `srcset` bernilai null dan browser memakai `src` ukuran penuh seperti dulu.
 *
 * Berkas asli tidak pernah disentuh, jadi perintah ini aman diulang.
 */
class BuildImageVariants extends Command
{
    protected $signature = 'images:variants
                            {--fresh : Hapus seluruh varian lalu bangun ulang dari awal}';

    protected $description = 'Bangun varian ukuran gambar untuk srcset (jalankan sekali setelah deploy)';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        if ($this->option('fresh')) {
            ImageVariant::flush();
            $this->comment('Varian lama dihapus.');
        }

        $files = collect($disk->allFiles('uploads'))
            ->filter(fn (string $file) => preg_match('/\.(jpe?g|png|webp)$/i', $file));

        if ($files->isEmpty()) {
            $this->info('Tidak ada gambar untuk diproses.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        $written = 0;

        foreach ($files as $file) {
            $written += ImageVariant::warm($file);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $total = collect($disk->files('variants'))
            ->sum(fn (string $file) => $disk->size($file));

        $this->info(sprintf(
            '%d varian baru dari %d gambar. Total ukuran varian: %s.',
            $written,
            $files->count(),
            $total >= 1048576 ? round($total / 1048576, 1).' MB' : round($total / 1024).' KB',
        ));

        return self::SUCCESS;
    }
}
