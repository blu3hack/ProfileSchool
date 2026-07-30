<?php

namespace App\Http\Controllers;

use App\Support\NewsRepository;
use App\Support\PageContent;
use App\Support\PageMeta;
use App\Support\SiteInfo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman berita: indeks seluruh berita dan halaman detail per artikel.
 *
 * Navbar & footer dipakai ulang dari landing utama, karena itu data
 * pendukungnya diambil dari `SiteInfo` — satu sumber untuk semua halaman.
 */
class NewsController extends Controller
{
    /** Semua berita — tujuan tombol "Lihat Semua Berita" di landing. */
    public function index(): Response
    {
        $articles = NewsRepository::all();

        return Inertia::render('Berita/Index', [
            ...$this->layoutData(),
            // Berita bisa kosong bila admin belum menerbitkan satu pun.
            'featured' => isset($articles[0]) ? NewsRepository::toCard($articles[0]) : null,
            'articles' => NewsRepository::cards(array_slice($articles, 1)),
            'categories' => NewsRepository::categories(),
        ])->withViewData([
            'meta' => PageMeta::make([
                'title' => 'Berita & Pengumuman',
                'description' => PageContent::get('news_description'),
                // Foto berita terbaru mewakili halaman indeks.
                'image' => $articles[0]['image'] ?? null,
                'url' => route('news.index'),
            ]),
        ]);
    }

    /** Detail satu berita — tujuan saat kartu berita diklik. */
    public function show(string $slug): Response|RedirectResponse
    {
        $article = NewsRepository::find($slug);

        // Slug tak dikenal (mis. tautan lama) diarahkan ke indeks berita
        // supaya pengunjung tidak berhenti di halaman kosong.
        if (! $article) {
            return redirect()->route('news.index');
        }

        return Inertia::render('Berita/Show', [
            ...$this->layoutData(),
            'article' => $article,
            'related' => NewsRepository::cards(NewsRepository::related($slug)),
        ])->withViewData([
            // Inilah halaman yang paling sering dibagikan ke WhatsApp, jadi
            // kartu pratinjaunya diisi dari artikelnya sendiri: judul, foto
            // utama, dan ringkasan yang sama seperti yang tampil di kartu berita.
            'meta' => PageMeta::make([
                'title' => $article['title'],
                'description' => $article['excerpt'],
                'image' => $article['image'],
                'imageAlt' => $article['imageCaption'] ?: $article['title'],
                'url' => route('news.show', $article['slug']),
                'type' => 'article',
                'publishedTime' => $article['publishedAt'],
            ]),
        ]);
    }

    /** Data yang selalu dibutuhkan navbar & footer. */
    protected function layoutData(): array
    {
        return [
            'schoolName' => SiteInfo::name(),
            'navLinks' => SiteInfo::navLinks(),
            'content' => PageContent::all(),
            'contacts' => SiteInfo::contacts(),
            'socials' => SiteInfo::socials(),
        ];
    }
}
