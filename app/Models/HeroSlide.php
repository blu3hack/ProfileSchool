<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/** Satu foto pada slideshow hero beranda, lengkap dengan profil singkatnya. */
class HeroSlide extends Model
{
    use Orderable;

    protected $fillable = ['image', 'alt', 'eyebrow', 'title', 'description', 'credit', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function toCard(): array
    {
        return [
            'src' => MediaUrl::resolve($this->image),
            'alt' => $this->alt ?: $this->title,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'description' => $this->description,
            'credit' => $this->credit,
        ];
    }
}
