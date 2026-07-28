<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Pengecilan gambar: saat diunggah admin maupun lewat perintah backfill. */
class ImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_foto_besar_dikecilkan_dan_disimpan_sebagai_webp(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post('/admin/media', [
            'file' => UploadedFile::fake()->image('foto-kamera.jpg', 6240, 4160),
        ]);

        $response->assertCreated();

        $media = Media::sole();

        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertSame('image/webp', $media->mime);
        Storage::disk('public')->assertExists($media->path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($media->path));

        $this->assertSame(ImageOptimizer::MAX_DIMENSION, $width);
        $this->assertSame(1280, $height, 'Rasio aspek harus dipertahankan.');
    }

    public function test_gambar_kecil_tidak_diperbesar(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/media', [
            'file' => UploadedFile::fake()->image('logo-kecil.png', 400, 300),
        ])->assertCreated();

        [$width, $height] = getimagesize(Storage::disk('public')->path(Media::sole()->path));

        $this->assertSame(400, $width);
        $this->assertSame(300, $height);
    }

    public function test_format_tak_terdukung_tetap_tersimpan_apa_adanya(): void
    {
        Storage::fake('public');

        // GIF dilewati karena GD hanya menyimpan bingkai pertama animasinya.
        $this->actingAs($this->admin())->post('/admin/media', [
            'file' => UploadedFile::fake()->image('animasi.gif', 800, 600),
        ])->assertCreated();

        $media = Media::sole();

        $this->assertStringEndsWith('.gif', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_perintah_backfill_mengecilkan_tanpa_mengubah_nama(): void
    {
        Storage::fake('public');

        $path = 'uploads/2026/07/foto-lama.jpg';
        Storage::disk('public')->put($path, UploadedFile::fake()->image('x.jpg', 5000, 4000)->getContent());

        $media = Media::create([
            'name' => 'foto-lama.jpg',
            'path' => $path,
            'mime' => 'image/jpeg',
            'size' => Storage::disk('public')->size($path),
            'user_id' => $this->admin()->id,
        ]);

        $this->artisan('images:optimize')->assertSuccessful();

        // Nama dan format wajib tetap: path-nya dirujuk berbagai tabel konten.
        Storage::disk('public')->assertExists($path);

        [$width, , $type] = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(ImageOptimizer::MAX_DIMENSION, $width);
        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertSame(Storage::disk('public')->size($path), $media->fresh()->size);
    }

    public function test_dry_run_tidak_mengubah_berkas(): void
    {
        Storage::fake('public');

        $path = 'uploads/2026/07/foto-lama.jpg';
        Storage::disk('public')->put($path, UploadedFile::fake()->image('x.jpg', 5000, 4000)->getContent());
        $before = Storage::disk('public')->size($path);

        $this->artisan('images:optimize', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame($before, Storage::disk('public')->size($path));
    }
}
