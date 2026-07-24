<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use Illuminate\Database\Eloquent\Model;

/** Angka ringkas di hero: "1.200+ Siswa Aktif". */
class Stat extends Model
{
    use Orderable;

    protected $fillable = ['value', 'label', 'hint', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
