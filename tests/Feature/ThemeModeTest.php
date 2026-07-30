<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ThemeMode;
use App\Support\ThemePalette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mode tampilan situs: disimpan dari panel admin, terbawa ke HTML halaman
 * publik, dan tidak ikut terpasang di panel admin (aturan `[data-theme='light']`
 * membalik skala slate yang dipakai teks panel).
 */
class ThemeModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ThemeMode::flush();
        ThemePalette::flush();
    }

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** Payload lengkap untuk PUT /admin/tema. */
    protected function payload(string $mode): array
    {
        return ['mode' => $mode, 'palette' => ThemePalette::defaults()];
    }

    public function test_bawaan_situs_adalah_mode_gelap(): void
    {
        $this->assertSame('dark', ThemeMode::current());

        $this->get('/')->assertOk()->assertSee('data-theme="dark"', false);
    }

    public function test_admin_bisa_menyetel_situs_ke_mode_terang(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/tema', $this->payload('light'))
            ->assertSessionHasNoErrors();

        $this->assertSame('light', ThemeMode::current());

        $this->get('/')->assertOk()->assertSee('data-theme="light"', false);
    }

    public function test_mengganti_mode_menaikkan_stamp_sehingga_pilihan_lama_pengunjung_kedaluwarsa(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/tema', $this->payload('light'));
        $first = ThemeMode::config()['stamp'];

        $this->assertGreaterThan(0, $first);

        // Menyimpan mode yang sama tidak boleh mengubah stamp — kalau tidak,
        // menekan "Simpan Tema" untuk mengubah warna saja akan ikut membuang
        // pilihan gelap/terang seluruh pengunjung.
        $this->actingAs($admin)->put('/admin/tema', $this->payload('light'));
        $this->assertSame($first, ThemeMode::config()['stamp']);

        $this->actingAs($admin)->put('/admin/tema', $this->payload('dark'));
        $this->assertGreaterThan($first, ThemeMode::config()['stamp']);
    }

    public function test_mode_tidak_dikenal_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/tema', $this->payload('neon'))
            ->assertSessionHasErrors('mode');

        $this->assertSame('dark', ThemeMode::current());
    }

    public function test_mode_system_dikirim_apa_adanya_ke_browser(): void
    {
        $this->actingAs($this->admin())->put('/admin/tema', $this->payload('system'));

        // Atribut server jatuh ke gelap (preferensi perangkat belum diketahui);
        // yang menanyakan preferensi itu skrip pra-render, jadi 'system' harus
        // utuh sampai ke sana. Payload-nya dibangun ulang lewat Js::from agar
        // pengujian tidak bergantung pada cara Laravel meng-escape kutip.
        $expected = \Illuminate\Support\Js::from(ThemeMode::boot('Welcome'))->toHtml();

        $this->assertStringContainsString('system', $expected);

        $this->get('/')
            ->assertSee('data-theme="dark"', false)
            ->assertSee($expected, false);
    }

    public function test_warna_bawaan_tidak_menimpa_ramp_hasil_setelan_tangan_di_app_css(): void
    {
        // Belum ada yang dikustomisasi → tidak ada satu pun variabel ditimpa.
        $this->assertSame('', ThemePalette::css());

        $palette = ThemePalette::defaults();
        $palette['light']['aqua'] = '#1d4ed8';

        $this->actingAs($this->admin())->put('/admin/tema', [
            'mode' => 'light',
            'palette' => $palette,
        ])->assertSessionHasNoErrors();

        ThemePalette::flush();
        $css = ThemePalette::css();

        // Hanya aksen yang diubah yang muncul; latar & aksen lain tetap
        // memakai nilai bawaan app.css.
        $this->assertStringContainsString("[data-theme='light'][data-theme='light']", $css);
        $this->assertStringContainsString('--color-aqua-400: #1d4ed8;', $css);
        $this->assertStringNotContainsString('--color-void-', $css);
        $this->assertStringNotContainsString('--color-solar-', $css);
        $this->assertStringNotContainsString("[data-theme='dark']", $css);
    }

    public function test_panel_admin_tidak_mendapat_atribut_tema(): void
    {
        $this->actingAs($this->admin())->put('/admin/tema', $this->payload('light'));

        // Kutip ganda = atribut pada <html>. Selektor di <style> memakai kutip
        // tunggal (`[data-theme='light']`), jadi tidak ikut tertangkap.
        $this->actingAs($this->admin())
            ->get('/admin/tema')
            ->assertOk()
            ->assertDontSee('data-theme="', false);
    }
}
