<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\ImageVariant;
use App\Support\PageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Varian gambar kecil untuk elemen yang dirender kecil.
 *
 * Yang dijaga di sini adalah logo: berkas aslinya ratusan KB, tapi navbar
 * merendernya 40 px. Tanpa varian, tiap kunjungan mengunduh berkas penuh.
 */
class ImageVariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /** Tulis PNG asli berukuran `$size` px persegi ke disk publik. */
    protected function png(string $path, int $size = 1024): string
    {
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

        // Beberapa bentuk supaya berkasnya tidak sepenuhnya seragam.
        imagefilledellipse($image, $size / 2, $size / 2, $size, $size, imagecolorallocate($image, 20, 200, 220));

        $full = Storage::disk('public')->path($path);
        @mkdir(dirname($full), 0777, true);
        imagepng($image, $full);

        return $path;
    }

    public function test_varian_lebih_kecil_dari_berkas_asli(): void
    {
        $path = $this->png('uploads/logo.png');
        $disk = Storage::disk('public');

        $url = ImageVariant::url($path, 192);

        $this->assertStringContainsString('.webp', $url);

        $variant = collect($disk->files('variants'))->firstOrFail();

        $this->assertLessThan($disk->size($path), $disk->size($variant));

        // Berkas asli tidak boleh tersentuh — path-nya dipakai banyak tabel
        // dan admin tetap berhak atas versi penuhnya.
        $this->assertTrue($disk->exists($path));
    }

    public function test_varian_dibuat_sekali_lalu_dipakai_ulang(): void
    {
        $path = $this->png('uploads/logo.png');

        $first = ImageVariant::url($path, 192);
        $stamp = Storage::disk('public')->lastModified('variants/'.md5($path).'-192.webp');

        $this->assertSame($first, ImageVariant::url($path, 192));
        $this->assertSame($stamp, Storage::disk('public')->lastModified('variants/'.md5($path).'-192.webp'));
    }

    public function test_gambar_lebih_kecil_dari_target_tidak_diperbesar(): void
    {
        $path = $this->png('uploads/kecil.png', 64);

        ImageVariant::url($path, 192);

        $size = getimagesize(Storage::disk('public')->path('variants/'.md5($path).'-192.webp'));

        $this->assertSame(64, $size[0]);
    }

    public function test_sumber_luar_dikembalikan_apa_adanya(): void
    {
        $url = 'https://images.unsplash.com/photo-123?w=1920';

        $this->assertSame($url, ImageVariant::url($url, 192));
        $this->assertSame([], Storage::disk('public')->files('variants'));
    }

    public function test_path_kosong_menghasilkan_null(): void
    {
        $this->assertNull(ImageVariant::url(null, 192));
        $this->assertNull(ImageVariant::url('', 192));
    }

    public function test_srcset_tidak_membuat_varian_apa_pun(): void
    {
        $path = $this->png('uploads/foto.png', 1600);

        // Inilah pengamannya: beranda memuat ~30 gambar. Kalau srcset() ikut
        // membuat varian, request pertama setelah deploy harus menjalankan
        // puluhan operasi GD sinkron dan berisiko menembus batas waktu PHP.
        $this->assertNull(ImageVariant::srcset($path));
        $this->assertSame([], Storage::disk('public')->files('variants'));
    }

    public function test_srcset_terisi_setelah_varian_dibangun(): void
    {
        $path = $this->png('uploads/foto.png', 1600);

        $this->assertSame(3, ImageVariant::warm($path));

        $srcset = ImageVariant::srcset($path);

        foreach (ImageVariant::WIDTHS as $width) {
            $this->assertStringContainsString($width.'w', $srcset);
        }
    }

    public function test_lebar_yang_ditulis_adalah_lebar_sebenarnya(): void
    {
        // Sumber 600 px: slot 960 & 1440 tidak diperbesar, jadi keduanya
        // menghasilkan berkas 600 px. Menyebutnya "960w"/"1440w" akan membuat
        // browser memilihnya untuk ruang yang tak sanggup ia isi.
        $path = $this->png('uploads/kecil.png', 600);

        ImageVariant::warm($path);

        $srcset = ImageVariant::srcset($path);

        $this->assertStringContainsString('480w', $srcset);
        $this->assertStringContainsString('600w', $srcset);
        $this->assertStringNotContainsString('960w', $srcset);
        $this->assertStringNotContainsString('1440w', $srcset);
    }

    public function test_warm_aman_diulang(): void
    {
        $path = $this->png('uploads/foto.png', 1600);

        $this->assertSame(3, ImageVariant::warm($path));
        $this->assertSame(0, ImageVariant::warm($path));
    }

    public function test_halaman_publik_menerima_logo_versi_kecil(): void
    {
        $path = $this->png('uploads/logo.png');

        SiteSetting::updateOrCreate(['key' => 'nav_logo'], ['value' => $path, 'label' => 'Logo Sekolah']);
        SiteSetting::flush();

        $logo = PageContent::all()['nav_logo'];

        $this->assertStringContainsString('/variants/', $logo);
        $this->assertStringEndsWith('.webp', $logo);
    }

    public function test_panel_admin_tetap_melihat_berkas_asli(): void
    {
        $path = $this->png('uploads/logo.png');

        SiteSetting::updateOrCreate(['key' => 'nav_logo'], ['value' => $path, 'label' => 'Logo Sekolah']);
        SiteSetting::flush();

        $field = collect(PageContent::schema())
            ->flatMap(fn (array $group) => $group['fields'])
            ->firstWhere('key', 'nav_logo');

        // Admin mengelola berkas aslinya, bukan turunannya — kalau tidak,
        // penyimpanan berikutnya akan menimpa path asli dengan path varian.
        $this->assertSame($path, $field['value']);
        $this->assertStringNotContainsString('/variants/', $field['preview']);
    }
}
