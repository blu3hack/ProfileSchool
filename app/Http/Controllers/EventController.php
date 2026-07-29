<?php

namespace App\Http\Controllers;

use App\Support\EventRepository;
use App\Support\PageContent;
use App\Support\SiteInfo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman agenda: daftar seluruh "Next Event" dan halaman detail per acara.
 *
 * Sama seperti halaman berita, navbar & footer diambil dari `SiteInfo`
 * supaya seluruh halaman publik memakai satu sumber data yang sama.
 */
class EventController extends Controller
{
    /** Semua agenda — tujuan tombol "Lihat Semua Next Event" di landing. */
    public function index(): Response
    {
        $upcoming = EventRepository::allUpcoming();

        return Inertia::render('Event/Index', [
            ...$this->layoutData(),
            // Acara terdekat tampil besar di puncak halaman.
            'featured' => $upcoming[0] ?? null,
            'upcoming' => array_slice($upcoming, 1),
            'past' => EventRepository::past(6),
            'categories' => EventRepository::categories(),
        ]);
    }

    /** Detail satu acara — tujuan saat kartu agenda diklik. */
    public function show(string $slug): Response|RedirectResponse
    {
        $event = EventRepository::find($slug);

        // Slug tak dikenal (mis. acara yang sudah dihapus) diarahkan ke
        // indeks agenda supaya pengunjung tidak berhenti di halaman kosong.
        if (! $event) {
            return redirect()->route('events.index');
        }

        return Inertia::render('Event/Show', [
            ...$this->layoutData(),
            'event' => $event,
            'related' => EventRepository::related($slug),
        ]);
    }

    /** Data yang selalu dibutuhkan navbar & footer. */
    protected function layoutData(): array
    {
        return [
            'schoolName' => SiteInfo::name(),
            'navLinks' => SiteInfo::navLinks(),
            'extraLinks' => SiteInfo::extraLinks(),
            'content' => PageContent::all(),
            'contacts' => SiteInfo::contacts(),
            'socials' => SiteInfo::socials(),
        ];
    }
}
