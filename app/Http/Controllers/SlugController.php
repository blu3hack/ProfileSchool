<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use App\Support\PageContent;
use App\Support\PageMeta;
use App\Support\SiteInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Penangkap alamat satu ruas di akar situs, mis. /datasiswa atau /sanggar.
 *
 * Dua fitur berbagi ruang alamat ini, jadi keduanya harus diputuskan satu
 * controller — rute Laravel dipilih berdasarkan pola URL, bukan hasil kerja
 * controller-nya, sehingga dua rute `/{slug}` berurutan tidak akan pernah
 * saling melempar giliran. Urutannya:
 *
 *   1. Halaman kustom buatan admin (tabel `custom_pages`) — ditampilkan.
 *   2. Tautan pendek (tabel `shortlinks`) — dialihkan ke tujuannya.
 *   3. Keduanya tidak ada → 404.
 *
 * Halaman kustom didahulukan karena ia adalah halaman situs ini sendiri;
 * slug yang bertabrakan sudah ditolak saat disimpan di kedua sisi.
 */
class SlugController extends Controller
{
    public function __invoke(Request $request, string $slug, ShortlinkController $shortlinks): Response|RedirectResponse
    {
        $page = CustomPage::query()->where('slug', CustomPage::normalizeSlug($slug))->first();

        if ($page && $this->canView($request, $page)) {
            return $this->render($page, draft: ! $page->isVisible());
        }

        // Halaman draf pun jatuh ke sini bagi pengunjung biasa: dari luar,
        // alamatnya tidak boleh dibedakan dari alamat yang belum pernah ada.
        return $shortlinks($slug);
    }

    /**
     * Draf hanya terbuka untuk admin yang sedang login — itulah yang membuat
     * tombol "Lihat" di panel admin berguna sebelum halaman diterbitkan.
     * Pengunjung biasa mendapat 404, persis seperti slug yang tak dikenal.
     */
    protected function canView(Request $request, CustomPage $page): bool
    {
        return $page->isVisible() || (bool) $request->user()?->is_admin;
    }

    protected function render(CustomPage $page, bool $draft): Response
    {
        $payload = $page->toPayload();

        return Inertia::render('Halaman', [
            'schoolName' => SiteInfo::name(),
            'navLinks' => SiteInfo::navLinks(),
            'content' => PageContent::all(),
            'contacts' => SiteInfo::contacts(),
            'socials' => SiteInfo::socials(),
            'page' => $payload,
            // Menyalakan bilah peringatan "belum diterbitkan" di halaman.
            'draft' => $draft,
        ])->withViewData([
            'meta' => PageMeta::make([
                'title' => $payload['meta']['title'],
                'description' => $payload['meta']['description'],
                'image' => $payload['meta']['image'],
                'url' => $payload['url'],
                'type' => 'article',
                'modifiedTime' => $payload['updatedAt'],
                // Draf hanya terbuka bagi admin yang sedang login, tapi
                // alamatnya bisa saja ia tempel ke grup untuk minta pendapat.
                // Penanda ini menjaganya tetap di luar mesin pencari.
                'noindex' => $draft,
            ]),
        ]);
    }
}
