<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pustaka media: unggah, telusuri, dan hapus gambar.
 *
 * Berkas disimpan di disk `public` (storage/app/public/uploads/TAHUN/BULAN)
 * dan diakses lewat symlink `public/storage`. Jalankan sekali:
 *   php artisan storage:link
 */
class MediaController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Media', [
            'media' => Media::query()
                ->latest()
                ->paginate(24)
                ->withQueryString()
                ->through(fn (Media $item) => $this->present($item)),
        ]);
    }

    /**
     * Unggah gambar. Dipanggil lewat XHR dari komponen pemilih gambar,
     * jadi jawabannya JSON berisi path + URL berkas yang baru dibuat.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
            'alt' => ['nullable', 'string', 'max:255'],
        ], [
            'file.image' => 'Berkas harus berupa gambar.',
            'file.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $file = $request->file('file');

        $name = Str::limit(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)), 60, '');
        $base = 'uploads/'.now()->format('Y/m').'/'.($name ?: 'gambar').'-'.Str::random(6);

        [$path, $mime, $size] = $this->keep($file, $base);

        $media = Media::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $mime,
            'size' => $size,
            'alt' => $request->string('alt')->toString() ?: null,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($this->present($media), 201);
    }

    /**
     * Simpan berkas, dikecilkan ke WebP bila memungkinkan.
     *
     * Format yang tak bisa diproses GD (mis. GIF beranimasi) tetap disimpan
     * apa adanya — lebih baik gambar besar daripada gambar rusak.
     *
     * @return array{0: string, 1: string, 2: int} path, mime, dan ukuran akhir
     */
    private function keep(UploadedFile $file, string $base): array
    {
        $temporary = tempnam(sys_get_temp_dir(), 'img');

        try {
            if ($temporary && ImageOptimizer::toWebp($file->getRealPath(), $temporary)) {
                $path = $base.'.webp';
                Storage::disk('public')->put($path, file_get_contents($temporary));

                return [$path, 'image/webp', (int) filesize($temporary)];
            }
        } finally {
            if ($temporary) {
                @unlink($temporary);
            }
        }

        $path = $file->storeAs(
            dirname($base),
            basename($base).'.'.$file->getClientOriginalExtension(),
            'public',
        );

        return [$path, $file->getClientMimeType(), (int) $file->getSize()];
    }

    /** Daftar media untuk pemilih gambar (dipanggil lewat XHR). */
    public function list(): JsonResponse
    {
        return response()->json(
            Media::query()->latest()->limit(60)->get()->map(fn (Media $item) => $this->present($item))
        );
    }

    public function destroy(Media $medium): RedirectResponse
    {
        Storage::disk('public')->delete($medium->path);

        $medium->delete();

        return back()->with('success', 'Gambar berhasil dihapus dari pustaka.');
    }

    protected function present(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'path' => $media->path,
            'url' => $media->url,
            'alt' => $media->alt,
            'size' => $media->size,
            'size_label' => $this->humanSize($media->size),
            'created_at' => $media->created_at?->locale('id')->translatedFormat('j M Y'),
        ];
    }

    protected function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }
}
