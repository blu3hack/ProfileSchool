<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\ImageVariant;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/** Kartu keunggulan pada section "Keunggulan Kami". */
class Pillar extends Model
{
    use Orderable;

    protected $fillable = ['icon', 'title', 'description', 'points', 'accent', 'span', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['points' => 'array', 'is_active' => 'boolean'];
    }

    /** Bentuk yang dikonsumsi komponen Vue. */
    public function toCard(): array
    {
        return [
            'icon' => $this->icon,
            'title' => $this->title,
            'description' => $this->description,
            'points' => $this->points ?? [],
            'accent' => $this->accent,
            'span' => $this->span,
            'image' => MediaUrl::resolve($this->image),
            'srcset' => ImageVariant::srcset($this->image),
        ];
    }
}
