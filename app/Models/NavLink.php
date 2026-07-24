<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use Illuminate\Database\Eloquent\Model;

/** Item menu navbar & footer. `hash` menunjuk ke id section landing. */
class NavLink extends Model
{
    use Orderable;

    protected $fillable = ['label', 'hash', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
