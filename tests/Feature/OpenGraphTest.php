<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kartu pratinjau tautan (Open Graph & Twitter Card).
 *
 * Seluruh tes di sini memeriksa HTML MENTAH, bukan payload Inertia — dan itu
 * intinya. Perayap WhatsApp, Telegram, dan Facebook tidak menjalankan
 * JavaScript: yang mereka lihat hanya dokumen yang dikirim server. Tag yang
 * cuma ada di props Inertia tidak akan pernah sampai ke mereka.
 *
 * @see App\Support\PageMeta
 */
class OpenGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function article(array $attributes = []): News
    {
        return News::create([
            'slug' => 'english-zone',
            'title' => 'English Zone Perdana Digelar',
            'category' => 'Kegiatan',
            'accent' => 'mint',
            'excerpt' => 'Siswa berlatih percakapan bahasa Inggris setiap Rabu pagi.',
            'image' => 'https://example.test/foto.jpg',
            'body' => [['type' => 'paragraph', 'text' => 'Isi berita.']],
            'is_published' => true,
            'published_at' => now()->subDay(),
            ...$attributes,
        ]);
    }

    /** Berkas JPEG sungguhan seukuran yang diminta. */
    protected function jpeg(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        ob_start();
        imagejpeg($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    public function test_detail_berita_membawa_kartu_pratinjau_di_html_mentah(): void
    {
        $this->article();

        $html = $this->get('/berita/english-zone')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<meta property="og:title" content="English Zone Perdana Digelar">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta property="og:description" content="Siswa berlatih percakapan bahasa Inggris setiap Rabu pagi.">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta property="og:image" content="https://example.test/foto.jpg">',
            $html,
        );
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
    }

    public function test_alamat_pratinjau_selalu_absolut(): void
    {
        $this->article();

        $html = $this->get('/berita/english-zone')->getContent();

        // og:url dipakai WhatsApp & Facebook sebagai kunci cache-nya; alamat
        // relatif membuat kartunya gagal terbentuk sama sekali.
        $this->assertStringContainsString(
            '<meta property="og:url" content="'.config('app.url').'/berita/english-zone">',
            $html,
        );
        $this->assertStringContainsString(
            '<link rel="canonical" href="'.config('app.url').'/berita/english-zone">',
            $html,
        );
    }

    public function test_judul_dokumen_sama_dengan_susunan_di_sisi_klien(): void
    {
        $this->article();

        // Susunan "Judul - Nama Aplikasi" harus identik dengan callback `title`
        // di resources/js/app.js, kalau tidak judul tab berkedip berubah
        // begitu Inertia mengambil alih.
        $this->assertStringContainsString(
            '<title inertia>English Zone Perdana Digelar - '.config('app.name').'</title>',
            $this->get('/berita/english-zone')->getContent(),
        );
    }

    public function test_berita_tanpa_foto_jatuh_ke_gambar_bawaan_situs(): void
    {
        $this->article(['image' => null]);

        $html = $this->get('/berita/english-zone')->getContent();

        // Halaman tanpa foto tetap harus menghasilkan kartu utuh — bukan
        // pratinjau kosong yang tampak seperti tautan rusak.
        $this->assertStringContainsString('<meta property="og:image" content="http', $html);
    }

    public function test_halaman_kustom_memakai_meta_isian_admin(): void
    {
        CustomPage::create([
            'slug' => 'datasiswa',
            'title' => 'Data Siswa',
            'mode' => CustomPage::MODE_BUILDER,
            'blocks' => [['type' => 'heading', 'text' => 'Rekapitulasi', 'level' => 'h2']],
            'meta_title' => 'Data Siswa SMPI Alazka',
            'meta_description' => 'Rekap jumlah siswa per angkatan.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $html = $this->get('/datasiswa')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<meta property="og:title" content="Data Siswa SMPI Alazka">',
            $html,
        );
        $this->assertStringContainsString(
            '<meta property="og:description" content="Rekap jumlah siswa per angkatan.">',
            $html,
        );
    }

    public function test_beranda_dan_indeks_ikut_membawa_kartu(): void
    {
        foreach (['/', '/berita', '/event', '/galeri'] as $url) {
            $this->assertStringContainsString(
                '<meta property="og:title"',
                $this->get($url)->assertOk()->getContent(),
                "Halaman {$url} tidak membawa tag Open Graph.",
            );
        }
    }

    public function test_ukuran_gambar_dibaca_dari_berkas_yang_diunggah(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/kegiatan.jpg', $this->jpeg(1200, 630));

        $this->article(['image' => 'uploads/kegiatan.jpg']);

        $html = $this->get('/berita/english-zone')->getContent();

        // Tanpa dua tag ini pembagian pertama sebuah tautan baru sering keluar
        // tanpa gambar — lihat PageMeta::dimensions().
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="630">', $html);
    }

    public function test_gambar_yang_berkasnya_hilang_tidak_menggagalkan_halaman(): void
    {
        Storage::fake('public');

        // Path masih tersimpan di baris berita, berkasnya sudah tidak ada.
        $this->article(['image' => 'uploads/sudah-dihapus.jpg']);

        $html = $this->get('/berita/english-zone')->assertOk()->getContent();

        $this->assertStringContainsString('<meta property="og:image" content="http', $html);
        $this->assertStringNotContainsString('og:image:width', $html);
    }

    public function test_deskripsi_dibersihkan_dari_html_dan_dipangkas(): void
    {
        $this->article([
            'excerpt' => '<p>Ringkasan <strong>panjang</strong> '.str_repeat('sekali ', 60).'</p>',
        ]);

        $html = $this->get('/berita/english-zone')->getContent();

        // Tag mentah akan tampil apa adanya di kartu pratinjau.
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
        $this->assertStringContainsString('Ringkasan panjang sekali', $html);
        $this->assertStringContainsString('...', $html);
    }
}
