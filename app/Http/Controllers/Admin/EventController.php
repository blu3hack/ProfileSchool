<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\MediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD agenda "Next Event": daftar, tambah, ubah, hapus, sakelar terbit.
 *
 * Struktur formnya mengikuti editor berita (blok isi + galeri), ditambah
 * jadwal pelaksanaan dan susunan acara (rundown).
 */
class EventController extends Controller
{
    /** Pilihan siap pakai untuk dropdown di form. */
    public const CATEGORIES = ['Kegiatan', 'Akademik', 'Lomba', 'Keagamaan', 'PPDB', 'Pengumuman'];

    public const ACCENTS = ['mint', 'gold', 'sky', 'lilac'];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $events = Event::query()
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")))
            ->soonest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Event $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'category' => $item->category,
                'icon' => $item->icon,
                'location' => $item->location,
                'is_published' => $item->is_published,
                'is_upcoming' => $item->isUpcoming(),
                'date' => $item->formattedDate(),
                'time' => $item->formattedTime(),
                'image' => MediaUrl::resolve($item->image),
            ]);

        return Inertia::render('Admin/Event/Index', [
            'events' => $events,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Event/Form', [
            'event' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Event::create($this->validated($request));

        return redirect()->route('admin.events.index')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Admin/Event/Form', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'category' => $event->category,
                'icon' => $event->icon,
                'accent' => $event->accent,
                'excerpt' => $event->excerpt,
                'location' => $event->location,
                'organizer' => $event->organizer,
                'audience' => $event->audience,
                'registration_url' => $event->registration_url,
                'registration_label' => $event->registration_label,
                'image' => $event->image,
                'image_url' => MediaUrl::resolve($event->image),
                'image_caption' => $event->image_caption,
                'tags' => $event->tags ?? [],
                'body' => $event->body ?? [],
                'rundown' => $event->rundown ?? [],
                'gallery' => collect($event->gallery ?? [])->map(fn ($item) => [
                    'src' => is_array($item) ? ($item['src'] ?? '') : $item,
                    'caption' => is_array($item) ? ($item['caption'] ?? '') : '',
                    'url' => MediaUrl::resolve(is_array($item) ? ($item['src'] ?? null) : $item),
                ])->all(),
                'is_published' => $event->is_published,
                // <input type="datetime-local"> memakai format "Y-m-d\TH:i".
                'starts_at' => optional($event->starts_at)->format('Y-m-d\TH:i'),
                'ends_at' => optional($event->ends_at)->format('Y-m-d\TH:i'),
            ],
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validated($request, $event));

        return redirect()->route('admin.events.index')->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return back()->with('success', 'Agenda berhasil dihapus.');
    }

    /** Terbitkan / sembunyikan tanpa membuka form. */
    public function togglePublish(Event $event): RedirectResponse
    {
        $event->update(['is_published' => ! $event->is_published]);

        return back()->with('success', $event->is_published ? 'Agenda diterbitkan.' : 'Agenda disembunyikan.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request, ?Event $event = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/', 'unique:events,slug'.($event ? ",{$event->id}" : '')],
            'category' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:16'],
            'accent' => ['required', 'in:'.implode(',', self::ACCENTS)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:200'],
            'organizer' => ['nullable', 'string', 'max:120'],
            'audience' => ['nullable', 'string', 'max:120'],
            'registration_url' => ['nullable', 'string', 'max:2048'],
            'registration_label' => ['nullable', 'string', 'max:60'],
            'image' => ['nullable', 'string', 'max:2048'],
            'image_caption' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            // `nullable`: baris kosong dari form diubah middleware menjadi null
            // dan baru dibuang setelah validasi.
            'tags.*' => ['nullable', 'string', 'max:60'],
            'gallery' => ['nullable', 'array'],
            'gallery.*.src' => ['required', 'string', 'max:2048'],
            'gallery.*.caption' => ['nullable', 'string', 'max:255'],
            'rundown' => ['nullable', 'array'],
            'rundown.*.time' => ['nullable', 'string', 'max:40'],
            'rundown.*.title' => ['nullable', 'string', 'max:200'],
            'rundown.*.description' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'array'],
            'body.*.type' => ['required', 'in:paragraph,heading,list,quote'],
            'body.*.text' => ['nullable', 'string', 'max:5000'],
            'body.*.cite' => ['nullable', 'string', 'max:200'],
            'body.*.items' => ['nullable', 'array'],
            'body.*.items.*' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Slug ini sudah dipakai agenda lain.',
            'starts_at.required' => 'Waktu mulai acara wajib diisi — dipakai untuk hitung mundur.',
            'ends_at.after_or_equal' => 'Waktu selesai tidak boleh mendahului waktu mulai.',
        ]);

        // Field opsional yang tidak dikirim tidak muncul di hasil validasi,
        // jadi setiap nilai diambil dengan pengaman `?? null`.
        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['title'], $event);
        $data['icon'] = ($data['icon'] ?? null) ?: '📅';
        $data['is_published'] = $request->boolean('is_published');
        $data['tags'] = array_values(array_filter($data['tags'] ?? [], fn ($tag) => filled($tag)));
        $data['body'] = $this->cleanBody($data['body'] ?? []);
        $data['rundown'] = $this->cleanRundown($data['rundown'] ?? []);
        $data['gallery'] = array_values($data['gallery'] ?? []);
        $data['ends_at'] = ($data['ends_at'] ?? null) ?: null;

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

    /** Baris rundown tanpa judul dianggap belum diisi. */
    protected function cleanRundown(array $rundown): array
    {
        return collect($rundown)
            ->filter(fn (array $row) => filled($row['title'] ?? null))
            ->map(fn (array $row) => [
                'time' => $row['time'] ?? '',
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
            ])
            ->values()
            ->all();
    }

    protected function uniqueSlug(string $title, ?Event $event = null): string
    {
        $base = Str::slug($title) ?: 'agenda';
        $slug = $base;
        $suffix = 2;

        while (Event::where('slug', $slug)->when($event, fn ($q) => $q->where('id', '!=', $event->id))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
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
