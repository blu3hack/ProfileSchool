<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\ImageVariant;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/**
 * Menu tambahan bikinan admin. Satu baris di sini muncul di dua tempat
 * sekaligus: dropdown "Lainnya" pada navbar dan kartu pada section
 * "Menu Lainnya" tepat di atas footer.
 */
class CustomLink extends Model
{
    use Orderable;

    protected $fillable = ['icon', 'label', 'href', 'description', 'accent', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Bentuk yang dikonsumsi komponen Vue. */
    public function toCard(): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'label' => $this->label,
            'href' => $this->href,
            'description' => $this->description,
            'accent' => $this->accent,
            'image' => MediaUrl::resolve($this->image),
            'srcset' => ImageVariant::srcset($this->image),
        ];
    }
}
