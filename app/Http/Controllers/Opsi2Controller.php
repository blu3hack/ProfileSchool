<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Activity;
use App\Models\Pillar;
use App\Models\Stat;
use App\Support\NewsRepository;
use App\Support\PageContent;
use App\Support\SiteInfo;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Landing page alternatif (Opsi 2) — konsep "Islamic Futuristic".
 *
 * Semua konten halaman dikumpulkan di sini dan kini bersumber dari
 * database (dikelola lewat panel admin di /admin), sehingga komponen Vue
 * tetap murni presentational.
 */
class Opsi2Controller extends Controller
{
    /** Komponen Inertia yang dirender — dioverride oleh varian tema lain. */
    protected string $page = 'Opsi2';

    public function __invoke(): Response
    {
        return Inertia::render($this->page, $this->payload());
    }

    /**
     * Seluruh konten halaman. Dipisah dari __invoke agar varian tema
     * (mis. Opsi 3) bisa menimpa/menambah data tanpa menyalin ulang.
     */
    protected function payload(): array
    {
        return [
            'schoolName' => SiteInfo::name(),
            'navLinks' => SiteInfo::navLinks(),
            'content' => PageContent::all(),
            'stats' => $this->stats(),
            'pillars' => $this->pillars(),
            'news' => $this->news(),
            'activities' => $this->activities(),
            'achievements' => $this->achievements(),
            'contacts' => $this->contacts(),
            'socials' => $this->socials(),
        ];
    }

    protected function stats(): array
    {
        return Stat::active()->ordered()->get()
            ->map(fn (Stat $stat) => [
                'value' => $stat->value,
                'label' => $stat->label,
                'hint' => $stat->hint,
            ])
            ->all();
    }

    protected function pillars(): array
    {
        return Pillar::active()->ordered()->get()
            ->map(fn (Pillar $pillar) => $pillar->toCard())
            ->all();
    }

    /**
     * Enam berita terbaru untuk slider di landing page.
     * Isi lengkapnya tinggal di NewsRepository — di sini hanya ringkasannya.
     */
    protected function news(): array
    {
        return NewsRepository::cards(NewsRepository::latest(6));
    }

    protected function activities(): array
    {
        return Activity::active()->ordered()->get()
            ->map(fn (Activity $activity) => $activity->toCard())
            ->all();
    }

    protected function achievements(): array
    {
        return Achievement::active()->ordered()->get()
            ->map(fn (Achievement $achievement) => $achievement->toCard())
            ->all();
    }

    protected function contacts(): array
    {
        return SiteInfo::contacts();
    }

    protected function socials(): array
    {
        return SiteInfo::socials();
    }
}
