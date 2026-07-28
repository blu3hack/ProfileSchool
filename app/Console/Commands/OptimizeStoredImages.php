<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Mengecilkan gambar yang sudah terlanjur tersimpan mentah dari kamera.
 *
 * Unggahan baru sudah dikecilkan otomatis oleh MediaController; perintah ini
 * untuk berkas yang masuk sebelum itu. Nama dan format berkas dipertahankan
 * (lihat ImageOptimizer::shrinkInPlace) agar path yang tersimpan di berbagai
 * tabel konten tetap sahih, jadi tidak ada perubahan database selain
 * penyelarasan kolom `size` pada tabel media.
 */
class OptimizeStoredImages extends Command
{
    protected $signature = 'images:optimize
                            {--dry-run : Tampilkan calon perubahan tanpa menulis berkas}';

    protected $description = 'Kecilkan gambar lama di storage agar tidak memberatkan pemuatan halaman';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');

        $files = collect($disk->allFiles('uploads'))
            ->filter(fn (string $file) => preg_match('/\.(jpe?g|png|webp)$/i', $file));

        if ($files->isEmpty()) {
            $this->info('Tidak ada gambar untuk diproses.');

            return self::SUCCESS;
        }

        $rows = [];
        $before = 0;
        $after = 0;

        foreach ($files as $file) {
            $path = $disk->path($file);
            $sizeBefore = (int) filesize($path);
            $before += $sizeBefore;

            if ($dryRun) {
                $after += $sizeBefore;
                $rows[] = [$file, $this->format($sizeBefore), '(dry-run)', ''];

                continue;
            }

            ImageOptimizer::shrinkInPlace($path);

            clearstatcache(true, $path);
            $sizeAfter = (int) filesize($path);
            $after += $sizeAfter;

            // Berkas dibiarkan utuh bila formatnya tak didukung atau hasil
            // pengecilannya ternyata tidak lebih kecil.
            if ($sizeAfter === $sizeBefore) {
                $rows[] = [$file, $this->format($sizeBefore), 'dilewati', 'tak ada penghematan'];

                continue;
            }

            // Jaga kolom `size` tetap cocok dengan berkas di disk.
            Media::where('path', $file)->update(['size' => $sizeAfter]);

            $rows[] = [
                $file,
                $this->format($sizeBefore),
                $this->format($sizeAfter),
                '-'.round(($sizeBefore - $sizeAfter) / $sizeBefore * 100).'%',
            ];
        }

        $this->table(['Berkas', 'Sebelum', 'Sesudah', 'Hemat'], $rows);

        $this->info(sprintf(
            'Total %s → %s (hemat %s).',
            $this->format($before),
            $this->format($after),
            $before > 0 ? round(($before - $after) / $before * 100).'%' : '0%',
        ));

        if ($dryRun) {
            $this->comment('Mode dry-run: tidak ada berkas yang diubah.');
        }

        return self::SUCCESS;
    }

    private function format(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1).' MB'
            : round($bytes / 1024).' KB';
    }
}
