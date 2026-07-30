<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\Shortlink;
use App\Models\User;
use App\Support\Embed;
use App\Support\HtmlSanitizer;
use App\Support\PageBlocks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman kustom buatan admin: alamatnya sendiri di akar situs (/datasiswa),
 * dua mode pengisian, penyaringan HTML, dan hidup berdampingan dengan tautan
 * pendek yang berbagi ruang alamat yang sama.
 */
class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    protected function page(array $attributes = []): CustomPage
    {
        return CustomPage::create([
            'slug' => 'datasiswa',
            'title' => 'Data Siswa',
            'mode' => CustomPage::MODE_BUILDER,
            'blocks' => [['type' => 'heading', 'text' => 'Rekapitulasi', 'level' => 'h2']],
            'is_published' => true,
            'published_at' => now()->subDay(),
            ...$attributes,
        ]);
    }

    // ============================== HALAMAN PUBLIK ==============================

    public function test_halaman_terbit_tampil_di_alamatnya(): void
    {
        $this->page();

        $this->get('/datasiswa')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Halaman')
                ->where('page.title', 'Data Siswa')
                ->where('draft', false)
                // Navbar & footer ikut terkirim seperti halaman lain.
                ->has('navLinks')
                ->has('contacts')
                ->has('socials'));
    }

    public function test_halaman_draf_menghasilkan_404_bagi_pengunjung(): void
    {
        $this->page(['is_published' => false]);

        $this->get('/datasiswa')->assertNotFound();
    }

    public function test_halaman_dengan_tanggal_terbit_di_masa_depan_belum_tampil(): void
    {
        $this->page(['published_at' => now()->addWeek()]);

        $this->get('/datasiswa')->assertNotFound();
    }

    public function test_admin_bisa_melihat_pratinjau_draf(): void
    {
        $this->page(['is_published' => false]);

        $this->actingAs($this->admin())
            ->get('/datasiswa')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Halaman')->where('draft', true));
    }

    public function test_slug_tak_dikenal_menghasilkan_404(): void
    {
        $this->get('/halaman-yang-tidak-pernah-dibuat')->assertNotFound();
    }

    public function test_alamat_dibakukan_sehingga_huruf_besar_tetap_ketemu(): void
    {
        $this->page();

        $this->get('/DataSiswa')->assertOk();
    }

    public function test_tautan_pendek_tetap_jalan_di_ruang_alamat_yang_sama(): void
    {
        $this->page();
        Shortlink::create(['slug' => 'sanggar', 'target' => 'https://forms.gle/contoh']);

        $this->get('/datasiswa')->assertOk();
        $this->get('/sanggar')->assertRedirect('https://forms.gle/contoh');
    }

    public function test_halaman_asli_situs_tidak_tertelan_rute_penangkap(): void
    {
        // Slug yang lolos ke database lewat jalur lain tetap tidak boleh
        // membajak halaman aslinya — rute di atas selalu menang.
        $this->page(['slug' => 'berita']);

        $this->get('/berita')->assertInertia(fn ($page) => $page->component('Berita/Index'));
    }

    public function test_mode_html_dirender_setelah_disaring(): void
    {
        $this->page([
            'mode' => CustomPage::MODE_HTML,
            'blocks' => [],
            // Sengaja disimpan MENTAH (tanpa lewat controller) untuk menguji
            // lapisan penyaring kedua di jalur render.
            'html' => '<p>Tabel data</p><script>alert(1)</script>',
        ]);

        $this->get('/datasiswa')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('page.html', '<p>Tabel data</p>')
                ->where('page.mode', 'html'));
    }

    public function test_meta_seo_terkirim_ke_halaman(): void
    {
        $this->page([
            'summary' => 'Rekap jumlah siswa per angkatan.',
            'meta_title' => 'Data Siswa SMPI Alazka',
        ]);

        $this->get('/datasiswa')->assertInertia(fn ($page) => $page
            ->where('page.meta.title', 'Data Siswa SMPI Alazka')
            ->where('page.meta.description', 'Rekap jumlah siswa per angkatan.'));
    }

    // ================================ PANEL ADMIN ================================

    public function test_pengunjung_biasa_tidak_bisa_membuka_panel_halaman(): void
    {
        $this->get('/admin/halaman')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/admin/halaman')->assertForbidden();
    }

    public function test_form_tambah_dan_ubah_terbuka_lengkap_dengan_pilihannya(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/halaman/tambah')
            ->assertInertia(fn ($view) => $view
                ->component('Admin/Pages/Form')
                ->where('page', null)
                ->has('options.blockTypes', count(PageBlocks::TYPES))
                ->has('options.modes', 2)
                // Daftar alamat terlarang dipakai form untuk memperingatkan
                // admin sebelum ia menekan simpan.
                ->has('options.reserved'));

        $this->actingAs($admin)
            ->get("/admin/halaman/{$page->id}/ubah")
            ->assertInertia(fn ($view) => $view
                ->component('Admin/Pages/Form')
                ->where('page.slug', 'datasiswa')
                ->has('page.blocks', 1));
    }

    public function test_admin_bisa_membuat_halaman_mode_builder(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/halaman', [
                'title' => 'Data Siswa',
                'slug' => 'datasiswa',
                'mode' => 'builder',
                'summary' => 'Rekap siswa.',
                'blocks' => [
                    ['type' => 'richtext', 'html' => '<p>Halo <strong>dunia</strong></p><script>alert(1)</script>'],
                    ['type' => 'cta', 'label' => 'Daftar', 'href' => 'javascript:alert(1)'],
                    // Blok kosong harus dibuang, bukan tersimpan sebagai lubang.
                    ['type' => 'heading', 'text' => ''],
                ],
                'is_published' => true,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $page = CustomPage::firstWhere('slug', 'datasiswa');

        $this->assertCount(2, $page->blocks);
        $this->assertSame('<p>Halo <strong>dunia</strong></p>', $page->blocks[0]['html']);
        // Tautan `javascript:` pada tombol dibuang, tombolnya sendiri bertahan.
        $this->assertSame('', $page->blocks[1]['href']);
    }

    public function test_admin_bisa_membuat_halaman_mode_html(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/halaman', [
                'title' => 'Profil Sekolah',
                'slug' => 'profil-sekolah',
                'mode' => 'html',
                'html' => '<style>.k{color:red}</style><div class="k" onclick="jahat()">Isi</div>'
                    .'<script>alert(1)</script><iframe src="https://evil.test"></iframe>',
                'is_published' => true,
            ])
            ->assertSessionHasNoErrors();

        $html = CustomPage::firstWhere('slug', 'profil-sekolah')->html;

        $this->assertStringContainsString('<style>.k{color:red}</style>', $html);
        $this->assertStringContainsString('<div class="k">Isi</div>', $html);
        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('iframe', $html);
    }

    public function test_alamat_dibuat_otomatis_dari_judul_bila_dikosongkan(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/halaman', ['title' => 'Profil Sekolah Kami', 'mode' => 'builder'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('custom_pages', ['slug' => 'profil-sekolah-kami']);
    }

    public function test_alamat_ganda_ditolak(): void
    {
        $this->page();

        $this->actingAs($this->admin())
            ->post('/admin/halaman', ['title' => 'Lain', 'slug' => 'datasiswa', 'mode' => 'builder'])
            ->assertSessionHasErrors('slug');
    }

    public function test_alamat_milik_halaman_asli_situs_ditolak(): void
    {
        foreach (['berita', 'admin', 'login', 'storage'] as $slug) {
            $this->actingAs($this->admin())
                ->post('/admin/halaman', ['title' => 'Uji', 'slug' => $slug, 'mode' => 'builder'])
                ->assertSessionHasErrors('slug');
        }
    }

    public function test_alamat_yang_sudah_dipakai_tautan_pendek_ditolak(): void
    {
        Shortlink::create(['slug' => 'sanggar', 'target' => 'https://forms.gle/contoh']);

        $this->actingAs($this->admin())
            ->post('/admin/halaman', ['title' => 'Sanggar', 'slug' => 'sanggar', 'mode' => 'builder'])
            ->assertSessionHasErrors('slug');
    }

    public function test_tautan_pendek_tidak_boleh_membajak_alamat_halaman(): void
    {
        $this->page();

        $this->actingAs($this->admin())
            ->post('/admin/koleksi/shortlinks', ['slug' => 'datasiswa', 'target' => 'https://forms.gle/contoh'])
            ->assertSessionHasErrors('slug');
    }

    public function test_alamat_boleh_dipakai_ulang_oleh_halamannya_sendiri(): void
    {
        $page = $this->page();

        $this->actingAs($this->admin())
            ->put("/admin/halaman/{$page->id}", [
                'title' => 'Data Siswa Terbaru',
                'slug' => 'datasiswa',
                'mode' => 'builder',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Data Siswa Terbaru', $page->refresh()->title);
    }

    public function test_sakelar_terbit_menyembunyikan_dan_menampilkan_halaman(): void
    {
        $page = $this->page();
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/halaman/{$page->id}/terbit");
        $this->assertFalse($page->refresh()->is_published);

        // Sesi admin ikut menghapus jejaknya supaya yang diuji benar-benar
        // tampilan untuk pengunjung biasa.
        $this->post('/logout');
        $this->get('/datasiswa')->assertNotFound();

        $this->actingAs($admin)->patch("/admin/halaman/{$page->id}/terbit");
        $this->assertTrue($page->refresh()->is_published);
    }

    public function test_admin_bisa_menghapus_halaman(): void
    {
        $page = $this->page();

        $this->actingAs($this->admin())->delete("/admin/halaman/{$page->id}")->assertRedirect();

        $this->assertDatabaseCount('custom_pages', 0);
        $this->get('/datasiswa')->assertNotFound();
    }

    public function test_daftar_halaman_bisa_dicari(): void
    {
        $this->page();
        $this->page(['slug' => 'ekstrakurikuler', 'title' => 'Ekstrakurikuler']);

        $this->actingAs($this->admin())
            ->get('/admin/halaman?search=ekstra')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Pages/Index')
                ->has('pages.data', 1)
                ->where('pages.data.0.slug', 'ekstrakurikuler'));
    }

    // ============================= PENYARING & SEMATAN =============================

    public function test_penyaring_html_membuang_jalur_skrip_dan_menyimpan_tata_letak(): void
    {
        $clean = HtmlSanitizer::clean(
            '<div class="grid" style="gap:1rem"><img src="/storage/a.jpg" onerror="alert(1)">'
            .'<a href="javascript:alert(1)">a</a><a href="/berita" target="_blank">b</a>'
            .'<table><tr><td>1</td></tr></table></div>',
        );

        $this->assertStringContainsString('<div class="grid" style="gap:1rem">', $clean);
        $this->assertStringContainsString('<td>1</td>', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        // Tab baru selalu dapat rel pengaman.
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_penyaring_html_bersifat_idempoten(): void
    {
        $once = HtmlSanitizer::clean('<p>Satu <em>dua</em></p><ul><li>tiga</li></ul>');

        $this->assertSame($once, HtmlSanitizer::clean($once));
    }

    public function test_berkas_html_utuh_diambil_isinya_saja(): void
    {
        $clean = HtmlSanitizer::clean(
            '<!DOCTYPE html><html><head><title>Judul Berkas</title><style>body{margin:0}</style></head>'
            .'<body><h2>Isi</h2></body></html>',
        );

        $this->assertStringContainsString('<style>body{margin:0}</style>', $clean);
        $this->assertStringContainsString('<h2>Isi</h2>', $clean);
        // Judul berkas tidak boleh bocor sebagai teks di tengah halaman.
        $this->assertStringNotContainsString('Judul Berkas', $clean);
    }

    public function test_iframe_hanya_boleh_ke_layanan_sematan_yang_dikenal(): void
    {
        $this->assertStringContainsString(
            'youtube-nocookie.com/embed/abc123',
            HtmlSanitizer::clean('<iframe src="https://www.youtube-nocookie.com/embed/abc123"></iframe>'),
        );

        $this->assertSame('', HtmlSanitizer::clean('<iframe src="https://evil.test/x"></iframe>'));
    }

    public function test_tautan_sematan_diubah_ke_bentuk_yang_bisa_di_frame(): void
    {
        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            Embed::src('https://www.youtube.com/watch?v=dQw4w9WgXcQ'),
        );

        $this->assertSame(
            'https://player.vimeo.com/video/123456789',
            Embed::src('https://vimeo.com/123456789'),
        );

        $this->assertSame(
            'https://drive.google.com/file/d/1ABC/preview',
            Embed::src('https://drive.google.com/file/d/1ABC/view?usp=sharing'),
        );

        $this->assertNull(Embed::src('https://evil.test/video'));
    }

    public function test_blok_sematan_menyimpan_alamat_iframe_di_payload(): void
    {
        $page = $this->page([
            'blocks' => [['type' => 'embed', 'url' => 'https://youtu.be/dQw4w9WgXcQ', 'title' => 'Profil']],
        ]);

        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            $page->toPayload()['blocks'][0]['src'],
        );
    }
}
