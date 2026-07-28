<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\HeroSlide;
use App\Support\EventRepository;
use App\Support\PageContent;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Landing page utama (Opsi 3) — konsep "Neo Cyber Madrasah".
 *
 * Konten identik dengan Opsi 2 (diwarisi dari induk); yang berbeda hanya
 * lapisan presentasi: tema gelap futuristik, neon, dan efek 3D.
 * Ditambah aset visual khusus hero (foto gedung sekolah).
 */
class Opsi3Controller extends Opsi2Controller
{
    protected string $page = 'Opsi3';

    /** Cache per-request: heroSlides() dipakai payload sekaligus preload. */
    protected ?array $slides = null;

    /**
     * Foto hero adalah elemen LCP halaman ini. Tanpa petunjuk di <head>,
     * browser baru menemukannya setelah bundel JS diunduh dan Vue selesai
     * merender — jadi alamatnya dititipkan ke root view untuk di-preload.
     */
    public function __invoke(): Response
    {
        return Inertia::render($this->page, $this->payload())
            ->withViewData(['heroPreload' => $this->heroSlides()[0]['src'] ?? null]);
    }

    protected function payload(): array
    {
        return array_merge(parent::payload(), [
            'heroImage' => $this->heroImage(),
            'heroSlides' => $this->heroSlides(),
            'events' => $this->events(),
            'gallery' => $this->gallery(),
        ]);
    }

    /**
     * Foto galeri profil sekolah untuk carousel di beranda (maks. 10).
     * Koleksi lengkapnya tampil di halaman /galeri.
     */
    protected function gallery(): array
    {
        return GalleryImage::active()->ordered()->limit(10)->get()
            ->map(fn (GalleryImage $image) => $image->toCard())
            ->filter(fn (array $image) => filled($image['src']))
            ->values()
            ->all();
    }

    /**
     * Empat agenda terdekat untuk section "Next Event". Isi lengkapnya
     * tinggal di EventRepository — di sini hanya ringkasan kartunya.
     */
    protected function events(): array
    {
        return EventRepository::upcoming(4);
    }

    /**
     * Foto latar hero. Diunggah/diganti admin lewat menu
     * "Konten Halaman → Beranda (Hero)".
     */
    protected function heroImage(): array
    {
        $content = PageContent::all();

        return [
            'src' => $content['hero_image'] ?? null,
            'alt' => $content['hero_image_alt'] ?? '',
            'credit' => $content['hero_image_credit'] ?? null,
        ];
    }

    /**
     * Slide hero yang bergantian otomatis (foto + profil singkat), dikelola
     * admin lewat menu "Slide Beranda".
     *
     * Bila belum ada slide sama sekali, foto tunggal lama dipakai sebagai satu
     * slide supaya hero tidak pernah tampil kosong.
     */
    protected function heroSlides(): array
    {
        if ($this->slides !== null) {
            return $this->slides;
        }

        $slides = HeroSlide::active()->ordered()->get()
            ->map(fn (HeroSlide $slide) => $slide->toCard())
            ->filter(fn (array $slide) => filled($slide['src']))
            ->values()
            ->all();

        if (! $slides) {
            $fallback = $this->heroImage();

            $slides = $fallback['src']
                ? [$fallback + ['eyebrow' => null, 'title' => null, 'description' => null]]
                : [];
        }

        return $this->slides = $slides;
    }
}
