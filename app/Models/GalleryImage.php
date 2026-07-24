<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/** Satu foto pada galeri profil sekolah, lengkap dengan keterangannya. */
class GalleryImage extends Model
{
    use Orderable;

    protected $fillable = ['image', 'title', 'caption', 'alt', 'credit', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function toCard(): array
    {
        return [
            'src' => MediaUrl::resolve($this->image),
            'title' => $this->title,
            'caption' => $this->caption,
            'alt' => $this->alt ?: $this->title,
            'credit' => $this->credit,
        ];
    }
}
