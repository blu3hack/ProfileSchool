<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Satu controller untuk semua koleksi konten sederhana (statistik, pilar,
 * kegiatan, prestasi, kontak, sosial, menu).
 *
 * Bentuk form, kolom tabel, dan aturan validasinya dibaca dari
 * `config/admin_resources.php`, jadi menambah jenis konten baru cukup
 * dengan menambah entri di config tersebut.
 */
class ResourceController extends Controller
{
    public function index(string $resource): Response
    {
        $config = $this->config($resource);
        $model = $config['model'];

        return Inertia::render('Admin/Resource', [
            'resource' => $resource,
            'meta' => [
                'label' => $config['label'],
                'singular' => $config['singular'],
                'icon' => $config['icon'],
                'description' => $config['description'],
                'columns' => $config['columns'],
            ],
            'fields' => $this->fields($config),
            'items' => $model::query()->ordered()->get()->map(fn (Model $item) => $this->present($item, $config))->all(),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $config = $this->config($resource);
        $model = $config['model'];

        $data = $this->validated($request, $config);
        $data['sort_order'] = $model::nextSortOrder();

        $model::create($data);

        return back()->with('success', $config['singular'].' berhasil ditambahkan.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $model = $config['model'];

        $item = $model::findOrFail($id);
        $item->update($this->validated($request, $config));

        return back()->with('success', $config['singular'].' berhasil diperbarui.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $model = $config['model'];

        $model::findOrFail($id)->delete();

        return back()->with('success', $config['singular'].' berhasil dihapus.');
    }

    /** Menggeser satu item ke atas/bawah dengan menukar `sort_order`. */
    public function move(Request $request, string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $model = $config['model'];

        $direction = $request->string('direction')->toString() === 'up' ? 'up' : 'down';

        $item = $model::findOrFail($id);

        $neighbour = $model::query()
            ->when($direction === 'up',
                fn ($q) => $q->where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order'),
                fn ($q) => $q->where('sort_order', '>', $item->sort_order)->orderBy('sort_order'),
            )
            ->first();

        if (! $neighbour) {
            return back();
        }

        [$item->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $item->sort_order];

        $item->save();
        $neighbour->save();

        return back();
    }

    /** Sakelar tampil/sembunyi tanpa menghapus data. */
    public function toggle(string $resource, int $id): RedirectResponse
    {
        $config = $this->config($resource);
        $model = $config['model'];

        $item = $model::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', $item->is_active ? 'Item ditampilkan di halaman publik.' : 'Item disembunyikan.');
    }

    /** @return array<string, mixed> */
    protected function config(string $resource): array
    {
        $config = config("admin_resources.{$resource}");

        abort_if(! $config, 404, 'Jenis konten tidak dikenal.');

        return $config;
    }

    /** Field + nilai default, siap dipakai form Vue. */
    protected function fields(array $config): array
    {
        return array_map(fn (array $field) => [
            'name' => $field['name'],
            'label' => $field['label'],
            'type' => $field['type'],
            'hint' => $field['hint'] ?? null,
            'options' => $field['options'] ?? [],
            'default' => $field['default'] ?? ($field['type'] === 'tags' ? [] : ''),
        ], $config['fields']);
    }

    protected function validated(Request $request, array $config): array
    {
        $rules = ['is_active' => ['boolean']];

        foreach ($config['fields'] as $field) {
            $rules[$field['name']] = $field['rules'];

            // Setiap elemen daftar tag harus berupa teks pendek. `nullable`
            // karena baris kosong dari form sampai di sini sebagai null.
            if ($field['type'] === 'tags') {
                $rules[$field['name'].'.*'] = ['nullable', 'string', 'max:120'];
            }
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);

        // Buang entri kosong pada field daftar sebelum disimpan.
        foreach ($config['fields'] as $field) {
            if ($field['type'] === 'tags') {
                $data[$field['name']] = array_values(array_filter(
                    $data[$field['name']] ?? [],
                    fn ($item) => filled($item),
                ));
            }
        }

        return $data;
    }

    /** Menambahkan URL gambar siap tampil ke baris tabel admin. */
    protected function present(Model $item, array $config): array
    {
        $row = $item->toArray();

        foreach ($config['fields'] as $field) {
            if ($field['type'] === 'image') {
                $row[$field['name'].'_url'] = MediaUrl::resolve($item->{$field['name']});
            }
        }

        return $row;
    }
}
