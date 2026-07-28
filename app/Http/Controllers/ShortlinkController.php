<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Meneruskan smpialazka.com/<slug> ke URL tujuannya.
 *
 * Terdaftar sebagai rute terakhir di routes/web.php, jadi hanya menangkap
 * alamat satu ruas yang tidak cocok dengan rute lain mana pun.
 */
class ShortlinkController extends Controller
{
    public function __invoke(string $slug): RedirectResponse
    {
        $link = Shortlink::query()
            ->active()
            ->where('slug', Shortlink::normalizeSlug($slug))
            ->firstOrFail();

        // Lewat query builder, bukan Eloquent: `updated_at` harus tetap
        // menandai kapan tautannya diubah admin, bukan kapan terakhir dibuka.
        Shortlink::query()->whereKey($link->id)->update([
            'hits' => DB::raw('hits + 1'),
            'last_visited_at' => now(),
        ]);

        // 302, bukan 301: tujuan tautan ini sering diganti tiap tahun ajaran
        // dan 301 akan tersimpan permanen di cache peramban pengunjung.
        return redirect()->away($link->target, 302);
    }
}
