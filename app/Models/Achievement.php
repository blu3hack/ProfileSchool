<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

/** Prestasi siswa pada section "Jejak Prestasi". */
class Achievement extends Model
{
    use Orderable;

    protected $fillable = ['icon', 'year', 'level', 'title', 'description', 'student', 'grade', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function toCard(): array
    {
        return [
            'icon' => $this->icon,
            'year' => $this->year,
            'level' => $this->level,
            'title' => $this->title,
            'description' => $this->description,
            'student' => $this->student,
            'grade' => $this->grade,
            'image' => MediaUrl::resolve($this->image),
        ];
    }
}
