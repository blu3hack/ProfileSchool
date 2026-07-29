<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ThemeMode;
use App\Support\ThemePalette;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pengaturan tema landing page — admin memilih mode tampilan situs
 * (gelap/terang/ikut perangkat) serta warna aksen neon untuk tiap mode.
 * Nilainya disimpan lewat ThemeMode & ThemePalette dan langsung tampil
 * di situs setelah disimpan.
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
            'mode' => ThemeMode::current(),
            'modes' => ThemeMode::MODES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $hex = ['required', 'string', 'regex:/^#?[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/'];

        $rules = [
            'palette' => ['required', 'array'],
            'mode' => ['required', 'string', Rule::in(array_keys(ThemeMode::MODES))],
        ];

        foreach (['dark', 'light'] as $mode) {
            foreach (array_keys(ThemePalette::ACCENTS) as $accent) {
                $rules["palette.{$mode}.{$accent}"] = $hex;
            }

            $rules["palette.{$mode}.".ThemePalette::BACKGROUND] = $hex;
        }

        $validated = $request->validate($rules, [
            'palette.*.*.regex' => 'Warna harus berupa kode heksadesimal, mis. #34e2f5.',
            'mode.in' => 'Mode tampilan tidak dikenali.',
        ]);

        ThemePalette::save($validated['palette']);
        ThemeMode::save($validated['mode']);

        return back()->with('success', 'Tema berhasil disimpan.');
    }
}
