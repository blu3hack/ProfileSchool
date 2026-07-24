<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD berita: daftar, tambah, ubah, hapus, dan sakelar terbit.
 *
 * Isi artikel disusun sebagai daftar blok (`body`) sehingga admin menulis
 * lewat form terstruktur, bukan HTML mentah.
 */
class NewsController extends Controller
{
    /** Pilihan siap pakai untuk dropdown di form. */
    public const CATEGORIES = ['Berita', 'Pengumuman', 'Kegiatan', 'Prestasi'];

    public const ACCENTS = ['mint', 'gold', 'sky', 'lilac'];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $news = News::query()
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")))
            ->latestFirst()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (News $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'category' => $item->category,
                'icon' => $item->icon,
                'is_published' => $item->is_published,
                'published_at' => optional($item->published_at)->format('Y-m-d'),
                'date' => $item->formattedDate(),
                'image' => MediaUrl::resolve($item->image),
            ]);

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/News/Form', [
            'news' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        News::create($data);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news): Response
    {
        return Inertia::render('Admin/News/Form', [
            'news' => [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'category' => $news->category,
                'icon' => $news->icon,
                'accent' => $news->accent,
                'excerpt' => $news->excerpt,
                'author' => $news->author,
                'read_time' => $news->read_time,
                'image' => $news->image,
                'image_url' => MediaUrl::resolve($news->image),
                'image_caption' => $news->image_caption,
                'tags' => $news->tags ?? [],
                'body' => $news->body ?? [],
                'gallery' => collect($news->gallery ?? [])->map(fn ($item) => [
                    'src' => is_array($item) ? ($item['src'] ?? '') : $item,
                    'caption' => is_array($item) ? ($item['caption'] ?? '') : '',
                    'url' => MediaUrl::resolve(is_array($item) ? ($item['src'] ?? null) : $item),
                ])->all(),
                'is_published' => $news->is_published,
                'published_at' => optional($news->published_at)->format('Y-m-d'),
            ],
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, News $news): RedirectResponse
    {
        $news->update($this->validated($request, $news));

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news): RedirectResponse
    {
        $news->delete();

        return back()->with('success', 'Berita berhasil dihapus.');
    }

    /** Terbitkan / sembunyikan tanpa membuka form. */
    public function togglePublish(News $news): RedirectResponse
    {
        $news->update(['is_published' => ! $news->is_published]);

        return back()->with('success', $news->is_published ? 'Berita diterbitkan.' : 'Berita disembunyikan.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?News $news = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/', 'unique:news,slug'.($news ? ",{$news->id}" : '')],
            'category' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:16'],
            'accent' => ['required', 'in:'.implode(',', self::ACCENTS)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:120'],
            'read_time' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'string', 'max:2048'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            // `nullable`: baris kosong dari form diubah middleware menjadi null
            // dan baru dibuang setelah validasi.
            'tags.*' => ['nullable', 'string', 'max:60'],
            'gallery' => ['nullable', 'array'],
            'gallery.*.src' => ['required', 'string', 'max:2048'],
            'gallery.*.caption' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'array'],
            'body.*.type' => ['required', 'in:paragraph,heading,list,quote'],
            'body.*.text' => ['nullable', 'string', 'max:5000'],
            'body.*.cite' => ['nullable', 'string', 'max:200'],
            'body.*.items' => ['nullable', 'array'],
            'body.*.items.*' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ], [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Slug ini sudah dipakai berita lain.',
        ]);

        // Field opsional yang tidak dikirim tidak muncul di hasil validasi,
        // jadi setiap nilai diambil dengan pengaman `?? null`.
        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['title'], $news);
        $data['icon'] = ($data['icon'] ?? null) ?: '📰';
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = ($data['published_at'] ?? null) ?: now();
        $data['tags'] = array_values(array_filter($data['tags'] ?? [], fn ($tag) => filled($tag)));
        $data['body'] = $this->cleanBody($data['body'] ?? []);
        $data['gallery'] = array_values($data['gallery'] ?? []);
        $data['read_time'] = ($data['read_time'] ?? null) ?: $this->estimateReadTime($data['body']);

        return $data;
    }

    /** Membuang blok kosong & merapikan item daftar. */
    protected function cleanBody(array $body): array
    {
        return collect($body)
            ->map(function (array $block) {
                if ($block['type'] === 'list') {
                    $block['items'] = array_values(array_filter($block['items'] ?? [], fn ($i) => filled($i)));
                }

                return $block;
            })
            ->filter(fn (array $block) => $block['type'] === 'list'
                ? count($block['items']) > 0
                : filled($block['text'] ?? null))
            ->values()
            ->all();
    }

    protected function uniqueSlug(string $title, ?News $news = null): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $suffix = 2;

        while (News::where('slug', $slug)->when($news, fn ($q) => $q->where('id', '!=', $news->id))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /** Estimasi waktu baca ±200 kata per menit. */
    protected function estimateReadTime(array $body): string
    {
        $words = collect($body)->sum(function (array $block) {
            $text = $block['type'] === 'list'
                ? implode(' ', $block['items'] ?? [])
                : (string) ($block['text'] ?? '');

            return str_word_count($text);
        });

        return max(1, (int) ceil($words / 200)).' menit baca';
    }

    protected function options(): array
    {
        return [
            'categories' => self::CATEGORIES,
            'accents' => [
                ['value' => 'mint', 'label' => 'Mint / Aqua'],
                ['value' => 'gold', 'label' => 'Emas'],
                ['value' => 'sky', 'label' => 'Ungu / Volt'],
                ['value' => 'lilac', 'label' => 'Magenta / Plasma'],
            ],
            'blockTypes' => [
                ['value' => 'paragraph', 'label' => 'Paragraf'],
                ['value' => 'heading', 'label' => 'Sub Judul'],
                ['value' => 'list', 'label' => 'Daftar Poin'],
                ['value' => 'quote', 'label' => 'Kutipan'],
            ],
        ];
    }
}
