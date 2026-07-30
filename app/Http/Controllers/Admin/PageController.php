<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;
use App\Models\Shortlink;
use App\Support\Embed;
use App\Support\HtmlSanitizer;
use App\Support\MediaUrl;
use App\Support\PageBlocks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD halaman kustom: halaman baru dengan alamatnya sendiri di akar situs
 * (mis. /datasiswa), diisi lewat penyusun blok atau kode HTML.
 *
 * Semua HTML yang masuk — kolom `html` maupun blok teks kaya di dalam
 * `blocks` — disaring App\Support\HtmlSanitizer SEBELUM tersimpan. Jadi yang
 * tersimpan di database sudah bentuk final yang akan dirender, dan admin
 * melihat hasil saringan itu apa adanya saat form dibuka kembali.
 */
class PageController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $pages = CustomPage::query()
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->latestFirst()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (CustomPage $page) => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'mode' => $page->mode,
                'modeLabel' => CustomPage::MODES[$page->mode] ?? $page->mode,
                'is_published' => $page->is_published,
                'is_visible' => $page->isVisible(),
                'date' => $page->formattedDate(),
                'url' => url('/'.$page->slug),
                'blockCount' => $page->mode === CustomPage::MODE_BUILDER ? count($page->blocks ?? []) : null,
            ]);

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Form', [
            'page' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = CustomPage::create($this->validated($request));

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Halaman berhasil dibuat. Alamatnya: /'.$page->slug);
    }

    public function edit(CustomPage $page): Response
    {
        return Inertia::render('Admin/Pages/Form', [
            'page' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->title,
                'mode' => $page->mode,
                'eyebrow' => $page->eyebrow,
                'summary' => $page->summary,
                'hero_image' => $page->hero_image,
                'hero_image_url' => MediaUrl::resolve($page->hero_image),
                'blocks' => $this->blocksForEditor($page),
                'html' => $page->html ?? '',
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'og_image' => $page->og_image,
                'og_image_url' => MediaUrl::resolve($page->og_image),
                'is_published' => $page->is_published,
                'published_at' => optional($page->published_at)->format('Y-m-d'),
                'url' => url('/'.$page->slug),
            ],
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, CustomPage $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return back()->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(CustomPage $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }

    /** Terbitkan / sembunyikan tanpa membuka form. */
    public function togglePublish(CustomPage $page): RedirectResponse
    {
        $page->update([
            'is_published' => ! $page->is_published,
            // Halaman yang baru pertama kali diterbitkan belum punya tanggal.
            'published_at' => $page->published_at ?? now(),
        ]);

        return back()->with('success', $page->is_published
            ? 'Halaman diterbitkan di /'.$page->slug
            : 'Halaman disembunyikan (kembali menjadi draf).');
    }

    /**
     * Gambar di dalam blok dikirim ke editor lengkap dengan URL pratinjaunya,
     * karena yang tersimpan hanyalah path relatif hasil unggahan.
     */
    protected function blocksForEditor(CustomPage $page): array
    {
        return collect($page->blocks ?? [])
            ->map(fn (array $block) => match ($block['type'] ?? '') {
                'image' => [...$block, 'src_url' => MediaUrl::resolve($block['src'] ?? null)],
                'gallery' => [
                    ...$block,
                    'items' => collect($block['items'] ?? [])
                        ->map(fn (array $item) => [...$item, 'src_url' => MediaUrl::resolve($item['src'] ?? null)])
                        ->all(),
                ],
                'cards' => [
                    ...$block,
                    'items' => collect($block['items'] ?? [])
                        ->map(fn (array $item) => [...$item, 'image_url' => MediaUrl::resolve($item['image'] ?? null)])
                        ->all(),
                ],
                default => $block,
            })
            ->all();
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?CustomPage $page = null): array
    {
        // Slug dibakukan lebih dulu supaya yang diperiksa keunikannya sama
        // persis dengan yang nanti tersimpan (lihat CustomPage::normalizeSlug).
        $request->merge(['slug' => CustomPage::normalizeSlug($request->input('slug'))]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable', 'string', 'max:100',
                'regex:/^[a-z0-9][a-z0-9-]*$/',
                Rule::unique('custom_pages', 'slug')->ignore($page),
                // Alamat halaman selalu menang atas tautan pendek, jadi slug
                // yang sudah dipakai tautan pendek akan mematikannya diam-diam.
                Rule::unique('shortlinks', 'slug'),
                Rule::notIn(CustomPage::reservedSlugs()),
            ],
            'mode' => ['required', Rule::in(array_keys(CustomPage::MODES))],
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'summary' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'string', 'max:2048'],
            // 400 KB: berkas HTML utuh dengan CSS di dalamnya masih lolos,
            // sementara tempelan tak wajar tetap tertahan.
            'html' => ['nullable', 'string', 'max:400000'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            ...PageBlocks::rules(),
        ], [
            'slug.regex' => 'Alamat hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Alamat ini sudah dipakai halaman atau tautan pendek lain.',
            'slug.not_in' => 'Alamat ini milik halaman asli situs, pilih yang lain.',
        ]);

        $data['slug'] = ($data['slug'] ?? '') ?: $this->uniqueSlug($data['title'], $page);
        $data['blocks'] = PageBlocks::clean($data['blocks'] ?? []);
        $data['html'] = HtmlSanitizer::clean($data['html'] ?? null);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = ($data['published_at'] ?? null) ?: now();

        return $data;
    }

    protected function uniqueSlug(string $title, ?CustomPage $page = null): string
    {
        $base = Str::slug($title) ?: 'halaman';
        $slug = $base;
        $suffix = 2;

        // Slug otomatis harus lolos pantangan yang sama seperti slug manual:
        // tidak menabrak halaman lain, tautan pendek, maupun rute asli situs.
        $taken = fn (string $candidate) => in_array($candidate, CustomPage::reservedSlugs(), true)
            || CustomPage::where('slug', $candidate)->when($page, fn ($q) => $q->whereKeyNot($page->id))->exists()
            || Shortlink::where('slug', $candidate)->exists();

        while ($taken($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /** Pilihan siap pakai untuk form: mode, jenis blok, dan contoh alamat. */
    protected function options(): array
    {
        return [
            'modes' => collect(CustomPage::MODES)
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'blockTypes' => collect(PageBlocks::TYPES)
                ->map(fn (array $meta, string $value) => ['value' => $value, ...$meta])
                ->values()
                ->all(),
            'levels' => PageBlocks::LEVELS,
            'widths' => [
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'wide', 'label' => 'Lebar'],
                ['value' => 'full', 'label' => 'Selebar layar'],
            ],
            'columns' => PageBlocks::COLUMNS,
            'ctaStyles' => [
                ['value' => 'primary', 'label' => 'Tombol utama (gradasi)'],
                ['value' => 'ghost', 'label' => 'Tombol garis'],
            ],
            'reserved' => CustomPage::reservedSlugs(),
            'baseUrl' => rtrim(url('/'), '/').'/',
            'allowedTags' => HtmlSanitizer::TAGS,
            'embedHosts' => Embed::HOSTS,
        ];
    }
}
