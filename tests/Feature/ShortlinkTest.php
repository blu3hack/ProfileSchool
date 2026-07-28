<?php

namespace Tests\Feature;

use App\Models\Shortlink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tautan pendek smpialazka.com/<slug> — pengalihan publik & kelola admin. */
class ShortlinkTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    protected function link(array $attributes = []): Shortlink
    {
        return Shortlink::create([
            'slug' => 'sanggar',
            'target' => 'https://docs.google.com/forms/d/e/1FAIpQLS/viewform',
            ...$attributes,
        ]);
    }

    public function test_tautan_pendek_mengalihkan_ke_tujuannya(): void
    {
        $link = $this->link();

        $this->get('/sanggar')->assertRedirect($link->target);
    }

    public function test_kunjungan_dihitung_tanpa_mengubah_updated_at(): void
    {
        $link = $this->link();
        $updatedAt = $link->updated_at;

        $this->get('/sanggar');
        $this->get('/sanggar');

        $link->refresh();

        $this->assertSame(2, (int) $link->hits);
        $this->assertNotNull($link->last_visited_at);
        $this->assertEquals($updatedAt, $link->updated_at);
    }

    public function test_tautan_nonaktif_tidak_mengalihkan(): void
    {
        $this->link(['is_active' => false]);

        $this->get('/sanggar')->assertNotFound();
    }

    public function test_slug_tidak_dikenal_menghasilkan_404(): void
    {
        $this->get('/tautan-yang-tidak-ada')->assertNotFound();
    }

    public function test_halaman_asli_tidak_tertelan_rute_tautan_pendek(): void
    {
        // Slug yang lolos ke database (mis. dibuat sebelum aturan `not_in`
        // ada) tetap tidak boleh membajak halaman aslinya.
        $this->link(['slug' => 'berita', 'target' => 'https://contoh.test/bajakan']);

        $this->get('/berita')->assertOk();
        $this->get('/galeri')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_slug_dibakukan_saat_disimpan(): void
    {
        $this->actingAs($this->admin())->post('/admin/koleksi/shortlinks', [
            'slug' => '/Sanggar/',
            'target' => 'https://forms.gle/contoh',
            'note' => 'Pendaftaran sanggar',
        ])->assertRedirect();

        $this->assertDatabaseHas('shortlinks', ['slug' => 'sanggar']);

        $this->get('/sanggar')->assertRedirect('https://forms.gle/contoh');
    }

    public function test_slug_ganda_ditolak(): void
    {
        $this->link();

        $this->actingAs($this->admin())->post('/admin/koleksi/shortlinks', [
            'slug' => 'sanggar',
            'target' => 'https://forms.gle/lain',
        ])->assertSessionHasErrors('slug');
    }

    public function test_slug_boleh_dipakai_ulang_oleh_barisnya_sendiri(): void
    {
        $link = $this->link();

        $this->actingAs($this->admin())->put("/admin/koleksi/shortlinks/{$link->id}", [
            'slug' => 'sanggar',
            'target' => 'https://forms.gle/tujuan-baru',
        ])->assertSessionHasNoErrors();

        $this->get('/sanggar')->assertRedirect('https://forms.gle/tujuan-baru');
    }

    public function test_slug_milik_halaman_asli_ditolak(): void
    {
        $this->actingAs($this->admin())->post('/admin/koleksi/shortlinks', [
            'slug' => 'berita',
            'target' => 'https://forms.gle/contoh',
        ])->assertSessionHasErrors('slug');
    }

    public function test_seeder_memulihkan_tautan_dari_wordpress_lama(): void
    {
        $this->seed(\Database\Seeders\ShortlinkSeeder::class);

        $this->assertSame(48, Shortlink::count());

        $this->get('/sanggar')->assertRedirect(
            'https://docs.google.com/forms/d/e/1FAIpQLSfVhoFuTgloa8iJnZ2T2t89fz-ndDa2RhHNG_4QW-lJPn7bMw/viewform?usp=dialog',
        );
    }

    public function test_seeder_tidak_menimpa_tautan_yang_sudah_diubah_admin(): void
    {
        $this->link(['target' => 'https://forms.gle/tujuan-baru']);

        $this->seed(\Database\Seeders\ShortlinkSeeder::class);

        $this->assertSame('https://forms.gle/tujuan-baru', Shortlink::where('slug', 'sanggar')->value('target'));
    }
}
