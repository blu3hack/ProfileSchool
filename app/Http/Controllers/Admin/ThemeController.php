<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ThemePalette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pengaturan warna tema landing page — admin memilih warna aksen neon
 * untuk mode gelap & terang. Nilainya disimpan lewat ThemePalette dan
 * langsung tampil di situs setelah disimpan.
 */
class ThemeController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Theme', [
            'palette' => ThemePalette::current(),
            'defaults' => ThemePalette::defaults(),
            // [mode => ..., accent-key => label] untuk dirender panel.
            'accents' => ThemePalette::ACCENTS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $hex = ['required', 'string', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'];

        $rules = ['palette' => ['required', 'array']];

        foreach (['dark', 'light'] as $mode) {
            foreach (array_keys(ThemePalette::ACCENTS) as $accent) {
                $rules["palette.{$mode}.{$accent}"] = $hex;
            }

            $rules["palette.{$mode}.".ThemePalette::BACKGROUND] = $hex;
        }

        $validated = $request->validate($rules, [
            'palette.*.*.regex' => 'Warna harus berupa kode heksadesimal, mis. #34e2f5.',
        ]);

        ThemePalette::save($validated['palette']);

        return back()->with('success', 'Warna tema berhasil disimpan.');
    }
}
