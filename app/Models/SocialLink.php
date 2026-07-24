<?php

namespace App\Models;

use App\Models\Concerns\Orderable;
use Illuminate\Database\Eloquent\Model;

/** Tautan media sosial di footer. */
class SocialLink extends Model
{
    use Orderable;

    protected $fillable = ['label', 'href', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
