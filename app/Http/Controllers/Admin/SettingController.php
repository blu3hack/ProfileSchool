<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Editor seluruh teks & gambar tunggal halaman publik
 * (judul hero, paragraf tiap section, label tombol, foto beranda).
 *
 * Daftar fieldnya berasal dari `config/site_content.php`.
 */
class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Content', [
            'groups' => PageContent::schema(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'values' => ['required', 'array'],
            'values.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $definitions = collect(config('site_content.fields', []))->keyBy('key');

        foreach ($validated['values'] as $key => $value) {
            // Abaikan key yang tidak terdaftar di config — mencegah
            // penyisipan setting liar lewat request yang dimodifikasi.
            $field = $definitions->get($key);

            if (! $field) {
                continue;
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $field['group'] ?? 'umum',
                    'type' => $field['type'] ?? 'text',
                    'label' => $field['label'],
                    'hint' => $field['hint'] ?? null,
                ],
            );
        }

        SiteSetting::flush();

        return back()->with('success', 'Konten halaman berhasil disimpan.');
    }
}
