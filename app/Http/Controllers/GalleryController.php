<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Support\PageContent;
use App\Support\PageMeta;
use App\Support\SiteInfo;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman galeri lengkap — tujuan tombol "Lihat Semua" pada carousel galeri
 * di landing page.
 *
 * Navbar & footer dipakai ulang dari landing utama, karena itu data
 * pendukungnya diambil dari `SiteInfo` — satu sumber untuk semua halaman.
 */
class GalleryController extends Controller
{
    /** Seluruh foto galeri yang aktif, terurut sesuai pengaturan admin. */
    public function index(): Response
    {
        $images = GalleryImage::active()->ordered()->get()
            ->map(fn (GalleryImage $image) => $image->toCard())
            ->filter(fn (array $image) => filled($image['src']))
            ->values()
            ->all();

        return Inertia::render('Galeri/Index', [
            'schoolName' => SiteInfo::name(),
            'navLinks' => SiteInfo::navLinks(),
            'content' => PageContent::all(),
            'contacts' => SiteInfo::contacts(),
            'socials' => SiteInfo::socials(),
            'images' => $images,
        ])->withViewData([
            'meta' => PageMeta::make([
                'title' => 'Galeri',
                'description' => PageContent::get('gallery_description'),
                'image' => $images[0]['src'] ?? null,
                'url' => route('gallery.index'),
            ]),
        ]);
    }
}
