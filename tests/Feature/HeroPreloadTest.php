<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Foto hero adalah elemen LCP beranda. Petunjuk preload di <head> membuat
 * unduhannya mulai saat HTML dibaca, bukan menunggu Vue selesai merender
 * <img>-nya — mudah hilang tanpa sadar, jadi dijaga tes.
 */
class HeroPreloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_mem_preload_slide_hero_pertama(): void
    {
        HeroSlide::create([
            'image' => 'uploads/hero-utama.jpg',
            'title' => 'Gedung sekolah',
            'sort_order' => 1,
        ]);

        HeroSlide::create([
            'image' => 'uploads/hero-kedua.jpg',
            'title' => 'Ruang kelas',
            'sort_order' => 2,
        ]);

        $response = $this->get('/')->assertOk();

        // Hanya slide pertama yang langsung terlihat, jadi hanya itu yang
        // pantas direbutkan bandwidth-nya di awal.
        $response->assertSee('rel="preload" as="image"', false)
            ->assertSee('hero-utama.jpg', false);

        $this->assertStringNotContainsString(
            'hero-kedua.jpg"',
            str($response->getContent())->before('</head>')->toString(),
        );
    }

    public function test_preload_mendahului_bundel_javascript(): void
    {
        HeroSlide::create(['image' => 'uploads/hero-utama.jpg', 'title' => 'Gedung sekolah']);

        $head = str($this->get('/')->assertOk()->getContent())->before('</head>')->toString();

        // Urutan penting: browser memproses <head> dari atas ke bawah, jadi
        // preload yang tertulis setelah <script> kehilangan sebagian gunanya.
        $this->assertLessThan(
            strpos($head, 'modulepreload'),
            strpos($head, 'as="image"'),
        );
    }
}
