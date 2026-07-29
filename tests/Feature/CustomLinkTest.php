<?php

namespace Tests\Feature;

use App\Models\CustomLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Menu tambahan bikinan admin: satu baris `custom_links` harus sampai ke
 * dropdown "Lainnya" di navbar sekaligus section kartu di atas footer —
 * keduanya membaca prop `extraLinks` yang sama.
 *
 * Yang dijaga di sini justru pemisahannya: menu tambahan TIDAK boleh ikut
 * masuk `navLinks`, karena jumlahnya bebas bertambah dan akan memenuhi
 * baris navbar bila tercampur.
 */
class CustomLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function link(array $attributes = []): CustomLink
    {
        return CustomLink::create([
            'icon' => '📄',
            'label' => 'Jadwal KBM',
            'href' => 'https://drive.example.com/jadwal',
            'description' => 'Jadwal pelajaran semester berjalan.',
            'accent' => 'sky',
            'sort_order' => 1,
            'is_active' => true,
            ...$attributes,
        ]);
    }

    public function test_menu_tambahan_sampai_ke_beranda(): void
    {
        $this->link();

        $this->get('/')->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('extraLinks', 1)
                ->where('extraLinks.0.label', 'Jadwal KBM')
                ->where('extraLinks.0.href', 'https://drive.example.com/jadwal')
        );
    }

    public function test_menu_tambahan_tidak_mencampuri_menu_navbar(): void
    {
        $this->link();

        $navLinks = $this->get('/')->assertOk()->viewData('page')['props']['navLinks'];

        $this->assertNotContains('Jadwal KBM', array_column($navLinks, 'label'));
    }

    public function test_menu_yang_disembunyikan_tidak_ikut_terkirim(): void
    {
        $this->link(['is_active' => false]);

        $this->get('/')->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->has('extraLinks', 0)
        );
    }

    /** Navbar dipakai ulang di semua halaman publik, jadi datanya harus ikut. */
    public function test_halaman_lain_juga_menerima_menu_tambahan(): void
    {
        $this->link();

        foreach (['/berita', '/event', '/galeri'] as $url) {
            $this->get($url)->assertOk()->assertInertia(
                fn (AssertableInertia $page) => $page->has('extraLinks', 1)
            );
        }
    }

    /** Alamat setengah jadi seperti "www.sekolah.id" akan jadi /www.sekolah.id. */
    public function test_target_yang_bukan_alamat_dikenal_ditolak(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/koleksi/customlinks', [
            'icon' => '🔗',
            'label' => 'Salah Ketik',
            'href' => 'www.sekolah.id',
            'accent' => 'mint',
        ])->assertSessionHasErrors('href');

        $this->assertDatabaseCount('custom_links', 0);
    }

    public function test_admin_bisa_menambah_dan_menghapus_menu_tambahan(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/koleksi/customlinks', [
            'icon' => '🎓',
            'label' => 'E-Rapor',
            'href' => '/rapor',
            'description' => 'Portal nilai siswa.',
            'accent' => 'mint',
            'image' => '',
        ])->assertRedirect();

        $link = CustomLink::where('label', 'E-Rapor')->firstOrFail();

        $this->actingAs($admin)->delete("/admin/koleksi/customlinks/{$link->id}")->assertRedirect();

        $this->assertDatabaseMissing('custom_links', ['id' => $link->id]);
    }
}
