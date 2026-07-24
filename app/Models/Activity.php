<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/** Kegiatan / ekstrakurikuler pada section "Kegiatan". */
class Activity extends Model
{
    use Orderable;

    protected $fillable = ['icon', 'title', 'schedule', 'description', 'accent', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function toCard(): array
    {
        return [
            'icon' => $this->icon,
            'title' => $this->title,
            'schedule' => $this->schedule,
            'description' => $this->description,
            'accent' => $this->accent,
            'image' => MediaUrl::resolve($this->image),
        ];
    }
}
