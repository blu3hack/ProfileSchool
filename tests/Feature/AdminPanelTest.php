<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Media;
use App\Models\News;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Alur utama panel admin: login, edit konten, CRUD, dan unggah gambar. */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'password' => bcrypt('rahasia123')]);
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_user_biasa_ditolak(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_bisa_login_dan_membuka_dasbor(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);

        $this->get('/admin')->assertOk();
    }

    public function test_login_dengan_kata_sandi_salah_ditolak(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_bisa_mengubah_teks_halaman(): void
    {
        $this->seed(\Database\Seeders\SiteContentSeeder::class);

        $this->actingAs($this->admin())
            ->put('/admin/konten', ['values' => ['hero_title_1' => 'Generasi Unggul']])
            ->assertRedirect();

        $this->assertSame('Generasi Unggul', SiteSetting::where('key', 'hero_title_1')->value('value'));

        // Halaman publik ikut berubah.
        $this->get('/')->assertOk()->assertSee('Generasi Unggul', false);
    }

    public function test_admin_bisa_menambah_dan_menghapus_prestasi(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/koleksi/achievements', [
            'icon' => '🥇',
            'title' => 'Juara 1 Olimpiade Matematika',
            'year' => '2026',
            'level' => 'Nasional',
            'description' => 'Peringkat pertama tingkat nasional.',
            'student' => 'Aisyah',
            'grade' => 'SMP Kelas 8',
            'image' => '',
        ])->assertRedirect();

        $achievement = Achievement::where('title', 'Juara 1 Olimpiade Matematika')->firstOrFail();

        $this->actingAs($admin)->delete("/admin/koleksi/achievements/{$achievement->id}")->assertRedirect();

        $this->assertDatabaseMissing('achievements', ['id' => $achievement->id]);
    }

    public function test_admin_bisa_membuat_berita_dengan_blok_isi(): void
    {
        $this->actingAs($this->admin())->post('/admin/berita', [
            'title' => 'Wisuda Tahfidz Angkatan ke-15',
            'category' => 'Berita',
            'accent' => 'gold',
            'excerpt' => 'Sebanyak 100 siswa diwisuda.',
            'tags' => ['Tahfidz', 'Wisuda'],
            'body' => [
                ['type' => 'paragraph', 'text' => 'Prosesi berlangsung khidmat di aula utama.'],
                ['type' => 'list', 'items' => ['Sima\'an terbuka', 'Penyerahan sertifikat', '']],
                ['type' => 'paragraph', 'text' => ''],
            ],
            'is_published' => true,
        ])->assertRedirect('/admin/berita');

        $news = News::where('title', 'Wisuda Tahfidz Angkatan ke-15')->firstOrFail();

        // Slug dibuat otomatis, blok kosong dibuang.
        $this->assertSame('wisuda-tahfidz-angkatan-ke-15', $news->slug);
        $this->assertCount(2, $news->body);
        $this->assertCount(2, $news->body[1]['items']);
        $this->assertNotEmpty($news->read_time);

        // Berita langsung tampil di halaman publik.
        $this->get('/berita/'.$news->slug)->assertOk()->assertSee('Wisuda Tahfidz Angkatan ke-15', false);
    }

    public function test_berita_draf_tidak_tampil_di_halaman_publik(): void
    {
        $news = News::create([
            'slug' => 'draf-berita',
            'title' => 'Draf Berita',
            'category' => 'Berita',
            'accent' => 'mint',
            'is_published' => false,
            'published_at' => now(),
        ]);

        $this->get('/berita/'.$news->slug)->assertRedirect('/berita');
    }

    public function test_admin_bisa_mengunggah_gambar(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())
            ->post('/admin/media', ['file' => UploadedFile::fake()->image('banner-sekolah.jpg', 800, 600)]);

        $response->assertCreated()->assertJsonStructure(['id', 'path', 'url', 'name']);

        $media = Media::firstOrFail();

        Storage::disk('public')->assertExists($media->path);
    }

    public function test_unggahan_selain_gambar_ditolak(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->postJson('/admin/media', ['file' => UploadedFile::fake()->create('dokumen.pdf', 100)])
            ->assertStatus(422);
    }
}
